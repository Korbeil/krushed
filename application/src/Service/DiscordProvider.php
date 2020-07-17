<?php

namespace Krushed\Service;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Krushed\Repository\CommandRepository;

class DiscordProvider
{
    private string $token;
    private CommandRepository $commandRepository;

    public function __construct(string $token, CommandRepository $commandRepository)
    {
        $this->token = $token;
        $this->commandRepository = $commandRepository;
    }

    public function create(): Discord
    {
        $client = new Discord(['token' => $this->token]);
        $client->on('ready', function (Discord $discord) {
            $discord->on('message', function (Message $message, Discord $discord) {
                $prefix = $message->content[0];
                if ('$' !== $prefix) {
                    return;
                }
                $command = mb_substr($message->content, 1, mb_strlen($message->content) - 1);
                $commandOutput = $this->commandRepository->getOutputByCommandNameForDiscord($command);

                if (\is_string($commandOutput)) {
                    $message->channel->sendMessage($commandOutput);
                }
            });
        });

        return $client;
    }
}
