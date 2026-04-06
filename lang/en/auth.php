<?php

declare(strict_types=1);

return [
    'failed'   => 'These credentials do not match our records.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    'logout'   => 'Successfully logged out.',
    'login'    => 'Successfully logged in.',
    'web' => [
        'common' => [
            'or' => 'or',
            'back_to_login' => 'Back to login',
            'terms' => 'Terms of Service',
            'logout' => 'Logout',
        ],
        'fields' => [
            'email' => 'Email',
            'password' => 'Password',
            'user' => 'User',
            'guard' => 'Guard',
        ],
        'login' => [
            'meta_title' => 'Login',
            'title' => 'Sign in to your account',
            'forgot' => 'Forgot my password',
            'remember' => 'Remember me',
            'submit' => 'Sign in',
            'error_required' => 'Email and password are required.',
            'error_invalid' => 'Invalid login credentials.',
        ],
        'forgot' => [
            'meta_title' => 'Forgot Password',
            'title' => 'Forgot your password?',
            'description' => 'Enter your email address and we will prepare a reset link.',
            'submit' => 'Send reset link',
            'accept_prefix' => 'By continuing, you accept the',
            'accept_suffix' => '.',
            'error_invalid_email' => 'Please enter a valid email address.',
            'success' => 'Password reset link prepared (simulation).',
        ],
        'tos' => [
            'meta_title' => 'Terms of Service',
            'title' => 'Terms of Service',
            'p1' => ':app is a core designed for personal and small/medium business applications.',
            'p2' => 'This sample page is kept to validate UI and flow, not as a legal draft.',
            'p3' => 'For production, you should add application-specific privacy policy and terms.',
        ],
        'lock' => [
            'meta_title' => 'Unlock',
            'description' => 'Session locked. Enter your password to continue.',
            'submit' => 'Unlock',
            'switch_account' => 'Sign in with another account',
            'error_required' => 'Password is required.',
            'error_invalid' => 'Invalid password.',
        ],
        'dashboard' => [
            'meta_title' => 'Core Dashboard',
            'title' => 'Core Dashboard',
            'subtitle' => 'Default checkpoint after authentication.',
            'welcome' => 'Welcome, :name',
            'description' => ':app core is ready. You can add app-specific modules with make:module and make:crud commands.',
            'landing' => 'Landing',
            'account_summary' => 'Account Summary',
        ],
    ],
];
