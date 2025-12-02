<?php

return [

    /*
    |--------------------------------------------------------------------------
    | JWT Authentication Secret
    |--------------------------------------------------------------------------
    |
    | Don't forget to set this in your .env file, as it will be used to sign
    | your tokens. A helper command is provided for this:
    | `php artisan jwt:secret`
    |
    */

    'secret' => env('JWT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | JWT Authentication Keys
    |--------------------------------------------------------------------------
    |
    | The algorithm you are using, will determine whether your tokens are
    | asymmetrical or symmetrical. If you have specified an asymmetrical
    | algorithm, this is the public key path.
    |
    */

    'keys' => [
        'public' => env('JWT_PUBLIC_KEY'),
        'private' => env('JWT_PRIVATE_KEY'),
        'passphrase' => env('JWT_PASSPHRASE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | JWT time to live
    |--------------------------------------------------------------------------
    |
    | Specify the length of time (in minutes) that the token will be valid for.
    | Defaults to 1 hour.
    |
    */

    'ttl' => env('JWT_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | JWT refresh time to live
    |--------------------------------------------------------------------------
    |
    | Specify the length of time (in minutes) that the token can be refreshed
    | within. Defaults to 2 weeks.
    |
    */

    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),

    /*
    |--------------------------------------------------------------------------
    | JWT blacklist grace period
    |--------------------------------------------------------------------------
    |
    | When a token is blacklisted, it will be added to this grace period,
    | allowing it to be used for a short time after it was blacklisted.
    | This is to allow for network latency and clock sync issues.
    |
    */

    'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 0),

    /*
    |--------------------------------------------------------------------------
    | JWT encryption algorithm
    |--------------------------------------------------------------------------
    |
    | Specify the algorithm used to sign the token.
    |
    | See https://github.com/namshi/jose/tree/master/src/Namshi/JOSE/Signer
    | for more details.
    |
    */

    'algo' => env('JWT_ALGO', 'HS256'),

    /*
    |--------------------------------------------------------------------------
    | JWT HTTP Header
    |--------------------------------------------------------------------------
    |
    | By default, the JWT will be sent in the Authorization header as a Bearer
    | token. You can change this behavior here.
    |
    */

    'http_header' => env('JWT_HTTP_HEADER', 'Authorization'),

    /*
    |--------------------------------------------------------------------------
    | JWT HTTP Header prefix
    |--------------------------------------------------------------------------
    |
    | By default, the JWT will be sent in the Authorization header with the
    | prefix 'Bearer '. You can change this behavior here.
    |
    */

    'http_header_prefix' => env('JWT_HTTP_HEADER_PREFIX', 'Bearer'),

    /*
    |--------------------------------------------------------------------------
    | JWT Query string
    |--------------------------------------------------------------------------
    |
    | By default, the JWT will be sent in the Authorization header. You can
    | also send it as a query string parameter.
    |
    */

    'query_string' => env('JWT_QUERY_STRING', 'token'),

    /*
    |--------------------------------------------------------------------------
    | JWT cookies
    |--------------------------------------------------------------------------
    |
    | By default, the JWT will be sent in the Authorization header. You can
    | also send it as a cookie.
    |
    */

    'cookies' => [
        'key' => env('JWT_COOKIE_KEY', 'token'),
        'secure' => env('JWT_COOKIE_SECURE', false),
        'same_site' => env('JWT_COOKIE_SAME_SITE', 'lax'),
        'path' => env('JWT_COOKIE_PATH', '/'),
        'domain' => env('JWT_COOKIE_DOMAIN', null),
        'http_only' => env('JWT_COOKIE_HTTP_ONLY', true),
    ],

];