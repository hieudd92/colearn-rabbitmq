<?php 

namespace CoLearn\RabbitMQ;

use CoLearn\RabbitMQ\Connectors\RabbitMQConnector;
use Illuminate\Support\ServiceProvider;

/**
 * Class RabbitMQServiceProvider
 * @package CoLearn\RabbitMQ
 */
class RabbitMQServiceProvider extends ServiceProvider
{
	/**
     * Register the Config provider
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/rabbitmq.php' => config_path('rabbitmq.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerManager();
    }

    /**
     * Register the queue manager.
     * and also register the Queue Connection
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app->singleton('rabbitmq.queue', function ($app) {
            $manager = new RabbitMQManager($app);
            $manager->addConnector('rabbitmq', function () {
	            return new RabbitMQConnector;
	        });
            return $manager;
        });

        $this->app->singleton('rabbitmq.queue.connection', function ($app) {
            return $app['rabbitmq.queue']->connection();
        });
    }
}