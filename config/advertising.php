<?php

return [
    'wallet_address'     => env('XMR_WALLET_ADDRESS', ''),
    'price_cache_ttl'    => env('XMR_PRICE_CACHE_TTL', 60),
    'xmr_precision'      => env('XMR_PRECISION', 6),
    'banner_price_usd'   => env('AD_BANNER_PRICE_USD', 200.00),
    'link_price_usd'     => env('AD_LINK_PRICE_USD', 100.00),
    'max_banners'        => env('MAX_ACTIVE_BANNERS', 10),
    'max_links'          => env('MAX_ACTIVE_LINKS', 20),
    'banner_width'       => 468,
    'banner_height'      => 60,
    'banner_max_bytes'   => 2097152,
    'fetch_timeout'      => 10,
    'price_api_primary'  => 'https://api.coingecko.com/api/v3/simple/price?ids=monero&vs_currencies=usd',
    'price_api_fallback' => 'https://min-api.cryptocompare.com/data/price?fsym=XMR&tsyms=USD',
];
