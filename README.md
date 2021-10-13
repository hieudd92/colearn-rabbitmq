# RabbitMQ

## Installation

Install via composer
```bash
composer require colearn/rabbitmq
```

### Publish package assets

```bash
php artisan vendor:publish --provider="CoLearn\RabbitMQ\RabbitMQServiceProvider"
```

## Security

If you discover any security related issues, please email
instead of using the issue tracker.

## How to use
####Server RPC as service
```
$this->rabbit = app('rabbitmq.queue')->connection('rabbitmq');
RabbitMQ::declareRPCServer($this->rabbit, 'name_of_queue', function ($request) {
    # code...
});
```
####Client RPC as service
```
$this->rabbit = app('rabbitmq.queue')->connection('rabbitmq');
$stringInput = json_encode(array);
$response = RabbitMQ::declareRPCClient($this->rabbit, 'name_of_queue', $stringInput);
```

####Server Worker Queue service
```
$this->rabbit = app('rabbitmq.queue')->connection('rabbitmq');
RabbitMQ::declareWorkerQueueServer($this->rabbit, 'name_of_queue', function ($request) {
    RabbitMQ::declareAck($request);
});
```
