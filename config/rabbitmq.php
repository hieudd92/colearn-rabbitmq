<?php

return [
    'default' => env('CUSTOM_QUEUE_DRIVER', 'rabbitmq'),
    'connections' => [
        'rabbitmq' => [
            'driver'                => 'rabbitmq',
            'host'                  => env('RABBITMQ_HOST', 'localhost'),
            'port'                  => env('RABBITMQ_PORT', 5672),
            'vhost'                 => env('RABBITMQ_VHOST', '/'),
            'login'                 => env('RABBITMQ_LOGIN', 'guest'),
            'password'              => env('RABBITMQ_PASSWORD', 'guest'),
            'queue'                 => env('RABBITMQ_QUEUE', 'rpc_queue'),
            'queue_declare_bind'    => env('RABBITMQ_QUEUE_DECLARE_BIND', true),
            'queue_params'          => [
                'passive'           => env('RABBITMQ_QUEUE_PASSIVE', false),
                'durable'           => env('RABBITMQ_QUEUE_DURABLE', true),
                'exclusive'         => env('RABBITMQ_QUEUE_EXCLUSIVE', false),
                'auto_delete'       => env('RABBITMQ_QUEUE_AUTODELETE', false),
            ],
        ],
    ]
];
