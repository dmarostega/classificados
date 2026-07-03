<?php

return [
    'default_title' => env('SEO_DEFAULT_TITLE', env('APP_NAME', 'Classificados')),
    'title_suffix' => env('SEO_TITLE_SUFFIX', ' | '.env('APP_NAME', 'Classificados')),
    'default_description' => env('SEO_DEFAULT_DESCRIPTION'),
    'default_image' => env('SEO_DEFAULT_IMAGE'),
    'twitter_card' => env('SEO_TWITTER_CARD', 'summary_large_image'),
    'robots' => env('SEO_ROBOTS', 'index,follow'),
];
