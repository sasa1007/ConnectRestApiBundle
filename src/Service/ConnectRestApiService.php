<?php

namespace BeckUp\ConnectRestApiBundle\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ConnectRestApiService
{
    public function __construct(
        private HttpClientInterface $client,
        private ParameterBagInterface $params
    ) {
    }

    public function connector(string $method, string $url, ?array $data = null)
    {
        $username = $this->params->get('connect_rest_api.username');
        $password = $this->params->get('connect_rest_api.password');

        $response = $this->client->request($method, $url, [
            'auth_basic' => [$username, $password],
            'body' => $data ? json_encode($data) : null,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        return $response;
    }


}
