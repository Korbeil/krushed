<?php

namespace Krushed\Service\Output;

interface OutputHandler
{
    public function render(string $output): string;
}
