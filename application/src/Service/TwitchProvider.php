<?php

namespace Krushed\Service;

use Krushed\Service\Message\Message;
use Krushed\Service\Message\TwitchMessageEvent;
use Krushed\Service\Twitch\TwitchClient;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class TwitchProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private string $token;
    private string $channel;
    private EventDispatcherInterface $dispatcher;

    public function __construct(string $token, string $channel, EventDispatcherInterface $dispatcher)
    {
        $this->token = $token;
        $this->channel = $channel;
        $this->dispatcher = $dispatcher;
    }

    public function create(): void
    {
        $client = new TwitchClient($this->token, $this->channel);
        $client->connect();

        if (!$client->isConnected()) {
            $this->logger->critical('It was not possible to connect.');

            return;
        }
        $this->logger->info('Twitch provider connected.');

        while (true) {
            $content = $client->read(512);

            if ('' !== $content) {
                $rows = explode("\n", $content);
                array_pop($rows); // last element is useless

                foreach ($rows as $row) {
                    $matches = [];
                    if (preg_match('#^\:(?<nickname>[a-z0-9]+)\!(?:[a-z0-9]+)\@(?:[a-z0-9]+)\.tmi\.twitch\.tv\ PRIVMSG \#(?<channel>[a-z]+)\ \:(?<message>.*)$#', $row, $matches)) {
                        $this->dispatcher->dispatch(new TwitchMessageEvent(
                            new Message($matches['nickname'], $matches['channel'], trim($matches['message'])),
                            function (string $output) use ($client) {
                                $client->message($output);
                            }
                        ));
                    }
                }
            }

            usleep(200);
        }
    }
}
