<?php

namespace Krushed\Service\Output;

use Krushed\Service\Message\Message;

class DefaultOutputHandler implements OutputHandler
{
    public function render(Message $message): string
    {
        return $message->message;
    }
}
