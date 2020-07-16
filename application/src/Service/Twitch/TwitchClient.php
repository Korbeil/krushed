<?php

namespace Krushed\Service\Twitch;

class TwitchClient
{
    private const HOST = 'irc.chat.twitch.tv';
    private const PORT = 6667;

    private $socket = null;
    private string $channel;
    private string $token;

    public function __construct(string $token, string $channel)
    {
        $this->token = $token;
        $this->channel = $channel;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function connect()
    {
        $this->socket = \socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (\socket_connect($this->socket, static::HOST, static::PORT) === FALSE) {
            return null;
        }

        $this->authenticate();
        $this->setNick();
        $this->joinChannel($this->channel);
    }

    public function authenticate()
    {
        $this->send(sprintf("PASS %s", $this->token));
    }

    public function setNick()
    {
        $this->send(sprintf("NICK %s", 'bot'));
    }

    public function joinChannel($channel)
    {
        $this->send(sprintf("JOIN #%s", $channel));
    }

    public function getLastError()
    {
        return socket_last_error($this->socket);
    }

    public function isConnected()
    {
        return is_resource($this->socket);
    }

    public function read($size = 256)
    {
        if (!$this->isConnected()) {
            return null;
        }

        return \socket_read($this->socket, $size);
    }

    public function send($message)
    {
        if (!$this->isConnected()) {
            return null;
        }

        return \socket_write($this->socket, $message . "\n");
    }

    public function message($message)
    {
        return $this->send(sprintf("PRIVMSG #%s :%s", $this->channel, $message));
    }

    public function close()
    {
        if ($this->isConnected()) {
            \socket_close($this->socket);
        }
    }
}