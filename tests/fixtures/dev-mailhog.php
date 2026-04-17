<?php
/**
 * MU-Plugin: route all wp_mail() calls to MailHog for local development.
 *
 * Installed automatically by wp-env via .wp-env.json mappings.
 * This file is only loaded in the local development environment.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Override WordPress PHPMailer to use MailHog SMTP.
 *
 * @param PHPMailer $phpmailer The PHPMailer instance.
 */
add_action(
    'phpmailer_init',
    static function (PHPMailer $phpmailer): void {
        $phpmailer->Host     = 'localhost';
        $phpmailer->Port     = 1025;
        $phpmailer->SMTPAuth = false;
        $phpmailer->isSMTP();
    }
);
