<?php

use BBLDN\Laravel\Messenger\Serializers\TransportJsonSerializer;
use Symfony\Component\Messenger\Bridge\Redis\Transport\Connection;
use Symfony\Component\Messenger\Bridge\Redis\Transport\RedisSender;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Messenger\Bridge\Redis\Transport\RedisReceiver;

return [
    'handlers' => [],
    'serializer' => TransportJsonSerializer::class,
    'sender' => static fn(string $queue, Serializer $serializer) => new RedisSender(Connection::fromDsn($queue), $serializer),
    'receiver' => static fn(string $queue, Serializer $serializer) => new RedisReceiver(Connection::fromDsn($queue), $serializer),

    'queues' => [
        'failed' => env('FAILED_MESSENGER_TRANSPORT_DSN'),
        'default' => env('DEFAULT_MESSENGER_TRANSPORT_DSN'),
    ],
];
