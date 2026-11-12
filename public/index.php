<?php
declare(strict_types=1);

use NoCaptcha\Guard;

require dirname(__DIR__) . '/src/Guard.php';

header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'self'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");

$reasons = [
    'invalid_token' => 'The form token was missing, altered, or issued for another form.',
    'expired_token' => 'The form was open past the token’s validity window.',
    'replayed_token' => 'That form token has already been used.',
    'invalid_message' => 'The message was empty, too large, or invalid.',
    'fast_submit' => 'The form was submitted unusually quickly.',
    'slow_submit' => 'The form was open past the timing window. A slow submission alone is allowed.',
    'webdriver_reported' => 'The browser reported WebDriver control. This signal alone is allowed.',
    'late_message_edit' => 'The first message edit was reported just before submission.',
    'filled_honeypot' => 'An automated form filler touched a hidden field.',
    'checked_trap' => 'The inverted checkbox was submitted in its checked state.',
    'repeated_message' => 'A matching message appeared on this form within the last 15 minutes.',
    'rate_limited' => 'The form received too many submissions in a short window.',
];

$scenarios = [
    'direct' => 'POST without a form token',
    'checkbox' => 'Bot that checks every box',
    'honeypot' => 'Bot that fills hidden fields',
    'repeat' => 'Slow bot repeating a message',
    'replay' => 'Submit the same token twice',
    'burst' => 'Send a burst of messages',
    'slow' => 'Submit after the timing window',
    'automation' => 'Try a WebDriver-controlled browser',
];

$layers = [
    'token' => ['Form token', 'PHP signs the token, checks its age, and tracks whether it was used.', 'Token verified.', ['invalid_token', 'expired_token', 'replayed_token']],
    'timing' => ['Timing & browser', 'PHP measures the form’s age. JavaScript can report the first edit and whether the browser reports WebDriver control.', 'No timing or browser signals fired.', ['fast_submit', 'slow_submit', 'late_message_edit', 'webdriver_reported']],
    'honeypot' => ['Text honeypot', 'An empty hidden field catches bots that fill every text input.', 'Hidden field was empty.', ['filled_honeypot']],
    'checkbox' => ['Inverted checkbox', 'JavaScript clears a hidden checkbox that simple bots may check again.', 'Checkbox was clear.', ['checked_trap']],
    'repeat' => ['Repeat messages', 'PHP compares recent messages using a keyed fingerprint.', 'No matching message found.', ['repeated_message']],
    'rate' => ['Submission limits', 'PHP counts recent submissions per client and per form.', 'Within the submission limits.', ['rate_limited']],
];

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function posted(string $name, int $limit): string
{
    $value = $_POST[$name] ?? '';
    return is_string($value) && strlen($value) <= $limit ? $value : '';
}

function simulate(string $scenario, string $secret): array
{
    $time = 100000;
    $guard = new Guard($secret, ':memory:', perClient: 2, clock: static function () use (&$time): int {
        return $time;
    });
    $form = 'simulation';
    $message = 'Can we talk about upgrading your web forms?';
    $first = ['nc_token' => $guard->issue($form)];
    $time += $scenario === 'slow' ? Guard::DEFAULT_SLOW_SECONDS : 12;

    return match ($scenario) {
        'direct' => $guard->verify($form, [], $message, '192.0.2.1'),
        'checkbox' => $guard->verify($form, $first + ['nc_checkbox' => '1'], $message, '192.0.2.1'),
        'honeypot' => $guard->verify($form, $first + ['nc_website' => 'https://example.test/'], $message, '192.0.2.1'),
        'slow' => $guard->verify($form, $first, $message, '192.0.2.1'),
        'automation' => $guard->verify($form, $first + ['nc_webdriver' => '1'], $message, '192.0.2.1'),
        'repeat' => (function () use ($guard, $form, $first, $message, &$time): array {
            $guard->verify($form, $first, $message, '192.0.2.1');
            $second = ['nc_token' => $guard->issue($form)];
            $time += 12;
            return $guard->verify($form, $second, $message, '192.0.2.2');
        })(),
        'replay' => (function () use ($guard, $form, $first, $message): array {
            $guard->verify($form, $first, $message, '192.0.2.1');
            return $guard->verify($form, $first, $message, '192.0.2.1');
        })(),
        'burst' => (function () use ($guard, $form, $message): array {
            for ($i = 0; $i < 2; $i++) {
                $guard->verify($form, ['nc_token' => $guard->issue($form)], $message . $i, '192.0.2.1');
            }
            return $guard->verify($form, ['nc_token' => $guard->issue($form)], $message, '192.0.2.1');
        })(),
    };
}

$config = [];
$configFile = dirname(__DIR__) . '/config.local.php';
$secret = '';
$result = null;
$scenario = '';
$error = '';
$name = '';
$message = '';
$fields = '';
$toggle = posted('nc_checkbox', 1) === '1' || $_SERVER['REQUEST_METHOD'] !== 'POST';

try {
    if (is_file($configFile)) {
        $config = require $configFile;
        if (!is_array($config)) {
            throw new RuntimeException('Invalid demo configuration.');
        }
    }
    $secret = getenv('NOCAPTCHA_SECRET') ?: ($config['secret'] ?? '');
    $database = getenv('NOCAPTCHA_DB') ?: ($config['database'] ?? '');
    if (!$secret || !$database) {
        throw new RuntimeException('Demo environment is not configured.');
    }
    $guard = new Guard($secret, $database, perClient: 20);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $scenario = posted('scenario', 16);
        if (isset($scenarios[$scenario])) {
            $result = simulate($scenario, $secret);
            $toggle = true;
        } else {
            $name = posted('name', 480);
            $message = posted('message', 16000);
            if (trim($name) === '' || trim($message) === '') {
                $error = 'Enter a name and a message to try the form.';
            } else {
                $result = $guard->verify('demo-contact', $_POST, $message, $_SERVER['REMOTE_ADDR'] ?? '');
            }
        }
    }
    $fields = $guard->fields('demo-contact', includeCheckbox: false);
} catch (Throwable $exception) {
    error_log('noCAPTCHA demo could not initialize or verify a submission.');
    http_response_code(503);
    $error = 'The demo is temporarily unavailable.';
}

$repoUrl = getenv('NOCAPTCHA_REPO_URL') ?: ($config['repoUrl'] ?? 'https://github.com/jdmdigital/nocaptcha');
$repoReady = is_string($repoUrl) && preg_match('~\Ahttps://github\.com/[a-zA-Z0-9-]+/[a-zA-Z0-9_.-]+/?\z~', $repoUrl) === 1;
$publicUrl = $config['publicUrl'] ?? '';
$isPublic = is_string($publicUrl) && str_starts_with($publicUrl, 'https://') && filter_var($publicUrl, FILTER_VALIDATE_URL);
$shareImage = $isPublic ? rtrim($publicUrl, '/') . '/assets/share.png' : '';
$pageTitle = 'noCAPTCHA | Term 13 Lab';
$pageDescription = 'Try a self-hosted spam filter for contact forms and see which checks fire.';
$shareAlt = 'A frustrated lime-green robot next to noCAPTCHA by Term 13.';
if (!$isPublic) {
    header('X-Robots-Tag: noindex');
}

$outcomes = ['allow' => 'Allowed through', 'review' => 'Held for review', 'reject' => 'Stopped'];
$fired = $result['reasons'] ?? [];
$tokenStopped = array_intersect($layers['token'][3], $fired) !== [];
$rateStopped = in_array('rate_limited', $fired, true);
$messageStopped = in_array('invalid_message', $fired, true);
$trapsFired = array_intersect(['filled_honeypot', 'checked_trap'], $fired) !== [];
$checkedLayers = $result && !$tokenStopped && !$messageStopped
    ? ($rateStopped ? ['token'] : ($trapsFired ? ['token', 'timing', 'honeypot', 'checkbox', 'rate'] : array_keys($layers)))
    : [];
$marks = ['pass' => '✓', 'flag' => '×', 'pending' => '–'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= escape($pageDescription) ?>">
  <title><?= escape($pageTitle) ?></title>
  <?php if ($isPublic): ?>
  <link rel="canonical" href="<?= escape($publicUrl) ?>">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Term 13">
  <meta property="og:url" content="<?= escape($publicUrl) ?>">
  <meta property="og:title" content="<?= escape($pageTitle) ?>">
  <meta property="og:description" content="<?= escape($pageDescription) ?>">
  <meta property="og:image" content="<?= escape($shareImage) ?>">
  <meta property="og:image:type" content="image/png">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="<?= escape($shareAlt) ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= escape($pageTitle) ?>">
  <meta name="twitter:description" content="<?= escape($pageDescription) ?>">
  <meta name="twitter:image" content="<?= escape($shareImage) ?>">
  <meta name="twitter:image:alt" content="<?= escape($shareAlt) ?>">
  <?php endif; ?>
  <link rel="icon" href="./assets/reaction.svg" type="image/svg+xml">
  <link rel="stylesheet" href="./assets/demo-shell.css">
  <link rel="stylesheet" href="./assets/demo.css">
  <script defer src="./assets/nocaptcha.js"></script>
  <script defer src="./assets/demo.js"></script>
</head>
<body>
  <a class="skip-link" href="#main">Skip to content</a>
  <header class="site-header">
    <div class="wrap masthead">
      <a class="brand" href="https://term13.com/"><img src="./assets/reaction.svg" width="29" height="29" alt="">TERM 13</a>
      <span class="masthead-label">/ Lab demos</span>
      <nav class="masthead-nav" aria-label="Demo navigation">
        <a href="https://term13.com/demos/">All demos</a>
        <?php if ($repoReady): ?><a href="<?= escape($repoUrl) ?>">GitHub <span aria-hidden="true">↗</span></a><?php endif; ?>
      </nav>
    </div>
  </header>

  <main id="main" class="wrap">
    <div class="intro">
      <h1>noCAPTCHA<span>.</span></h1>
      <p>Filter contact-form spam without asking visitors to solve a puzzle. Try the form, then see what the server noticed.</p>
    </div>

    <div class="lab">
      <section class="form-card" aria-labelledby="form-title">
        <h2 id="form-title">Try the form</h2>
        <p class="help" id="sample-note">Use a made-up name and message.</p>
        <?php if ($error !== ''): ?><p role="alert" class="error"><?= escape($error) ?></p><?php endif; ?>
        <form method="post" action="#inspection-title" data-nocaptcha data-fast-seconds="<?= Guard::DEFAULT_FAST_SECONDS ?>" data-slow-seconds="<?= Guard::DEFAULT_SLOW_SECONDS ?>" aria-describedby="sample-note">
          <?= $fields ?>
          <label for="name">Name</label>
          <input type="text" id="name" name="name" value="<?= escape($name) ?>" placeholder="Jamie Example" maxlength="120" required>
          <label for="message">Message</label>
          <textarea id="message" name="message" rows="3" maxlength="4000" placeholder="What are you working on?" data-nc-message required><?= escape($message) ?></textarea>
          <label class="toggle">
            <input type="checkbox" name="nc_checkbox" value="1" aria-describedby="toggle-note" <?= $toggle ? 'checked' : '' ?>>
            <span class="switch" aria-hidden="true"></span>
            <span class="toggle-red">Demo trap is on</span><span class="toggle-green">Demo trap is clear</span>
          </label>
          <p class="help" id="toggle-note">Flip it from red to green to try the inverted checkbox. This control is visible for the demo.</p>
          <button class="pill primary" type="submit" <?= $fields === '' ? 'disabled' : '' ?>>Check submission <span aria-hidden="true">→</span></button>
        </form>
        <div class="bot-examples">
          <h3>Try a bot submission</h3>
          <p class="help">Run an example through the same PHP checks.</p>
          <form method="post" action="#inspection-title" class="scenarios">
            <?php foreach ($scenarios as $value => $label): ?>
              <button type="submit" name="scenario" value="<?= escape($value) ?>" <?= $fields === '' ? 'disabled' : '' ?>><?= escape($label) ?> <span aria-hidden="true">↗</span></button>
            <?php endforeach; ?>
          </form>
        </div>
      </section>

      <section class="inspection" aria-labelledby="inspection-title">
        <h2 id="inspection-title"><?= $result ? 'What the server saw' : 'Layer monitor' ?></h2>
        <p class="monitor-note" id="monitor-note"><?= $result ? 'PHP results for the last submission.' : 'PHP checks these layers when you submit.' ?></p>
        <ul class="monitor" id="monitor" data-phase="<?= $result ? 'server' : 'live' ?>">
          <?php foreach ($layers as $key => [$title, $description, $passed, $codes]): ?>
            <?php
            $matches = array_intersect($codes, $fired);
            $state = $matches ? 'flag' : (in_array($key, $checkedLayers, true) ? 'pass' : 'pending');
            $detail = match ($state) {
                'flag' => implode(' ', array_map(static fn (string $reason): string => $reasons[$reason], $matches)),
                'pass' => $passed,
                default => $result ? 'Not reached for this submission.' : 'Waiting for submission.',
            };
            ?>
            <li data-layer="<?= escape($key) ?>" data-state="<?= $state ?>">
              <span class="layer-mark" aria-hidden="true"><?= $marks[$state] ?></span>
              <div><strong><?= escape($title) ?></strong><small><?= escape($detail) ?></small></div>
            </li>
          <?php endforeach; ?>
        </ul>
        <div class="verdict" id="result" role="status" <?= $result ? 'data-decision="' . escape($result['decision']) . '"' : '' ?>>
          <?php if ($result): ?>
            <strong><?= escape($outcomes[$result['decision']]) ?></strong>
            <p><?= isset($scenarios[$scenario]) ? escape($scenarios[$scenario]) : 'Contact form submission' ?></p>
            <?php if ($result['reasons']): ?>
              <ul><?php foreach ($result['reasons'] as $reason): ?>
                <li><code><?= escape($reason) ?></code><?= $reason === 'invalid_message' ? ' ' . escape($reasons[$reason]) : '' ?></li>
              <?php endforeach; ?></ul>
            <?php else: ?><p>No spam signals fired.</p><?php endif; ?>
          <?php else: ?>
            <strong>Ready to check</strong>
            <p>Submit the form or run a bot example to see the result here.</p>
          <?php endif; ?>
        </div>
      </section>
    </div>

    <section class="layers" aria-labelledby="layer-title">
      <h2 id="layer-title">What runs behind the form</h2>
      <dl>
        <?php foreach ($layers as [$title, $description]): ?>
          <div><dt><?= escape($title) ?></dt><dd><?= escape($description) ?></dd></div>
        <?php endforeach; ?>
      </dl>
    </section>
    <section class="contribute" aria-labelledby="contribute-title">
      <div>
        <h2 id="contribute-title">Help catch bots. Leave people out of it.</h2>
        <p>Have a signal that catches spam without making visitors jump through hoops? Bring your idea, a test case, or a new layer to the noCAPTCHA GitHub project.</p>
        <?php if ($repoReady): ?>
          <a class="pill primary" href="<?= escape($repoUrl) ?>">Contribute on GitHub <span aria-hidden="true">↗</span></a>
        <?php else: ?>
          <p class="repo-note">The GitHub repository will open with the public release.</p>
        <?php endif; ?>
      </div>
      <div class="robot-art" aria-hidden="true">
        <div class="robot-head"><span class="robot-antenna"></span><span class="robot-face"></span></div>
        <span class="robot-spark">!</span>
      </div>
    </section>
  </main>
  <footer class="site-footer"><div class="wrap footer-inner"><span>© Term 13, LLC</span><a href="https://term13.com/">Term 13 <span aria-hidden="true">↗</span></a></div></footer>
</body>
</html>

