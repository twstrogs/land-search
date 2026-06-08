<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | Your Cloudinary credentials. Get these from:
    | https://console.cloudinary.com/app/settings/api-keys
    |
    */

    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key' => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Upload Settings
    |--------------------------------------------------------------------------
    */

    'upload' => [
        // Folder name in Cloudinary (optional)
        'folder' => env('CLOUDINARY_UPLOAD_FOLDER', 'land-search/posts'),

        // Allowed file types
        'allowed_formats' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],

        // Auto-optimize images
        'transformation' => [
            'quality' => 'auto',
            'fetch_format' => 'auto',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image URL Settings
    |--------------------------------------------------------------------------
    */

    'url' => [
        // Secure delivery
        'secure' => true,

        // Default transformation for display
        'default_transformation' => 'f_auto,q_auto',
    ],

];
