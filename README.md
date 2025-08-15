# ConnectRestApiBundle

A Symfony bundle for connecting to REST APIs with basic authentication support. This bundle provides a simple way to send HTTP requests with Basic Authentication.

## Installation

```bash
composer require backend2-plus/connect-rest-api-bundle
```

## Registration

The bundle is automatically registered in your Symfony application. If you need manual registration, add it to `config/bundles.php`:

```php
<?php

return [
    // ... other bundles
    Backend2Plus\ConnectRestApiBundle\ConnectRestApiBundle::class => ['all' => true],
];
```

## Configuration

### 1. Environment Variables

Add the following to your `.env` file:

```env
# REST API credentials
CONNECT_REST_API_USERNAME=your_username
CONNECT_REST_API_PASSWORD=your_password
```

### 2. Bundle Configuration

The bundle automatically loads configuration from `config/packages/connect_rest_api.yaml`:

```yaml
connect_rest_api:
    username: '%env(CONNECT_REST_API_USERNAME)%'
    password: '%env(CONNECT_REST_API_PASSWORD)%'
```

## Usage

### Basic Usage

```php
<?php

namespace App\Controller;

use Backend2Plus\ConnectRestApiBundle\Service\ConnectRestApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    public function __construct(
        private ConnectRestApiService $connectRestApiService
    ) {}

    #[Route('/api/test', name: 'api_test')]
    public function test(): Response
    {
        try {
            // GET request
            $response = $this->connectRestApiService->connector(
                'GET', 
                'https://api.example.com/users'
            );
            
            $data = json_decode($response->getContent(), true);
            
            return $this->json($data);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
```

### POST Request with Data

```php
// POST request with JSON data
$response = $this->connectRestApiService->connector(
    'POST',
    'https://api.example.com/users',
    [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]
);
```

### PUT Request

```php
// PUT request for update
$response = $this->connectRestApiService->connector(
    'PUT',
    'https://api.example.com/users/123',
    [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com'
    ]
);
```

### DELETE Request

```php
// DELETE request
$response = $this->connectRestApiService->connector(
    'DELETE',
    'https://api.example.com/users/123'
);
```

### Using Convenience Methods

The service also provides convenience methods for common HTTP operations:

```php
// GET request
$response = $this->connectRestApiService->get('https://api.example.com/users');

// POST request
$response = $this->connectRestApiService->post('https://api.example.com/users', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// PUT request
$response = $this->connectRestApiService->put('https://api.example.com/users/123', [
    'name' => 'Jane Doe'
]);

// DELETE request
$response = $this->connectRestApiService->delete('https://api.example.com/users/123');

// PATCH request
$response = $this->connectRestApiService->patch('https://api.example.com/users/123', [
    'email' => 'newemail@example.com'
]);
```

### Custom Options

You can pass additional options to customize the HTTP request:

```php
$response = $this->connectRestApiService->connector('GET', 'https://api.example.com/users', null, [
    'timeout' => 60,
    'headers' => [
        'X-Custom-Header' => 'custom-value',
        'Accept' => 'application/xml'
    ]
]);
```

## Methods

### `connector(string $method, string $url, ?array $data = null, array $options = [])`

Main method for sending HTTP requests.

**Parameters:**
- `$method` - HTTP method (GET, POST, PUT, DELETE, PATCH, HEAD, OPTIONS)
- `$url` - URL endpoint
- `$data` - optional data to send (for POST, PUT, PATCH methods)
- `$options` - additional options for the HTTP request

**Return value:**
- `ResponseInterface` - Symfony HTTP Client response object

### Convenience Methods

- `get(string $url, array $options = [])` - GET request
- `post(string $url, array $data, array $options = [])` - POST request
- `put(string $url, array $data, array $options = [])` - PUT request
- `delete(string $url, array $options = [])` - DELETE request
- `patch(string $url, array $data, array $options = [])` - PATCH request

## Error Handling

The bundle uses Symfony HTTP Client which automatically handles HTTP errors. It's recommended to use try-catch blocks:

```php
try {
    $response = $this->connectRestApiService->connector('GET', 'https://api.example.com/endpoint');
    
    if ($response->getStatusCode() === 200) {
        $data = json_decode($response->getContent(), true);
        // process data
    }
} catch (\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface $e) {
    // Network error
    $this->logger->error('Network error: ' . $e->getMessage());
} catch (\Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface $e) {
    // HTTP error (4xx, 5xx)
    $this->logger->error('HTTP error: ' . $e->getMessage());
} catch (\InvalidArgumentException $e) {
    // Invalid parameters
    $this->logger->error('Invalid parameters: ' . $e->getMessage());
}
```

## Features

- **Basic Authentication**: Automatic username/password handling
- **HTTP Method Support**: GET, POST, PUT, DELETE, PATCH, HEAD, OPTIONS
- **JSON Support**: Automatic JSON encoding/decoding
- **Error Handling**: Comprehensive exception handling
- **Flexible Options**: Customizable headers, timeouts, and other HTTP options
- **Convenience Methods**: Easy-to-use methods for common operations
- **Parameter Validation**: Built-in validation for methods and URLs

## Requirements

- PHP 8.0 or higher
- Symfony 6.0 or higher
- Symfony HTTP Client component

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Support

For questions and support, please open an issue on GitHub or contact us at info@backend2-plus.com.
