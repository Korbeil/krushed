<?php

namespace Krushed\Service;

use Symfony\Component\HttpClient\CurlHttpClient;

class StreamElements
{
    private string $channelId;
    private string $token;

    public function __construct(string $channelId, string $token)
    {
        $this->channelId = $channelId;
        $this->token = $token;
    }

    public function getCustomCommands(): array
    {
        $client = new CurlHttpClient();
        $response = $client->request('GET', 'https://api.streamelements.com/kappa/v2/bot/commands/' . $this->channelId, [
            'headers' => ['Authorization' => 'Bearer ' . $this->token]
        ]);

        return $response->toArray();
    }
}
