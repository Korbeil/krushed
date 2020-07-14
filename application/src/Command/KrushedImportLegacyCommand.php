<?php

namespace Krushed\Command;

use Doctrine\ORM\EntityManagerInterface;
use Krushed\Entity\Command;
use Krushed\Repository\CommandRepository;
use Krushed\Service\StreamElements;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class KrushedImportLegacyCommand extends BaseCommand
{
    private StreamElements $streamElements;
    private EntityManagerInterface $entityManager;

    protected static $defaultName = 'krushed:import_legacy';

    public function __construct(StreamElements $streamElements, EntityManagerInterface $entityManager)
    {
        $this->streamElements = $streamElements;
        $this->entityManager = $entityManager;
        parent::__construct(null);
    }

    protected function configure()
    {
        $this->setDescription('Import commands from StreamElements API');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $imported = 0;
        $io = new SymfonyStyle($input, $output);

        $commands = $this->streamElements->getCustomCommands();
        $io->comment(sprintf('Found %d commands, importing ...', \count($commands)));

        /** @var CommandRepository $commandRepository */
        $commandRepository = $this->entityManager->getRepository(Command::class);

        foreach ($commands as $command) {
            if (!$command['enabled']) {
                continue;
            }

            $existingCommand = $commandRepository->findOneBy(['name' => $command['command']]);
            if ($existingCommand instanceof Command) {
                continue;
            }

            $newCommand = new Command();
            $newCommand->setName($command['command']);
            $newCommand->setOutput($command['reply']);
            $newCommand->setCooldown($command['cooldown']['user']);
            $newCommand->setEnabled(Command::ENABLED_DISCORD | Command::ENABLED_TWITCH);

            $this->entityManager->persist($newCommand);
            ++$imported;
        }

        $this->entityManager->flush();
        $io->success(sprintf('Imported %d commands !', $imported));

        return 0;
    }
}
