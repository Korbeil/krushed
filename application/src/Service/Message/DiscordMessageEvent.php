<?php

namespace Krushed\Service\Message;

class DiscordMessageEvent extends MessageEvent
{
    public function __construct(Message $message, callable $reply)
    {
        parent::__construct($message, 'discord', $reply);
    }
}
