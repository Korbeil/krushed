<?php

namespace Krushed\Service\Output;

use Krushed\Service\Message\Message;

interface OutputHandler
{
    public function render(Message $message): string;
}
