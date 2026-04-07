<?php

declare(strict_types=1);

return array (
  'failed' => 'These credentials do not match our records.',
  'login' => 'Successfully logged in.',
  'logout' => 'Successfully logged out.',
  'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
  'web' => 
  array (
    'common' => 
    array (
      'back_to_login' => 'Back to login',
      'logout' => 'Logout',
      'or' => 'or',
      'terms' => 'Terms of Service',
    ),
    'fields' => 
    array (
      'email' => 'Email',
      'guard' => 'Guard',
      'pin' => 'PIN',
      'password' => 'Password',
      'user' => 'User',
    ),
    'forgot' => 
    array (
      'accept_prefix' => 'By continuing, you accept the',
      'accept_suffix' => '.',
      'description' => 'Enter your email address and we will prepare a reset link.',
      'description_verify' => 'Enter your account email to verify password reset.',
      'description_set_new' => 'Set your new account password to continue.',
      'error_invalid_email' => 'Please enter a valid email address.',
      'error_not_found' => 'Account not found for this email.',
      'error_password_invalid' => 'Password must be at least 6 characters.',
      'error_password_mismatch' => 'Password confirmation does not match.',
      'error_session_expired' => 'Password reset session expired. Please verify again.',
      'error_send_failed' => 'Password reset email could not be sent. Check mail configuration.',
      'error_setup_required' => 'Password reset storage is not ready. Please run migrations.',
      'meta_title' => 'Forgot Password',
      'password_confirmation' => 'Password (confirm)',
      'submit' => 'Send reset link',
      'submit_verify' => 'Verify account',
      'submit_set' => 'Save new password',
      'success' => 'Password reset link prepared (simulation).',
      'success_sent' => 'Password reset link has been sent to your email address.',
      'success_login' => 'Password updated and lock PIN cleared. Please sign in with your new password.',
      'title' => 'Forgot your password?',
      'title_set_new' => 'Set new password',
    ),
    'forgot_pin' =>
    array (
      'back_to_lock' => 'Back to lock screen',
      'description_verify' => 'Verify your account with email and password.',
      'description_set_new' => 'Set your new lock PIN to continue.',
      'error_invalid_credentials' => 'Email or password is incorrect.',
      'error_invalid_email' => 'Please enter a valid email address.',
      'error_pin_invalid' => 'PIN must be 4-8 digits.',
      'error_pin_mismatch' => 'PIN confirmation does not match.',
      'error_session_expired' => 'PIN reset session expired. Please verify again.',
      'error_password_required' => 'Password is required.',
      'meta_title' => 'Forgot PIN',
      'pin_confirmation' => 'PIN (confirm)',
      'pin_hint' => 'Use 4-8 digits.',
      'submit_verify' => 'Verify account',
      'submit_set' => 'Save new PIN',
      'success' => 'New lock PIN saved successfully.',
      'title' => 'Forgot lock PIN?',
      'title_set_new' => 'Set new lock PIN',
    ),
    'lock' => 
    array (
      'description' => 'Session locked. Enter your password to continue.',
      'error_invalid' => 'Invalid password.',
      'error_pin_invalid' => 'Invalid PIN.',
      'error_pin_required' => 'PIN is required.',
      'error_required' => 'Password is required.',
      'meta_title' => 'Unlock',
      'password_hint' => 'No PIN is set for this account yet. Unlock with account password.',
      'pin_hint' => 'Enter your profile lock PIN (4-8 digits).',
      'submit' => 'Unlock',
      'forgot_pin' => 'Forgot PIN?',
      'forgot_password' => 'Forgot password?',
      'switch_account' => 'Sign in with another account',
    ),
    'login' => 
    array (
      'error_invalid' => 'Invalid login credentials.',
      'error_required' => 'Email and password are required.',
      'forgot' => 'Forgot my password',
      'meta_title' => 'Login',
      'remember' => 'Remember me',
      'submit' => 'Sign in',
      'title' => 'Sign in to your account',
    ),
    'nav' => 
    array (
      'dashboard' => 'Dashboard',
      'locales' => 'Locale Management',
      'management' => 'Management',
      'roles' => 'Roles',
      'users' => 'Users',
      'logs' => 'Logs',
    ),
    'user_menu' =>
    array (
      'account' => 'My Account',
      'profile' => 'Edit Profile',
      'lock' => 'Lock Screen',
    ),
    'tos' => 
    array (
      'meta_title' => 'Terms of Service',
      'p1' => ':app is a core designed for personal and small/medium business applications.',
      'p2' => 'This sample page is kept to validate UI and flow, not as a legal draft.',
      'p3' => 'For production, you should add application-specific privacy policy and terms.',
      'title' => 'Terms of Service',
    ),
  ),
);
