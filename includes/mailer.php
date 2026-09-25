<?php
/**
 * Password-reset delivery.
 * Choice for evaluation (documented in ReadMe.doc): localhost debug banner.
 * No SMTP is required. The generated reset URL is shown on forgot-password.php.
 */

function mailer_mode(): string
{
    return 'localhost_debug';
}

function send_password_reset_email(string $email, string $resetUrl): array
{
    return [
        'ok'       => true,
        'mode'     => mailer_mode(),
        'email'    => $email,
        'resetUrl' => $resetUrl,
        'message'  => 'Local Test Debug Banner: SMTP is not configured. Use the link below on this machine.',
    ];
}
