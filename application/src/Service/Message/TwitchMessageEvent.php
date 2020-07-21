<?php

namespace Krushed\Service\Message;

class TwitchMessageEvent extends MessageEvent
{
    public function __construct(Message $message, callable $reply)
    {
        parent::__construct($message, 'twitch', $reply);
    }
}
