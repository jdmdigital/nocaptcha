<?php
declare(strict_types=1);

namespace NoCaptcha;

use Closure;
use InvalidArgumentException;
use PDO;
use Throwable;

final class Guard
{
    private const TOKEN_TTL = 1800;
    private const REPEAT_WINDOW = 900;
    private const RATE_WINDOW = 60;
    private const MAX_MESSAGE_BYTES = 16000;
    public const DEFAULT_FAST_SECONDS = 5;
    public const DEFAULT_SLOW_SECONDS = 300;

    private PDO $db;
    private Closure $clock;

    public function __construct(
        private string $secret,
        string $database,
        private int $fastSeconds = self::DEFAULT_FAST_SECONDS,
        private int $perClient = 10,
        private int $perForm = 120,
        ?Closure $clock = null,
        private int $slowSeconds = self::DEFAULT_SLOW_SECONDS,
        private int $maxMessageBytes = self::MAX_MESSAGE_BYTES,
    ) {
        if (strlen($secret) < 32 || $database === '' || $fastSeconds < 0 || $perClient < 1 || $perForm < 1
            || $slowSeconds <= $fastSeconds || $slowSeconds >= self::TOKEN_TTL || $maxMessageBytes < 1) {
            throw new InvalidArgumentException('Provide a strong secret, a database path, and valid thresholds.');
        }

        $this->clock = $clock ?? static fn (): int => time();
        $this->db = new PDO('sqlite:' . $database, options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->db->exec('PRAGMA busy_timeout = 3000');
        $this->db->exec('CREATE TABLE IF NOT EXISTS submissions (
            nonce TEXT PRIMARY KEY, form TEXT NOT NULL, source TEXT NOT NULL,
            message TEXT, submitted INTEGER NOT NULL, expires INTEGER NOT NULL
        )');
        $this->db->exec('CREATE INDEX IF NOT EXISTS submissions_rate ON submissions(form, submitted, source)');
        $this->db->exec('CREATE INDEX IF NOT EXISTS submissions_repeat ON submissions(form, message, submitted)');
        $this->db->exec('CREATE INDEX IF NOT EXISTS submissions_expiry ON submissions(expires)');
    }

    public function issue(string $form): string
    {
        $payload = ($this->clock)() . '.' . bin2hex(random_bytes(16));
        return $payload . '.' . $this->digest('token', $form, $payload);
    }

    public function fields(string $form, bool $includeCheckbox = true): string
    {
        return self::controls($this->issue($form), $includeCheckbox);
    }

    /** Pass an empty token when JavaScript will fetch a fresh one for cached markup. */
    public static function controls(string $token = '', bool $includeCheckbox = true): string
    {
        $checkbox = $includeCheckbox
            ? '<input type="checkbox" name="nc_checkbox" value="1" data-nc-checkbox checked disabled>'
            : '';

        return '<input type="hidden" name="nc_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">'
            . '<input type="hidden" name="nc_edit_ms" value="" data-nc-edit-ms>'
            . '<input type="hidden" name="nc_webdriver" value="" data-nc-webdriver>'
            . '<div hidden><label>Leave this field empty <input type="text" name="nc_website"'
            . ' tabindex="-1" autocomplete="off"></label>' . $checkbox . '</div>';
    }

    /** @return array{decision: 'allow'|'review'|'reject', reasons: list<string>} */
    public function verify(string $form, array $post, string $message, string $clientIp): array
    {
        $now = ($this->clock)();
        $token = $post['nc_token'] ?? '';
        if (!is_string($token) || !preg_match('/\A([0-9]{1,10})\.([a-f0-9]{32})\.([a-f0-9]{64})\z/', $token, $parts)) {
            return self::result('reject', ['invalid_token']);
        }

        [, $issued, $nonce, $signature] = $parts;
        if (!hash_equals($this->digest('token', $form, "$issued.$nonce"), $signature) || (int) $issued > $now) {
            return self::result('reject', ['invalid_token']);
        }
        if ($now - (int) $issued >= self::TOKEN_TTL) {
            return self::result('reject', ['expired_token']);
        }

        $normalized = strlen($message) <= $this->maxMessageBytes ? preg_replace('/\s+/u', ' ', $message) : null;
        if ($normalized === null || trim($normalized) === '') {
            return self::result('reject', ['invalid_message']);
        }

        $source = filter_var($clientIp, FILTER_VALIDATE_IP) ? inet_pton($clientIp) : false;
        $sourceHash = $this->digest('source', $form, $source === false ? '' : $source);
        $messageHash = $this->digest('message', $form, strtolower(trim($normalized)));
        $elapsed = $now - (int) $issued;
        $reasons = $elapsed < $this->fastSeconds ? ['fast_submit'] : [];
        if ($elapsed >= $this->slowSeconds) {
            $reasons[] = 'slow_submit';
        }
        if (($post['nc_webdriver'] ?? '') === '1') {
            $reasons[] = 'webdriver_reported';
        }
        $edit = $post['nc_edit_ms'] ?? '';
        if (is_string($edit) && ctype_digit($edit) && strlen($edit) <= 7
            && (int) $edit <= ($elapsed + 1) * 1000 && $elapsed * 1000 - (int) $edit < 1000) {
            $reasons[] = 'late_message_edit';
        }
        $trap = array_key_exists('nc_website', $post) && $post['nc_website'] !== '';
        if ($trap) {
            $reasons[] = 'filled_honeypot';
        }
        $checked = array_key_exists('nc_checkbox', $post);
        if ($checked) {
            $reasons[] = 'checked_trap';
        }

        // Serialize rate checks, replay detection and recording across PHP workers.
        $this->db->exec('BEGIN IMMEDIATE');
        try {
            $this->query('DELETE FROM submissions WHERE expires <= ?', [$now]);
            if ($this->query('SELECT 1 FROM submissions WHERE nonce = ?', [$nonce])->fetchColumn()) {
                $result = self::result('reject', ['replayed_token']);
            } elseif ($this->countRecent($form, $now) >= $this->perForm
                || $this->countRecent($form, $now, $sourceHash) >= $this->perClient) {
                $result = self::result('reject', ['rate_limited']);
            } else {
                if (!$trap && !$checked && $this->query(
                    'SELECT 1 FROM submissions WHERE form = ? AND message = ? AND submitted > ? LIMIT 1',
                    [$form, $messageHash, $now - self::REPEAT_WINDOW],
                )->fetchColumn()) {
                    $reasons[] = 'repeated_message';
                }
                $this->query('INSERT INTO submissions VALUES (?, ?, ?, ?, ?, ?)', [
                    $nonce, $form, $sourceHash, $trap || $checked ? null : $messageHash, $now,
                    max((int) $issued + self::TOKEN_TTL, $now + self::REPEAT_WINDOW),
                ]);
                $review = $checked || in_array('repeated_message', $reasons, true)
                    || in_array('fast_submit', $reasons, true) && in_array('late_message_edit', $reasons, true);
                $result = self::result($trap ? 'reject' : ($review ? 'review' : 'allow'), $reasons);
            }
            $this->db->exec('COMMIT');
            return $result;
        } catch (Throwable $error) {
            $this->db->exec('ROLLBACK');
            throw $error;
        }
    }

    private function countRecent(string $form, int $now, ?string $source = null): int
    {
        $sql = 'SELECT COUNT(*) FROM submissions WHERE form = ? AND submitted > ?';
        $args = [$form, $now - self::RATE_WINDOW];
        if ($source !== null) {
            $sql .= ' AND source = ?';
            $args[] = $source;
        }
        return (int) $this->query($sql, $args)->fetchColumn();
    }

    private function query(string $sql, array $args): \PDOStatement
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($args);
        return $statement;
    }

    private function digest(string $purpose, string $form, string $value): string
    {
        return hash_hmac('sha256', $purpose . "\0" . strlen($form) . ':' . $form . $value, $this->secret);
    }

    private static function result(string $decision, array $reasons): array
    {
        return ['decision' => $decision, 'reasons' => $reasons];
    }
}
