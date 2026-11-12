<?php
/**
 * Plugin Name: noCAPTCHA
 * Description: Self-hosted spam verification for compatible forms.
 * Version: 0.1.0
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * License: GPL-3.0-or-later
 */
declare(strict_types=1);

use NoCaptcha\Guard;

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/src/Guard.php';

function term13_nocaptcha_enabled(): bool
{
    return defined('TERM13_NOCAPTCHA_DB') && is_string(TERM13_NOCAPTCHA_DB) && TERM13_NOCAPTCHA_DB !== '';
}

function term13_nocaptcha_guard(int $maxMessageBytes = 16000): Guard
{
    static $guards = [];
    $site = get_current_blog_id();
    return $guards[$site][$maxMessageBytes] ??= new Guard(
        secret: hash_hmac('sha256', 'noCAPTCHA WordPress site ' . $site, wp_salt('auth')),
        database: TERM13_NOCAPTCHA_DB,
        maxMessageBytes: $maxMessageBytes,
    );
}

function term13_nocaptcha_form_key(string $form): string
{
    return get_current_blog_id() . ':' . $form;
}

function term13_nocaptcha_enqueue(): void
{
    wp_enqueue_script(
        'term13-nocaptcha',
        plugins_url('public/assets/nocaptcha.js', __FILE__),
        [],
        '0.1.0',
        ['strategy' => 'defer', 'in_footer' => true],
    );
}
