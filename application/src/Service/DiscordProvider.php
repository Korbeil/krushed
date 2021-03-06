<?php

namespace Krushed\Service;

use Discord\Discord;
use Discord\Parts\Channel\Message as DiscordMessage;
use Krushed\Service\Message\DiscordMessageEvent;
use Krushed\Service\Message\Message;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class DiscordProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private string $token;
    private EventDispatcherInterface $dispatcher;

    public function __construct(string $token, EventDispatcherInterface $dispatcher)
    {
        $this->token = $token;
        $this->dispatcher = $dispatcher;
    }

    public function create(): Discord
    {
        $client = new Discord(['token' => $this->token]);
        $client->on('ready', function (Discord $discord) {
            $this->logger->info('Discord provider connected.');
            $discord->on('message', function (DiscordMessage $message, Discord $discord) {
                $this->dispatcher->dispatch(new DiscordMessageEvent(
                    new Message($message->author, $message->channel_id, $message->content),
                    function (string $output) use ($message) {
                        $message->channel->sendMessage($output);
                    }
                ));
            });
        });

        return $client;
    }
}
