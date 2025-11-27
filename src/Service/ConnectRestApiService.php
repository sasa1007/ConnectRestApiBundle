<?php

namespace Backend2Plus\ConnectRestApiBundle\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use InvalidArgumentException;

/**
 * Service for connecting to REST APIs with Basic Authentication support.
 */
class ConnectRestApiService
{
    /**
     * Supported HTTP methods
     */
    private const SUPPORTED_METHODS = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'];

    public function __construct(
        private HttpClientInterface $client,
        private ParameterBagInterface $params
    ) {
    }

    /**
     * Sends HTTP request to specified endpoint with Basic Authentication.
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE, PATCH, HEAD, OPTIONS)
     * @param string $url URL endpoint
     * @param array|null $data Data to send (for POST, PUT, PATCH methods)
     * @param array $options Additional options for HTTP request
     * 
     * @return ResponseInterface Symfony HTTP Client response object
     * 
     * @throws InvalidArgumentException If method or URL is invalid
     * @throws TransportExceptionInterface If network error occurs
     * @throws HttpExceptionInterface If server returns HTTP error
     */
    public function connector(string $method, string $url, ?array $data = null, array $options = []): ResponseInterface
    {
        // Validate HTTP method
        $method = strtoupper(trim($method));
        if (!in_array($method, self::SUPPORTED_METHODS)) {
            throw new InvalidArgumentException(
                sprintf('Unsupported HTTP method: %s. Supported methods are: %s', 
                    $method, 
                    implode(', ', self::SUPPORTED_METHODS)
                )
            );
        }

        // Validate URL
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid URL: ' . $url);
        }

        // Get credentials from configuration
        $username = $this->params->get('connect_rest_api.username');
        $password = $this->params->get('connect_rest_api.password');

        if (empty($username) || empty($password)) {
            throw new InvalidArgumentException(
                'REST API credentials are not configured. Please check CONNECT_REST_API_USERNAME and CONNECT_REST_API_PASSWORD environment variables.'
            );
        }

        // Prepare options for HTTP request
        $requestOptions = array_merge([
            'auth_basic' => [$username, $password],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'timeout' => 30,
        ], $options);

        // Add body for methods that require it
        if (in_array($method, ['POST', 'PUT', 'PATCH']) && $data !== null) {
            $requestOptions['body'] = json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        // Send request
        return $this->client->request($method, $url, $requestOptions);
    }

    /**
     * Sends GET request to specified endpoint.
     *
     * @param string $url URL endpoint
     * @param array $options Additional options for HTTP request
     * 
     * @return ResponseInterface
     */
    public function get(string $url, array $options = []): ResponseInterface
    {
        return $this->connector('GET', $url, null, $options);
    }

    /**
     * Sends POST request with data to specified endpoint.
     *
     * @param string $url URL endpoint
     * @param array $data Data to send
     * @param array $options Additional options for HTTP request
     * 
     * @return ResponseInterface
     */
    public function post(string $url, array $data, array $options = []): ResponseInterface
    {
        return $this->connector('POST', $url, $data, $options);
    }

    /**
     * Sends PUT request with data to specified endpoint.
     *
     * @param string $url URL endpoint
     * @param array $data Data to send
     * @param array $options Additional options for HTTP request
     * 
     * @return ResponseInterface
     */
    public function put(string $url, array $data, array $options = []): ResponseInterface
    {
        return $this->connector('PUT', $url, $data, $options);
    }

    /**
     * Sends DELETE request to specified endpoint.
     *
     * @param string $url URL endpoint
     * @param array $options Additional options for HTTP request
     * 
     * @return ResponseInterface
     */
    public function delete(string $url, array $options = []): ResponseInterface
    {
        return $this->connector('DELETE', $url, null, $options);
    }

    /**
     * Sends PATCH request with data to specified endpoint.
     *
     * @param string $url URL endpoint
     * @param array $data Data to send
     * @param array $options Additional options for HTTP request
     * 
     * @return ResponseInterface
     */
    public function patch(string $url, array $data, array $options = []): ResponseInterface
    {
        return $this->connector('PATCH', $url, $data, $options);
    }
}
