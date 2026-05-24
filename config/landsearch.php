<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image Download Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the image downloader service
    |
    */

    'images' => [
        // Maximum file size in bytes (default: 5MB)
        'max_size_bytes' => env('IMAGE_MAX_SIZE_BYTES', 5 * 1024 * 1024),

        // Request timeout in seconds
        'timeout_seconds' => env('IMAGE_TIMEOUT_SECONDS', 30),

        // Maximum images to download per post
        'max_per_post' => env('IMAGE_MAX_PER_POST', 10),

        // Storage disk to use
        'disk' => env('IMAGE_DISK', 'public'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Path Configuration
    |--------------------------------------------------------------------------
    */

    'path' => [
        // Directory within the storage disk
        'posts' => 'posts',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Agent
    |--------------------------------------------------------------------------
    |
    | User agent to use when downloading images
    |
    */

    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',

];
