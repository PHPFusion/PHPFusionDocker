<?php
/**
 * Here you can configure additional system settings
 */
$config_inc = [
    'cache' => [
        'storage'        => 'file', // file|redis|memcache
        'memcache_hosts' => ['localhost:11211'], // e.g. ['localhost:11211', '192.168.1.100:11211', 'unix:///var/tmp/memcached.sock']
        'redis_hosts'    => ['localhost:6379'], // e.g. ['localhost:6379', '192.168.1.100:6379:1:passwd']
        'path'           => BASEDIR.'cache/system/' // for FileCache
    ]
];
