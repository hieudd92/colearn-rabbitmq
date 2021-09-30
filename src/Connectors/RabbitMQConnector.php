<?php

namespace CoLearn\RabbitMQ\Connectors;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use CoLearn\RabbitMQ\RabbitMQ;

use Illuminate\Queue\Connectors\ConnectorInterface;

class RabbitMQConnector implements ConnectorInterface
{
    protected $connection;

    /**
     * Establish a queue connection.
     *
     * @param  array $config
     *
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        // create connection with AMQP
        $this->connection = new AMQPStreamConnection($config['host'], $config['port'], $config['login'], $config['password'], $config['vhost']);

        return new RabbitMQ(
            $this->connection,
            $config
        );
    }

    public function getConnection()
    {
        return $this->connection;
    }
}
