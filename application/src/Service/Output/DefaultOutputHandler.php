<?php

namespace Krushed\Service\Output;

use Krushed\Service\Message\Message;
use Krushed\Service\Reference;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class DefaultOutputHandler implements OutputHandler
{
    private Reference $reference;
    private AdapterInterface $adapter;

    public function __construct(Reference $reference, AdapterInterface $adapter)
    {
        $this->reference = $reference;
        $this->adapter = $adapter;
    }

    public function render(Message $message): string
    {
        $this->process($message);

        return $message->message;
    }

    private function process(Message $message): void
    {
        $commands = [
            'twitch subcount' => \Closure::fromCallable([$this->reference, 'getSubCount']),
            'uptime' => \Closure::fromCallable([$this->reference, 'uptime']),
            'count' => fn (Message $message) => $this->countCommand($message),
            'user' => fn () => $message->sender,
            'sender' => fn () => $message->sender,
            'me' => fn () => $message->sender,
        ];

        $matches = [];
        if (preg_match('#\$\(1\|\$\((?<command>.*?)\)\)#', $message->message, $matches)) {
            $message->message = str_replace(sprintf('$(1|$(%s))', $matches['command']), $message->getParameter() ?? $commands[$matches['command']]($message), $message->message);
        }

        /**
         * @var string   $command
         * @var \Closure $closure
         */
        foreach ($commands as $command => $closure) {
            if (false !== strpos($message->message, '$('.$command.')')) {
                $message->message = str_replace('$('.$command.')', $closure($message), $message->message);
            }
        }
    }

    private function countCommand(Message $message): int
    {
        $cacheItem = $this->adapter->getItem(sprintf('count_%s', $message->uniqueCommandIdentifier()));
        if (!$cacheItem->isHit()) {
            $cacheItem->set(1);
        } else {
            $cacheItem->set($cacheItem->get() + 1);
        }
        $this->adapter->save($cacheItem);

        return $cacheItem->get();
    }
}
