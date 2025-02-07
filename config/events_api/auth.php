<?php

declare(strict_types=1);

return [
    'valid_email_domains' => array_map('trim', explode(',', env('VALID_EMAIL_DOMAINS', '@khable.com'))),
];
