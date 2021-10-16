<?php

namespace BBLDN\Laravel\Messenger;

use Exception;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;

final class DispatcherPool
{
    /**
     * @var SenderInterface[]
     *
     * @psalm-var array<string, SenderInterface>
     */
    private array $pool;

    /**
     * @param SenderInterface[] $pool
     *
     * @psalm-var array<string, SenderInterface> $pool
     */
    public function __construct(array $pool)
    {
        $this->pool = $pool;
    }

    /**
     * @param string $channel
     * @param mixed $message
     * @throws Exception
     */
    public function send(string $channel, $message): void
    {
        if (false === is_a($message, Envelope::class)) {
            $message = new Envelope($message);
        }

        $sender = $this->getSender($channel);
        $sender->send($message);
    }

    /**
     * @param string $channel
     * @return SenderInterface
     * @throws Exception
     */
    private function getSender(string $channel): SenderInterface
    {
        if (false === key_exists($channel, $this->pool)) {
            throw new Exception("Unknown channel: $channel");
        }

        $sender = $this->pool[$channel];
        if (null === $sender) {
            throw new Exception("Unknown channel: $channel");
        }

        if (false === is_a($sender, SenderInterface::class)) {
            throw new Exception("Channel $channel should implements SenderInterface");
        }

        return $sender;
    }
}