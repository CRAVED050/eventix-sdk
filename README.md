# Eventix SDK for PHP *[work in progress]*
This is an unofficial PHP SDK for Eventix. It provides convenient access to the Eventix API from applications written in the PHP language.

> ⚠️ This library is under heavy development and therefore not all endpoints are implemented yet. If you need a specific endpoint, please open a pull request. Usage in production is not recommended yet.
 
## Installation
You can install the package via composer:
```bash
composer require janyksteenbeek/eventix-sdk
```

## Usage example

```php
use Janyk\Eventix\Client as EventixClient;

$eventix = new EventixClient('YOUR_CLIENT_ID', 'YOUR_CLIENT_SECRET', 'YOUR_REDIRECT_URI');

// Fetch the redirect URI to authorize your application
$redirectUri = $eventix->redirect();
header('Location: ' . $redirectUri); // ... redirect to the URL generated in the `$redirectUri` variable


// ..............


// After Eventix redirects you back to the application, you can fetch the access token
$authorizationCode = $_GET['code'];
$eventix->authorize($authorizationCode);

// You can now use the access token to make requests to the API
$events = $eventix->events()->all();
```

## Endpoints

Check `src/Endpoints` for all available endpoints.

### Events
```php
/** @var \Janyk\Eventix\Resources\Event[] $events */
$events = $eventix->events()->all();
```

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Security
If you discover any security related issues, please email `security-opensource [at] janyksteenbeek.nl`. All security vulnerabilities will be promptly addressed. 

## Disclaimer

This library is not affiliated with Eventix in any way. Eventix is a registered trademark of Eventix holding BV.