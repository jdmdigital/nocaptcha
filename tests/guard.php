<?php
declare(strict_types=1);

use NoCaptcha\Guard;

require dirname(__DIR__) . '/src/Guard.php';

$now = 100000;
$secret = str_repeat('local-test-only', 3);
$clock = static function () use (&$now): int { return $now; };
$guard = new Guard($secret, ':memory:', clock: $clock);
$form = 'contact';
$client = '192.0.2.1';
$message = 'A normal inquiry';

function expectResult(array $actual, string $decision, array $reasons): void
{
    if ($actual !== ['decision' => $decision, 'reasons' => $reasons]) {
        throw new RuntimeException('Unexpected verdict: ' . json_encode($actual));
    }
}

expectResult($guard->verify($form, [], $message, $client), 'reject', ['invalid_token']);
$post = ['nc_token' => $guard->issue($form), 'nc_website' => ''];
expectResult($guard->verify('another-form', $post, $message, $client), 'reject', ['invalid_token']);
$tampered = $post;
$tampered['nc_token'][0] = '2';
expectResult($guard->verify($form, $tampered, $message, $client), 'reject', ['invalid_token']);
$now += 7;
expectResult($guard->verify($form, $post, $message, $client), 'allow', []);
expectResult($guard->verify($form, $post, $message, $client), 'reject', ['replayed_token']);
$post = ['nc_token' => $guard->issue($form)];
$now += 7;
expectResult($guard->verify($form, $post, "  A NORMAL\n inquiry ", '192.0.2.2'), 'review', ['repeated_message']);

$post = ['nc_token' => $guard->issue($form), 'nc_checkbox' => '1'];
$now += 7;
expectResult($guard->verify($form, $post, 'Checkbox check', $client), 'review', ['checked_trap']);
$post = ['nc_token' => $guard->issue($form), 'nc_website' => 'https://example.test/'];
$now += 7;
expectResult($guard->verify($form, $post, 'Text trap check', $client), 'reject', ['filled_honeypot']);

$post = ['nc_token' => $guard->issue($form)];
expectResult($guard->verify($form, $post, 'Fast submission with JavaScript off', $client), 'allow', ['fast_submit']);
$post = ['nc_token' => $guard->issue($form), 'nc_edit_ms' => '100'];
expectResult($guard->verify($form, $post, 'Fast submission with late edit', $client), 'review', ['fast_submit', 'late_message_edit']);

$post = ['nc_token' => $guard->issue($form)];
$now += Guard::DEFAULT_FAST_SECONDS;
expectResult($guard->verify($form, $post, 'At the start of the timing window', $client), 'allow', []);
$post = ['nc_token' => $guard->issue($form)];
$now += Guard::DEFAULT_SLOW_SECONDS - 1;
expectResult($guard->verify($form, $post, 'Just inside the timing window', $client), 'allow', []);
$post = ['nc_token' => $guard->issue($form)];
$now += Guard::DEFAULT_SLOW_SECONDS;
expectResult($guard->verify($form, $post, 'Took a break while writing', $client), 'allow', ['slow_submit']);
$post = ['nc_token' => $guard->issue($form), 'nc_webdriver' => '1'];
$now += 8;
expectResult($guard->verify($form, $post, 'Automated browser', $client), 'allow', ['webdriver_reported']);

$post = ['nc_token' => $guard->issue($form)];
$now += 1800;
expectResult($guard->verify($form, $post, $message, $client), 'reject', ['expired_token']);
$post = ['nc_token' => $guard->issue($form)];
$now += 7;
expectResult($guard->verify($form, $post, $message, $client), 'allow', []);

$limited = new Guard($secret, ':memory:', perClient: 1, perForm: 2, clock: $clock);
$submit = static fn (string $ip, string $text): array => $limited->verify($form, [
    'nc_token' => $limited->issue($form),
], $text, $ip);
expectResult($submit($client, 'First submission'), 'allow', ['fast_submit']);
expectResult($submit($client, 'Second submission'), 'reject', ['rate_limited']);
expectResult($submit('192.0.2.2', 'Another source'), 'allow', ['fast_submit']);
expectResult($submit('192.0.2.3', 'Third source'), 'reject', ['rate_limited']);
$now += 60;
expectResult($submit('192.0.2.3', 'Window reset'), 'allow', ['fast_submit']);

echo "noCAPTCHA guard checks passed.\n";
