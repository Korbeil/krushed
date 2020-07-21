<?php

namespace Krushed\Service\Message;

use Symfony\Contracts\EventDispatcher\Event;

class MessageEvent extends Event
{
    private Message $message;
    private string $provider;
    /** @var callable */
    private $reply;

    public function __construct(Message $message, string $provider, callable $reply)
    {
        $this->message = $message;
        $this->provider = $provider;
        $this->reply = $reply;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function reply(string $output): void
    {
        ($this->reply)($output);
    }
}
