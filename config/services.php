<?php

return [

    'firebase' => [
        'project_id'  => env('FIREBASE_PROJECT_ID'),
        'credentials' => env('FIREBASE_CREDENTIALS', 'storage/app/firebase-service-account.json'),
    ],

    'jazzcash' => [
        'merchant_id'    => env('JAZZCASH_MERCHANT_ID'),
        'password'       => env('JAZZCASH_PASSWORD'),
        'integrity_salt' => env('JAZZCASH_INTEGRITY_SALT'),
        'endpoint'       => 'https://sandbox.jazzcash.com.pk/ApplicationAPI/API/2.0/Purchase/DoMWalletTransaction',
    ],

    'easypaisa' => [
        'store_id'  => env('EASYPAISA_STORE_ID'),
        'hash_key'  => env('EASYPAISA_HASH_KEY'),
        'endpoint'  => 'https://easypaisaapi.com/payments/initiate',
    ],

];
