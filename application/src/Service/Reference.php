<?php

namespace Krushed\Service;

use Krushed\Entity\Streamer;
use Krushed\Repository\StreamerRepository;
use TwitchApi\TwitchApi;

class Reference
{
    private StreamerRepository $streamerRepository;
    private ?Streamer $streamer = null;
    private TwitchApi $twitchClient;

    public function __construct(StreamerRepository $streamerRepository, TwitchApi $twitchClient)
    {
        $this->streamerRepository = $streamerRepository;
        $this->twitchClient = $twitchClient;
    }

    private function getStreamer(): Streamer
    {
        if (null === $this->streamer) {
            /* @var Streamer|null $streamer */
            $this->streamer = $this->streamerRepository->findOneBy(['name' => 'birdlytv']); // @fixme
        }

        if (null === $this->streamer) {
            throw new \Exception('No related streamer found.');
        }

        return $this->streamer;
    }

    private function getToken(): string
    {
        $data = $this->twitchClient->validateAccessToken($this->getStreamer()->getToken());

        if (!$data['token']['valid']) {
            $data = $this->twitchClient->refreshToken($this->getStreamer()->getRefreshToken());

            $this->getStreamer()
                ->setToken($data['access_token'])
                ->setRefreshToken($data['refresh_token']);
        }

        return $this->getStreamer()->getToken();
    }

    public function getSubCount(): int
    {
        $data = $this->twitchClient->getChannelSubscribers($this->getStreamer()->getChannelId(), $this->getToken());

        return (int) $data['_total'];
    }

    public function uptime(): string
    {
        $data = $this->twitchClient->getStreamByUser($this->getStreamer()->getChannelId());
        if (null === $data['stream']) {
            return 'N/A';
        }

        $now = new \DateTimeImmutable();
        $startedAt = new \DateTimeImmutable($data['stream']['created_at']);
        $interval = $startedAt->diff($now);

        $output = '';
        if ($interval->d > 0) {
            $output .= $interval->d.'d ';
        }
        if ($interval->h > 0) {
            $output .= $interval->h.'h ';
        }
        if ($interval->i > 0) {
            $output .= $interval->i.'min ';
        }
        if ($interval->s > 0) {
            $output .= $interval->s.'s ';
        }

        return trim($output);
    }
}
