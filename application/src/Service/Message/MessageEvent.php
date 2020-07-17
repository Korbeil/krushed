<?php

namespace Krushed\Service\Message;

use Symfony\Contracts\EventDispatcher\Event;

class MessageEvent extends Event
{
    private string $nickname;
    private string $channel;
    private string $message;
    private string $provider;
    /** @var callable */
    private $reply;

    public function __construct(string $nickname, string $channel, string $message, string $provider, callable $reply)
    {
        $this->nickname = $nickname;
        $this->channel = $channel;
        $this->message = $message;
        $this->provider = $provider;
        $this->reply = $reply;
    }

    public function getNickname(): string
    {
        return $this->nickname;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getMessage(): string
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
