<?php

namespace Backend2Plus\ConnectRestApiBundle\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use InvalidArgumentException;

/**
 * Service za povezivanje sa REST API-jevima sa Basic Authentication podrškom.
 */
class ConnectRestApiService
{
    /**
     * Podržane HTTP metode
     */
    private const SUPPORTED_METHODS = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'];

    public function __construct(
        private HttpClientInterface $client,
        private ParameterBagInterface $params
    ) {
    }

    /**
     * Šalje HTTP zahtev na određeni endpoint sa Basic Authentication.
     *
     * @param string $method HTTP metoda (GET, POST, PUT, DELETE, PATCH, HEAD, OPTIONS)
     * @param string $url URL endpoint-a
     * @param array|null $data Podaci za slanje (za POST, PUT, PATCH metode)
     * @param array $options Dodatne opcije za HTTP zahtev
     * 
     * @return ResponseInterface Symfony HTTP Client response objekat
     * 
     * @throws InvalidArgumentException Ako je metoda ili URL nevalidan
     * @throws TransportExceptionInterface Ako dođe do network greške
     * @throws HttpExceptionInterface Ako server vrati HTTP grešku
     */
    public function connector(string $method, string $url, ?array $data = null, array $options = []): ResponseInterface
    {
        // Validacija HTTP metode
        $method = strtoupper(trim($method));
        if (!in_array($method, self::SUPPORTED_METHODS)) {
            throw new InvalidArgumentException(
                sprintf('Nepodržana HTTP metoda: %s. Podržane metode su: %s', 
                    $method, 
                    implode(', ', self::SUPPORTED_METHODS)
                )
            );
        }

        // Validacija URL-a
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Nevalidan URL: ' . $url);
        }

        // Uzimanje kredencijala iz konfiguracije
        $username = $this->params->get('connect_rest_api.username');
        $password = $this->params->get('connect_rest_api.password');

        if (empty($username) || empty($password)) {
            throw new InvalidArgumentException(
                'Kredencijali za REST API nisu konfigurisani. Proverite CONNECT_REST_API_USERNAME i CONNECT_REST_API_PASSWORD environment varijable.'
            );
        }

        // Priprema opcija za HTTP zahtev
        $requestOptions = array_merge([
            'auth_basic' => [$username, $password],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'timeout' => 30,
        ], $options);

        // Dodavanje body-a za metode koje ga zahtevaju
        if (in_array($method, ['POST', 'PUT', 'PATCH']) && $data !== null) {
            $requestOptions['body'] = json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        // Slanje zahteva
        return $this->client->request($method, $url, $requestOptions);
    }

    /**
     * Šalje GET zahtev na određeni endpoint.
     *
     * @param string $url URL endpoint-a
     * @param array $options Dodatne opcije za HTTP zahtev
     * 
     * @return ResponseInterface
     */
    public function get(string $url, array $options = []): ResponseInterface
    {
        return $this->connector('GET', $url, null, $options);
    }

    /**
     * Šalje POST zahtev sa podacima na određeni endpoint.
     *
     * @param string $url URL endpoint-a
     * @param array $data Podaci za slanje
     * @param array $options Dodatne opcije za HTTP zahtev
     * 
     * @return ResponseInterface
     */
    public function post(string $url, array $data, array $options = []): ResponseInterface
    {
        return $this->connector('POST', $url, $data, $options);
    }

    /**
     * Šalje PUT zahtev sa podacima na određeni endpoint.
     *
     * @param string $url URL endpoint-a
     * @param array $data Podaci za slanje
     * @param array $options Dodatne opcije za HTTP zahtev
     * 
     * @return ResponseInterface
     */
    public function put(string $url, array $data, array $options = []): ResponseInterface
    {
        return $this->connector('PUT', $url, $data, $options);
    }

    /**
     * Šalje DELETE zahtev na određeni endpoint.
     *
     * @param string $url URL endpoint-a
     * @param array $options Dodatne opcije za HTTP zahtev
     * 
     * @return ResponseInterface
     */
    public function delete(string $url, array $options = []): ResponseInterface
    {
        return $this->connector('DELETE', $url, null, $options);
    }

    /**
     * Šalje PATCH zahtev sa podacima na određeni endpoint.
     *
     * @param string $url URL endpoint-a
     * @param array $data Podaci za slanje
     * @param array $options Dodatne opcije za HTTP zahtev
     * 
     * @return ResponseInterface
     */
    public function patch(string $url, array $data, array $options = []): ResponseInterface
    {
        return $this->connector('PATCH', $url, $data, $options);
    }
}
