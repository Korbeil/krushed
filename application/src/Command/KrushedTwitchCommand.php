<?php

namespace Krushed\Command;

use Krushed\Service\TwitchProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class KrushedTwitchCommand extends Command
{
    private TwitchProvider $provider;

    protected static $defaultName = 'krushed:twitch';

    public function __construct(TwitchProvider $provider)
    {
        parent::__construct(null);
        $this->provider = $provider;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->success('Running Twitch handler.');

        $this->provider->create();

        return 0;
    }
}
