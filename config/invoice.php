<?php

return [
    'issuer' => [
        'brand' => env('INVOICE_BRAND', 'SimplyHiree'),
        'legal_name' => env('INVOICE_LEGAL_NAME', 'SIMPLY HIREE PRIVATE LIMITED'),
        'office_address' => env('INVOICE_OFFICE_ADDRESS', '3rd Floor, B-23, B Block, Sector 62, Gautam Buddh Nagar, Uttar Pradesh 201301'),
        'state' => env('INVOICE_ISSUER_STATE', 'Uttar Pradesh'),
        'state_code' => env('INVOICE_ISSUER_STATE_CODE', '09'),
        'gstin' => env('INVOICE_ISSUER_GSTIN', '09ABSCS0529G1Z0'),
        'pan' => env('INVOICE_ISSUER_PAN', 'ABSCS0529G'),
        'email' => env('INVOICE_BILLING_EMAIL', 'support@simplyhiree.com'),
        'phone' => env('INVOICE_PHONE', '8888353984'),
        'bank_name' => env('INVOICE_BANK_NAME', 'YES Bank'),
        'bank_account_name' => env('INVOICE_BANK_ACCOUNT_NAME', 'Simply Hiree Pvt Ltd'),
        'bank_account_number' => env('INVOICE_BANK_ACCOUNT_NUMBER', '119826900000677'),
        'bank_ifsc' => env('INVOICE_BANK_IFSC', 'YESB0001198'),
        'authorised_signatory_name' => env('INVOICE_AUTHORISED_SIGNATORY_NAME', 'Aman Yadav'),
        'stamp_path' => env('INVOICE_STAMP_PATH', public_path('images/invoices/simplyhiree-stamp.png')),
    ],
    'payment_terms_days' => (int) env('INVOICE_PAYMENT_TERMS_DAYS', 15),
    'number_prefix' => env('INVOICE_NUMBER_PREFIX', 'SH/INV'),
    // Default for permanent placement. Other staffing models can override it later.
    'default_sac' => env('INVOICE_DEFAULT_SAC', '998512'),
    'default_service_description' => env('INVOICE_DEFAULT_SERVICE_DESCRIPTION', 'Permanent placement services, other than executive search services'),
];
