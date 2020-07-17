<?php

namespace Krushed\Service\Output;

class DefaultOutputHandler implements OutputHandler
{
    public function render(string $output): string
    {
        return $output;
    }
}
