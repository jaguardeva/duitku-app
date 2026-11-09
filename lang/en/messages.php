<?php

return [
    'locale' => [
        'updated' => 'Language updated successfully.',
    ],
    'profile' => [
        'updated' => 'Profile information updated successfully.',
        'password_updated' => 'Your password has been updated successfully.',
    ],
    'auth' => [
        'suspended' => 'Your account has been suspended. Please contact an administrator.',
    ],
    'otp' => [
        'wait' => 'Please wait :seconds seconds before requesting a new code.',
        'no_active_code' => 'There is no active verification code. Please request a new code.',
        'expired' => 'The verification code has expired. Please request a new code.',
        'attempts_exhausted' => 'The verification attempts have been exhausted. Please request a new code.',
        'verified' => 'Your email has been verified successfully.',
        'incorrect_exhausted' => 'The code is incorrect. The attempts limit has been reached. Please request a new code.',
        'incorrect_remaining' => 'The verification code is incorrect. Remaining attempts: :remaining.',
        'resent' => 'A new verification code has been sent to your email.',
    ],
    'sessions' => [
        'not_found' => 'The session was not found or has already expired.',
        'current' => 'You cannot revoke the session you are currently using.',
        'revoked' => 'The device session was revoked successfully.',
        'others_revoked' => 'All other device sessions were revoked successfully.',
        'all_revoked' => 'You have been signed out from all devices. Please sign in again.',
    ],
    'validation' => [
        'password_no_spaces' => 'The password must not contain spaces.',
    ],
    'wallet' => [
        'created' => 'Wallet ":name" was created successfully.',
        'updated' => 'Wallet ":name" was updated successfully.',
        'archived' => 'Wallet ":name" was archived successfully.',
        'restored' => 'Wallet ":name" was restored successfully.',
        'deleted' => 'Wallet ":name" was deleted successfully.',
        'cannot_delete' => 'A wallet with transaction or transfer history cannot be deleted. Please archive it instead.',
    ],
    'category' => [
        'created' => 'Category ":name" was created successfully.',
        'updated' => 'Category ":name" was updated successfully.',
        'archived' => 'Category ":name" was archived successfully.',
        'restored' => 'Category ":name" was restored successfully.',
        'deleted' => 'Category ":name" was deleted successfully.',
        'cannot_delete' => 'A category used in transactions or budgets cannot be deleted. Please archive it instead.',
        'invalid_budget' => 'The budget category must be a valid expense category.',
    ],
    'transaction' => [
        'wallet_invalid' => 'The wallet is invalid or archived.',
        'wallet_missing' => 'The wallet is invalid.',
        'category_invalid' => 'The category does not match the transaction type or is archived.',
        'category_mismatch' => 'The category does not match the transaction type.',
        'income_created' => 'Income was recorded successfully.',
        'expense_created' => 'Expense was recorded successfully.',
        'updated' => 'Transaction was updated successfully.',
        'deleted' => 'Transaction was deleted successfully.',
    ],
    'transfer' => [
        'source_invalid' => 'The source wallet is invalid or archived.',
        'destination_invalid' => 'The destination wallet is invalid or archived.',
        'currency_mismatch' => 'Transfers between different currencies are not supported. Both wallets must use the same currency.',
        'insufficient_balance' => 'The source wallet balance is insufficient for this transfer.',
        'created' => 'The wallet transfer was completed successfully.',
        'deleted' => 'The transfer record was deleted successfully.',
    ],
    'budget' => [
        'created' => 'Budget was created successfully.',
        'updated' => 'Budget was updated successfully.',
        'deleted' => 'Budget was deleted successfully.',
    ],
    'admin' => [
        'cannot_suspend_self' => 'You cannot suspend your own administrator account.',
        'user_suspended' => 'User account ":name" was suspended successfully.',
        'user_restored' => 'User account ":name" was restored successfully.',
        'system_category_created' => 'System category ":name" was added successfully.',
        'system_category_updated' => 'System category ":name" was updated successfully.',
        'version_updated' => 'Application version was updated successfully.',
    ],
];
