<?php

namespace Krushed\Service\Message;

class TwitchMessageEvent extends MessageEvent
{
    public function __construct(string $nickname, string $channel, string $message, callable $reply)
    {
        parent::__construct($nickname, $channel, $message, 'twitch', $reply);
    }
}
