# noCAPTCHA

![A frustrated lime-green robot](docs/frustrated-robot.svg)

Self-hosted spam filtering for contact forms. PHP makes the decision before your handler delivers the message. Optional JavaScript adds first-edit timing, an inverted checkbox trap, and browser-reported automation signals without requiring visitors to solve a puzzle or perform an extra step.

[Try the Term 13 demo](https://term13.com/demos/nocaptcha/). The [original prototype](https://github.com/jdmdigital/nocaptcha/tree/5ea753bc0c102e571cc5e97efce0fda7cca02b81) is in the repository history.

## Try the demo

Requirements: PHP 8.1 or later with PDO SQLite and ctype. Run these commands from this directory. Use a fresh random secret for each installation and keep it stable between requests.

```sh
export NOCAPTCHA_SECRET="$(php -r 'echo bin2hex(random_bytes(32));')"
export NOCAPTCHA_DB="$(mktemp -d)/nocaptcha.sqlite"
php -S 127.0.0.1:8853 -t public
```

PowerShell:

```powershell
$env:NOCAPTCHA_SECRET = php -r 'echo bin2hex(random_bytes(32));'
$demoState = Join-Path ([IO.Path]::GetTempPath()) ('nocaptcha-' + [guid]::NewGuid())
New-Item -ItemType Directory -Path $demoState | Out-Null
$env:NOCAPTCHA_DB = Join-Path $demoState 'nocaptcha.sqlite'
php -S 127.0.0.1:8853 -t public
```

Open [localhost:8853](http://127.0.0.1:8853/). Use a made-up name and message. The visible switch demonstrates the inverted checkbox. The red position is checked, and flipping it to green unchecks it. The canned bot examples use the same PHP verifier with isolated state, so they can be run repeatedly.

For a public demo, map only `public/` to the URL in a PHP-capable host. Keep `src/`, the SQLite directory, and the secret outside the public document root. Exclude the demo’s dynamic page from CDN and page caches. Set `NOCAPTCHA_REPO_URL` to override the upstream contributor link for a fork. Use a separate secret and database for each real application.

On hosts that do not pass environment variables to PHP, create a private `config.local.php` alongside this README that returns an array with `secret`, `database`, `repoUrl`, and `publicUrl`. The last two keys control the GitHub link and the canonical URL. This file is ignored by Git.

## Install on an existing PHP contact form

1. Put `src/Guard.php` in your application code. Copy `public/assets/nocaptcha.js` into your public assets if you want the optional browser signals.
2. Set `NOCAPTCHA_SECRET` to a random value of at least 32 bytes. The generation command above produces a suitable value. Set `NOCAPTCHA_DB` to an absolute path to a SQLite file in an existing directory outside the web root. Give the PHP worker read and write access to that directory and restrict access to the service account. The verifier creates its tables on first use.
3. Choose a stable form key for each form, such as `contact`. Initialize the verifier in both the rendering route and the POST handler. Keep the same key, secret, database and thresholds on both routes.

```php
require_once __DIR__ . '/lib/Guard.php';

$guard = new \NoCaptcha\Guard(
    secret: getenv('NOCAPTCHA_SECRET') ?: '',
    database: getenv('NOCAPTCHA_DB') ?: '',
);
$formKey = 'contact';
```

4. Add the fields when PHP renders the form. Serve that form page with `Cache-Control: no-store`, or exclude it from page caches and CDNs, so each visitor gets their own token. Sites with cached HTML can render the form through an uncached server endpoint. Keep your usual CSRF protection and validation in place.

```php
<form method="post" action="/contact" data-nocaptcha>
  <?= $guard->fields($formKey) ?>
  <label for="email">Email</label>
  <input id="email" type="email" name="email" autocomplete="email" required>
  <label for="message">Message</label>
  <textarea id="message" name="message" maxlength="4000" data-nc-message required></textarea>
  <button type="submit">Send</button>
</form>
<script defer src="/assets/nocaptcha.js"></script>
```

5. On POST, validate your application fields first. Then call the verifier before sending mail, creating a ticket, or writing to your application database. Pass the message field explicitly and use the source address your server trusts. `REMOTE_ADDR` is the socket peer. Behind a reverse proxy, configure trusted proxy handling at your server or framework before using a forwarded address. Do not trust an arbitrary request header.

```php
// This runs inside your validated POST handler.
$verdict = $guard->verify($formKey, $_POST, $message, $_SERVER['REMOTE_ADDR'] ?? '');

switch ($verdict['decision']) {
    case 'allow':
        // Deliver using your existing handler. Use your normal success response.
        break;
    case 'review':
        // Save the inquiry in your own private moderation queue if you use one.
        // For a first rollout, you can log the reason codes and deliver as usual.
        break;
    case 'reject':
        // Do not deliver. Render a fresh token, preserving the visitor's draft.
        // Use HTTP 429 with Retry-After for rate_limited if appropriate.
        break;
}
```

The guard returns a decision and a list of machine-readable reason codes. Your handler owns delivery, moderation, errors, and message retention. Avoid showing exact rule outcomes on a real contact form. Catch storage or initialization errors at your application boundary and handle them explicitly. A temporary service error is a reasonable default if you require filtering before delivery.

## Default decisions

| Check | Default |
| --- | --- |
| Missing, altered, expired, or already-used token | Reject |
| Invalid or oversized message, or filled text honeypot | Reject |
| Above the per-client or per-form submission limit | Reject |
| Checked checkbox trap or repeated message | Review |
| Under 5 seconds from form issue and a late first message edit | Review |
| Fast submission alone, late edit alone, slow submission, or WebDriver report | Allow, with the corresponding reasons included |

Tokens expire after 30 minutes and are consumed atomically with recording the attempt. The rolling limit defaults to 10 attempts per client and 120 per form in 60 seconds. The constructor accepts named arguments `fastSeconds`, `slowSeconds`, `perClient`, and `perForm`. Submissions before 5 seconds or at or after 5 minutes raise timing signals by default. The upper threshold must be greater than the lower threshold and shorter than the token lifetime. JavaScript is optional for token, text honeypot, server timing, repeat, and rate checks.

The optional browser script reports `navigator.webdriver` when the browser exposes it. This can indicate browser automation, including ordinary testing tools. The value can be omitted or forged and cannot establish AI involvement. The verifier records `webdriver_reported` as a signal without blocking on it.

The repeat check matches the same message within 15 minutes on the same form, including from another address. It normalizes whitespace and ASCII case. SQLite stores timestamps, form keys, random nonces, and keyed fingerprints of client addresses and messages. The guard stores neither raw messages nor raw addresses. Expired rows are pruned on verification after token and message validation. For an inactive form, schedule cleanup if you need a strict retention period.

SQLite is suited to a single application server with a local disk and multiple PHP workers. Use an equivalent shared atomic store before running independent instances. This is a contact-form filter. Keep server-level request-size limits and ingress throttling in place for traffic that never presents a valid token.

## Add a layer

Run application-specific rules after the verifier and before delivery. Preserve rejections, and move allowed submissions to review when an additional rule fires. Use stable reason codes, describe what can trigger a false positive, and include a demo case or regression test when contributing a rule. Browser signals should be optional, since callers can omit or forge them.

Run the verifier checks with `php tests/guard.php`.
