<?php

return [
    'name' => 'Inquiry Management System',
    'base_url' => '/public/',
    'timezone' => 'Asia/Shanghai',
    'debug' => true,
    'session_name' => 'ims_session',
    'api' => [
        'version' => 'v1',
        'enable_cors' => true,
        'ip_rate_limit_window_minutes' => 10,
        'ip_rate_limit_max' => 8,
        'email_rate_limit_window_minutes' => 10,
        'email_rate_limit_max' => 5,
        'duplicate_window_minutes' => 10,
        'spam_link_threshold' => 2,
        'honeypot_field' => 'website',
    ],
];
