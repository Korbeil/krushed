<?php

namespace Krushed\Service\Message;

class Message
{
    public string $sender;
    /** @var int|string */
    public $channel;
    public string $originalMessage;
    public string $message;

    /**
     * @param string|int $channel Will be channel_id for Discord and streamer.name for Twitch
     */
    public function __construct(string $sender, $channel, string $message)
    {
        $this->sender = $sender;
        $this->channel = $channel;
        $this->originalMessage = $this->message = $message;
    }

    public function getFirstWord(): string
    {
        $parts = explode(' ', $this->originalMessage);

        return $parts[0];
    }

    public function getParameter(): ?string
    {
        $parts = explode(' ', $this->originalMessage);

        return \array_key_exists(1, $parts) ? $parts[1] : null;
    }

    public function uniqueCommandIdentifier(): string
    {
        return sha1($this->getFirstWord());
    }
}
