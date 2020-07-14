<?php

namespace Krushed\Command;

use Krushed\Service\DiscordProvider;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class KrushedDiscordCommand extends BaseCommand
{
    private DiscordProvider $provider;

    protected static $defaultName = 'krushed:discord';

    public function __construct(DiscordProvider $provider)
    {
        parent::__construct(null);
        $this->provider = $provider;
    }

    protected function configure()
    {
        $this->setDescription('Run discord handler');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->success('Running Discord handler.');

        $client = $this->provider->create();
        $client->run();

        return 0;
    }
}
