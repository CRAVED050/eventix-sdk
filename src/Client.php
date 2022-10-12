<?php

namespace Janyk\Eventix;

use GuzzleHttp\Client as GuzzleClient;
use Janyk\Eventix\Exceptions\RefreshTokenHasExpired;
use Janyk\Eventix\Models\AbstractModel;
use Janyk\Eventix\Types\RequestMethod;

class Client
{
    const AUTH_BASE_URI = 'https://auth.openticket.tech/';
    const BASE_URI = 'https://api.eventix.io/v1/';

    private GuzzleClient $client;

    private \DateTime $expiresAt;
    private \DateTime $refreshExpiresAt;

    /**
     * Initialize the Eventix client.
     *
     * @param string $clientId              The Eventix / OpenTicket client ID.
     * @param string $clientSecret          The Eventix / OpenTicket client secret.
     * @param string|null $accessToken      An existing access token.
     * @param string|null $refreshToken     An existing refresh token.
     */
    public function __construct(public string  $clientId,
                                public string  $clientSecret,
                                public string  $redirectUri,
                                public ?string $accessToken     = null,
                                public ?string $refreshToken    = null)
    {
        $this->client = new GuzzleClient([
            'base_uri' => self::BASE_URI,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        if(! empty($this->refreshToken)) {
            $this->refreshToken();
        }
    }

    /**
     * Get the authorization URL.
     *
     * @return string The authorization URL.
     */
    public function redirect(): string
    {
        $query = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
        ]);

        return self::AUTH_BASE_URI . 'token/authorize?' . $query;
    }

    /**
     * Exchange the authorization code for an access & refresh token.
     *
     * @param string $authorizationCode     The authorization code.
     * @return bool                         True if the token was successfully exchanged and stored in the client.
     */
    public function authorize(string $authorizationCode): bool
    {
        $response = $this->client->post(self::AUTH_BASE_URI . '/token', [
            'form_params' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'authorization_code',
                'code' => $authorizationCode,
                'redirect_uri' => $this->redirectUri,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if($response->getStatusCode() !== 200) {
            return false;
        }

        $this->setTokens($data);

        return true;
    }

    /**
     * Refresh the access token.
     *
     * @return bool True if the access token was successfully refreshed.
     * @throws RefreshTokenHasExpired If the refresh token has expired.
     */
    public function refresh(): bool
    {
        if($this->refreshTokenIsExpired()) {
            throw new RefreshTokenHasExpired();
        }

        $response = $this->client->post(self::AUTH_BASE_URI . '/token', [
            'form_params' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->refreshToken,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if($response->getStatusCode() !== 200) {
            return false;
        }

        $this->setTokens($data);

        return true;
    }

    private function setTokens(array $data): void
    {
        $this->accessToken = $data['access_token'];
        $this->refreshToken = $data['refresh_token'];
        $this->expiresAt = (new \DateTime())->add(new \DateInterval('PT' . $data['expires_in'] . 'S'));
        $this->refreshExpiresAt = (new \DateTime())->add(new \DateInterval('PT' . $data['refresh_token_expires_in'] . 'S'));
    }

    /**
     * Check if the access token is expired.
     *
     * @return bool True if the access token is expired.
     */
    public function accessTokenIsExpired(): bool
    {
        return $this->expiresAt < new \DateTime();
    }

    /**
     * Check if the refresh token is expired.
     *
     * @return bool True if the refresh token is expired.
     */
    public function refreshTokenIsExpired(): bool
    {
        return $this->refreshExpiresAt < new \DateTime();
    }

    /**
     * @param RequestMethod $method
     * @param string $uri
     * @param AbstractModel $result
     * @param array $parameters
     * @return AbstractModel|array
     * @throws RefreshTokenHasExpired
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(RequestMethod $method, string $uri, AbstractModel $result, array $parameters = []): AbstractModel|array
    {
        if($this->accessTokenIsExpired()) {
            $this->refresh();
        }

        $response = $this->client->request($method->value, $uri, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
            ],
            'json' => $parameters,
        ]);

        $data = json_decode($response->getBody()->getContents());

        if(is_array($data)) {
            return array_map(fn($item) => $result->fromArray($item), $data);
        }

        return $result->fromArray($data);
    }

}