<?php

return [
    // Login Modal
    'login' => [
        'title' => 'Login',
        'subtitle' => 'welcome back',
        'email_label' => 'Email',
        'username_label' => 'Your username',
        'password_label' => 'Your password',
        'forgot_password' => 'Forgot password',
        'login_button' => 'Sign in',
        'logging_in' => 'Signing in...',
        'loading' => 'Loading...',
        'error' => 'Login failed. Please try again.',
    ],

    // Register Modal
    'register' => [
        'title' => 'Registration',
        'subtitle_female' => 'for women',
        'subtitle_male' => 'for men',
        'gender_selection' => [
            'female_title' => 'I am a woman',
            'female_subtitle' => 'and I want to earn money...',
            'male_title' => 'I am a man',
            'male_subtitle' => 'and I want company...',
        ],
        'form' => [
            'username_label' => 'Username',
            'email_label' => 'Your email',
            'password_label' => 'Password',
            'password_confirmation_label' => 'Confirm password',
            'phone_label' => 'Phone',
        ],
        'register_button' => 'Register for FREE',
        'creating' => 'Creating...',
        'loading' => 'Loading...',
        'error' => 'Registration failed. Please try again.',
        'terms' => 'By continuing and registering you agree to the Terms and Conditions and Privacy Policy. You acknowledge that you have read and understood all terms.',
        'success' => [
            'title' => 'Registration successful, thank you',
            'message' => 'Please confirm the verification link we sent to :email. Then you can start using the platform fully.',
            'close_button' => 'I understand',
        ],
    ],
    'reset' => [
        'title' => 'Reset Password',
        'subtitle' => 'Forgot Password?',
        'description' => 'Enter your email address and we\'ll send you a link to reset your password.',
        'email_label' => 'Email Address',
        'send_button' => 'Reset password',
        'sending' => 'Sending...',
        'back_to_login' => 'Back to Login',
        'success_title' => 'Email Sent!',
        'success_message' => 'We\'ve sent you a password reset link. Please check your email inbox and follow the instructions.',
        'close' => 'Close',
        'loading' => 'Loading...'
    ],
    // Common
    'close' => 'Close',
    'back' => 'Back',
    'unknown_text' => "Don't know each other yet?",
    'switch_to_register' => 'Register for FREE',
    'switch_to_login' => 'Sign in',

    // Validation messages
    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
];