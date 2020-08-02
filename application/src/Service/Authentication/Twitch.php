<?php

namespace Krushed\Service\Authentication;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use TwitchApi\TwitchApi;

class Twitch
{
    private string $clientId;
    private string $clientSecret;
    private UrlGeneratorInterface $urlGenerator;
    private TwitchApi $client;

    private const TWITCH_DOMAIN = 'https://id.twitch.tv';
    private const AUTHORIZE_PATH = '/oauth2/authorize';
    private const TOKEN_PATH = '/oauth2/token';

    private const SCOPES = [
        'channel_check_subscription',
        'channel_feed_read',
        'channel_subscriptions',
    ];

    public function __construct(string $clientId, string $clientSecret, TwitchApi $twitchClient, UrlGeneratorInterface $urlGenerator)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->urlGenerator = $urlGenerator;
        $this->client = $twitchClient;
    }

    public function getAuthorizeUrl(string $state): string
    {
        $parameters = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->urlGenerator->generate('twitch_authorize_callback', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'response_type' => 'code',
            'scope' => implode(' ', static::SCOPES),
            'state' => $state,
            'force_verify' => 1,
        ];

        return sprintf('%s%s?%s', static::TWITCH_DOMAIN, static::AUTHORIZE_PATH, http_build_query($parameters));
    }

    public function getTokens(string $nickname, string $code): array
    {
        $parameters = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->urlGenerator->generate('twitch_authorize_callback', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        $client = HttpClient::create(['base_uri' => static::TWITCH_DOMAIN]);
        $response = $client->request('POST', static::TOKEN_PATH, ['query' => $parameters]);
        $response = $response->toArray();

        $userData = $this->client->getUserByUsername($nickname);
        [$userData] = $userData['users'];
        $response['channel_id'] = $userData['_id'];

        return $response;
    }
}
