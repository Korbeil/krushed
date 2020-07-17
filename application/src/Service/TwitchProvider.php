<?php

namespace Krushed\Service;

use Krushed\Repository\CommandRepository;
use Krushed\Service\Twitch\TwitchClient;

class TwitchProvider
{
    private string $token;
    private string $channel;
    private CommandRepository $commandRepository;

    public function __construct(string $token, string $channel, CommandRepository $commandRepository)
    {
        $this->token = $token;
        $this->channel = $channel;
        $this->commandRepository = $commandRepository;
    }

    public function create(): void
    {
        $client = new TwitchClient($this->token, $this->channel);
        $client->connect();

        if (!$client->isConnected()) {
            var_dump('It was not possible to connect.');

            return;
        }

        while (true) {
            $content = $client->read(512);
            var_dump($content);

            if ('' !== $content) {
                $rows = explode("\n", $content);
                array_pop($rows); // last element is useless

                foreach ($rows as $row) {
                    $matches = [];
                    if (preg_match('#^\:(?<nickname>[a-z0-9]+)\!(?:[a-z0-9]+)\@(?:[a-z0-9]+)\.tmi\.twitch\.tv\ PRIVMSG \#(?<channel>[a-z]+)\ \:(?<message>.*)$#', $row, $matches)) {
                        $prefix = $matches['message'][0];
                        if ('$' !== $prefix) {
                            continue;
                        }
                        $command = mb_substr(trim($matches['message']), 1, mb_strlen(trim($matches['message'])) - 1);
                        $commandOutput = $this->commandRepository->getOutputByCommandNameForTwitch($command);

                        if (\is_string($commandOutput)) {
                            $client->message($commandOutput);
                        }
                    }
                }
            }

            usleep(200);
        }
    }
}
