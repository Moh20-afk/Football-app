<?php
// Database configuration
return [
    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'user' => 'root',
        'password' => 'password', // Replace with your actual password
        'database' => 'user_details'
    ],
    
    // Application settings
    'app' => [
        'debug' => true, // Set to false in production
        'session_timeout' => 3600, // 1 hour
        'max_login_attempts' => 5
    ]
];
?> 