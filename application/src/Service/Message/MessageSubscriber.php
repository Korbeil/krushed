<?php

namespace Krushed\Service\Message;

use Krushed\Repository\CommandRepository;
use Krushed\Service\Output\OutputHandler;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MessageSubscriber implements EventSubscriberInterface
{
    private string $prefix;
    private ContainerInterface $outputHandlers;
    private CommandRepository $commandRepository;

    public function __construct(string $prefix, ContainerInterface $outputHandlers, CommandRepository $commandRepository)
    {
        $this->prefix = $prefix;
        $this->outputHandlers = $outputHandlers;
        $this->commandRepository = $commandRepository;
    }

    public function onMessageEvent(MessageEvent $event)
    {
        $prefix = $event->getMessage()[0];
        if ($this->prefix !== $prefix) {
            return;
        }
        $command = mb_substr($event->getMessage(), 1, mb_strlen($event->getMessage()) - 1);

        $repositoryMethod = sprintf('getOutputByCommandNameFor%s', ucfirst($event->getProvider()));
        $command = $this->commandRepository->$repositoryMethod($command);

        if (\is_array($command)) {
            /** @var OutputHandler $outputHandler */
            $outputHandler = $this->outputHandlers->get($command['handler']);
            $event->reply($outputHandler->render($command['output']));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            DiscordMessageEvent::class => 'onMessageEvent',
            TwitchMessageEvent::class => 'onMessageEvent',
        ];
    }
}
