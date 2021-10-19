<?php

namespace CoLearn\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQ
{
    protected $connection;
    public $channel;
    protected $callback;
    protected $defaultQueue;
    private static $corr_id;
    private static $response;

    /**
     * RabbitMQ constructor.
     * @param AMQPStreamConnection $amqpConnection
     * @param $config
     */
    public function __construct(AMQPStreamConnection $amqpConnection, $config)
    {
        $this->connection = $amqpConnection;
        $this->defaultQueue = $config['queue'];
        $this->channel = $this->getChannel();
    }

    /**
     * @param string $queue
     *
     * @return string
     */
    public static function getQueueName($queue)
    {
        return $queue ?? null;
    }

    /**
     * @return AMQPChannel
     */
    private function getChannel()
    {
        return $this->connection->channel();
    }

    /**
     * @param $_this
     * @param $name
     * @param $callback
     */
    public static function declareRPCServer($_this, $name, $callback)
    {
        $name = RabbitMQ::getQueueName($name);

        $_this->channel->queue_declare($name, false, true, false, false);
        $_this->channel->basic_qos(null, 1, null);
        $_this->channel->basic_consume($name, '', false, true, false, false, $callback);

        while ($_this->channel->is_open()) {
            $_this->channel->wait();
        }
        self::close($_this);
    }

    /**
     * @param $_this
     * @param $name
     * @param $callback
     */
    public static function declareWorkerQueueServer($_this, $name, $callback)
    {
        $_this->channel->queue_declare($name, false, true, false, false);

        $_this->channel->basic_qos(null, 1, null);
        $_this->channel->basic_consume($name, '', false, false, false, false, $callback);

        while ($_this->channel->is_open()) {
            $_this->channel->wait();
        }
        self::close($_this);
    }

    /**
     * @param $request
     * @param string $string
     */
    public static function replyTo($request, $string = "[]")
    {
        $msg = new AMQPMessage(
            $string,
            array('correlation_id' => $request->get('correlation_id'))
        );
        $request->delivery_info['channel']->basic_publish(
            $msg,
            '',
            $request->get('reply_to')
        );
        // $request->ack();
    }

    /**
     * [declareAck description]
     * @param $request
     * @return void
     */
    public static function declareAck($request)
    {
        $request->delivery_info['channel']->basic_ack($request->delivery_info['delivery_tag']);
    }

    /**
     * @param $_this
     * @param $name
     * @param $stringInput
     * @return mixed
     */
    public static function declareRPCClient($_this, $name, $stringInput)
    {
        RabbitMQ::$corr_id = uniqid();
        $name = RabbitMQ::getQueueName($name);
        try {
            list($_this->callback_queue, ,) = $_this->channel->queue_declare("", false, false, true, false );
            $_this->channel->basic_consume( $_this->callback_queue, '', false, false, false, false,
                array(
                    $_this,
                    'onResponse'
                )
            );
            $msg = new AMQPMessage(
                (string)$stringInput,
                array(
                    'correlation_id' => RabbitMQ::$corr_id,
                    'reply_to' => $_this->callback_queue
                )
            );
            $_this->channel->basic_publish($msg, '', $name);
            while (!RabbitMQ::$response) {
                try {
                    $_this->channel->wait(null, false, 2);
                } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
                    self::close($_this);
                    return null;
                }
            }
            self::close($_this);
            return json_decode(RabbitMQ::$response, true);
        } catch (\Exception $e){
            self::close($_this);
            return null;
        }
    }
    /**
     * @param $response
     */
    public function onResponse($response)
    {
        if ($response->get('correlation_id') == RabbitMQ::$corr_id) {
            RabbitMQ::$response = $response->body;
        }
    }

    public static function close($_this=null)
    {
        if($_this != null) {
            $_this->channel->close();
            $_this->close();
        }
    }
}
