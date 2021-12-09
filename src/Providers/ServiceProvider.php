<?php

namespace BBLDN\Laravel\Messenger\Providers;

use Illuminate\Log\Logger;
use Laravel\Lumen\Application;
use Symfony\Component\Messenger\Worker;
use Symfony\Component\Messenger\MessageBus;
use BBLDN\Laravel\Messenger\DispatcherPool;
use Illuminate\Support\ServiceProvider as Base;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use BBLDN\Laravel\Messenger\Console\Command\MessengerConsume;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

class ServiceProvider extends Base
{
    /** @var Application */
    protected $app;

    /**
     * @return void
     */
    private function loadConfig(): void
    {
        $this->app->configure('messenger');
    }

    /**
     * @return void
     */
    private function registerCommandList(): void
    {
        $this->commands(MessengerConsume::class);
    }

    /**
     * @return void
     */
    private function registerDispatcherPoolAndWorker(): void
    {
        $serializerClass = config('messenger.serializer');
        if (null === $serializerClass || false === class_exists($serializerClass)) {
            return;
        }

        $sender = config('messenger.sender');
        if (null === $sender || false === is_callable($sender)) {
            return;
        }

        $receiver = config('messenger.receiver');
        if (null === $receiver || false === is_callable($receiver)) {
            return;
        }

        $serializer = new $serializerClass();

        $this->app->bind(DispatcherPool::class, function () use ($serializer, $sender) {
            $senders = [];
            foreach (config('messenger.queues', []) as $key => $queue) {
                $senders[$key] = $sender($queue, $serializer);
            }

            return new DispatcherPool($senders);
        });

        $this->app->bind(Worker::class, function () use ($serializer, $receiver) {
            $receivers = [];
            foreach (config('messenger.queues', []) as $queue) {
                $receivers[] = $receiver($queue, $serializer);
            }

            $handlers = $this->getHandlerList();
            $middleware = [new HandleMessageMiddleware(new HandlersLocator($handlers), false)];
            $messageBus = new MessageBus($middleware);

            $eventDispatcher = null;

            /** @var Logger $logger */
            $logger = $this->app->make(Logger::class);

            return new Worker($receivers, $messageBus, $eventDispatcher, $logger);
        });
    }

    /**
     * @return HandlerDescriptor[]
     *
     * @psalm-return list<HandlerDescriptor>
     */
    private function getHandlerList(): array
    {
        return array_map(function (array $handlerClasses) {
            return array_map(function (string $handlerClass) {
                return new HandlerDescriptor(function ($message) use ($handlerClass) {
                    return app()->make($handlerClass)->handle($message);
                });
            }, $handlerClasses);
        }, config('messenger.handlers', []));
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->loadConfig();
        $this->registerDispatcherPoolAndWorker();
        $this->registerCommandList();
    }
}