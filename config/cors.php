<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3000','https://mini-social-media-platform.vercel.app'],
    'allowed_headers' => ['*'],
    'supports_credentials' => false, // false for token-based auth
];


