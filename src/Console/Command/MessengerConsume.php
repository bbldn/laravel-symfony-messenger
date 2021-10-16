<?php

namespace BBLDN\Laravel\Messenger\Console\Command;

use Illuminate\Console\Command;
use Symfony\Component\Messenger\Worker;

class MessengerConsume extends Command
{
    /** @var string */
    protected $signature = 'messenger:consume';

    /** @var string */
    protected $description = 'Consumes messages';

    /**
     * @param Worker $worker
     * @return void
     */
    public function handle(Worker $worker): void
    {
        $worker->run();
    }
}