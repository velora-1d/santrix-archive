<?php

return [
    'central_domains' => array_filter(explode(',', env('CENTRAL_DOMAINS', 'santrix.my.id,santrix.test,localhost,127.0.0.1'))),
];
