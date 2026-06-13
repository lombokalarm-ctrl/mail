<?php

return [
    'domain' => env('APLI_MAIL_DOMAIN', 'email.apli.my.id'),
    'attachments_disk' => env('APLI_MAIL_ATTACHMENTS_DISK', env('FILESYSTEM_DISK', 'local')),
    'attachment_limit' => (int) env('APLI_MAIL_ATTACHMENT_LIMIT', 25 * 1024 * 1024),
    'default_admin_email' => env('APLI_MAIL_ADMIN_EMAIL', 'admin@apli.my.id'),
    'default_admin_password' => env('APLI_MAIL_ADMIN_PASSWORD', 'password'),
];
