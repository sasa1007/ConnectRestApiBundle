# ConnectRestApiBundle

Jednostavan Symfony bundle za povezivanje sa REST API-jevima sa osnovnom autentifikacijom.

## Instalacija

```bash
composer require backend2-plus/connect-rest-api-bundle
```

## Registracija

Dodajte u `config/bundles.php`:

```php
Backend2Plus\ConnectRestApiBundle\ConnectRestApiBundle::class => ['all' => true],
```

## Konfiguracija

U `.env` fajlu:
```env
CONNECT_REST_API_USERNAME=your_username
CONNECT_REST_API_PASSWORD=your_password
```

## Korišćenje

```php
use Backend2Plus\ConnectRestApiBundle\Service\ConnectRestApiService;

// U controller-u
$response = $this->connectRestApiService->connector('GET', 'https://api.example.com/endpoint');
```

## Licenca

MIT
