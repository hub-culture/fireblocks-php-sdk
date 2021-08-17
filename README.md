# Hub Culture's PHP SDK for Fireblocks API

# Usage

Include the library with composer.

```
composer require hubculture/fireblocks-php-sdk
```

### Account Service
Retrieving a user by id

```php
include '/vendor/autoload.php';

use Hub\FireBlocksSdk\AccountService;

$config = array(
    'private_key_file' => '/var/tmp/fireblocks_secret.key',
    'api_key' => '388ad1c312a488ee9e12998fe097f2258fa8d5ee',
);

$service = new AccountService($config);
$response = $service->getAccounts();
var_dump($response);
```
