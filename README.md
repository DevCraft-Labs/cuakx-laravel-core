# cuakx-core

Core utilities, base classes, and helpers for **Cuakx** Laravel microservices.

Provides a shared foundation — response shaping, input validation, exceptions, console logging, currency formatting, string utilities, and Redis abstractions — so every service starts from the same baseline without duplicating boilerplate.

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | `^8.2` |
| Laravel | `^11.0 \| ^12.0 \| ^13.0` |

---

## Installation

### From Packagist *(once published)*
```bash
composer require cuakx/cuakx-core
```

### Local path repository *(monorepo / development)*

Add to the consuming service's `composer.json`:
```json
"repositories": [
    { "type": "path", "url": "../../../Domains/cuakx-core" }
],
"require": {
    "cuakx/cuakx-core": "@dev"
}
```
Then:
```bash
composer require cuakx/cuakx-core
```

---

## Contents

```
src/
├── Http/Controllers/
│   └── BaseController.php       — Abstract controller with built-in validation
├── Exceptions/
│   ├── BaseException.php        — Abstract base exception (extends \Error)
│   └── BadRequestException.php  — HTTP 400 validation failure
├── DTO/
│   └── BaseResponseDTO.php      — Standardized JSON API response envelope
└── Utils/
    ├── Console.php              — Timestamped console output
    ├── CurrencyUtil.php         — Rupiah formatter / parser
    ├── StringUtil.php           — String transformation & generation helpers
    └── Redis/
        ├── RedisRepository.php  — ORM-like Redis CRUD
        └── RedisPubSub.php      — Redis Publish / Subscribe
```

---

## Usage

### BaseController

Extend `BaseController` in every API controller. Call `baseValidator()` at the top of any action that accepts input — it throws `BadRequestException` automatically on failure, so you never inspect a validator result manually.

```php
use Cuakx\Core\Http\Controllers\BaseController;
use Cuakx\Core\DTO\BaseResponseDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends BaseController
{
    public function store(Request $request): JsonResponse
    {
        $this->baseValidator($request, [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:uma_tbl_users',
            'password' => 'required|string|min:8',
            'address'  => 'required|string|max:255',
        ]);

        // validation passed — continue with business logic
        return BaseResponseDTO::success('User created.', null, '201');
    }
}
```

Custom messages per rule:
```php
$this->baseValidator($request, ['name' => 'required'], [
    'name.required' => 'Nama wajib diisi.',
]);
```

---

### BaseResponseDTO

All API responses use the same envelope shape:

```json
{
    "success": true,
    "code": "200",
    "message": "Fetched successfully.",
    "data": { ... }
}
```

```php
use Cuakx\Core\DTO\BaseResponseDTO;

// Success — with data
return BaseResponseDTO::success('Fetched.', (object) $user);

// Success — no data, custom code
return BaseResponseDTO::success('Created.', null, '201');

// Error
return BaseResponseDTO::error('404', 'User not found.');
```

---

### Exceptions

Register `BadRequestException` (and any future subclass) in your Laravel exception handler to map it to a proper HTTP response:

```php
// app/Exceptions/Handler.php  (Laravel 11+: bootstrap/app.php withExceptions())
use Cuakx\Core\Exceptions\BadRequestException;
use Cuakx\Core\DTO\BaseResponseDTO;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (BadRequestException $e) {
        return BaseResponseDTO::error('400', $e->getMessage());
    });
})
```

Creating custom domain exceptions:
```php
use Cuakx\Core\Exceptions\BaseException;

class NotFoundException extends BaseException
{
    public function __construct(string $resource)
    {
        parent::__construct("{$resource} not found.", '404');
    }
}
```

---

### Console

```php
use Cuakx\Core\Utils\Console;

Console::writeLine('Server started.');              // [LOG][2026-06-02 08:00:00] Server started.
Console::writeLine('Disk space low.',      'w');    // [WARNING][...]
Console::writeLine('Unhandled state.',     'e');    // [ERROR][...]
Console::writeLine('Request payload.',     'd');    // [DEBUG][...]
Console::writeLine('App initialised.',     'i');    // [INFO][...]
Console::writeLine('Verbose detail.',      'v');    // [VERBOSE][...]
Console::writeLine('How did we get here.', 'wtf'); // [WTF][...]
```

| Type param | Label |
|---|---|
| `'e'` | ERROR |
| `'w'` | WARNING |
| `'i'` | INFO |
| `'d'` | DEBUG |
| `'v'` | VERBOSE |
| `'wtf'` | WTF |
| `null` / anything else | LOG |

---

### CurrencyUtil

```php
use Cuakx\Core\Utils\CurrencyUtil;

CurrencyUtil::toRupiah(1500000);           // "Rp. 1.500.000,00"
CurrencyUtil::toRupiah(75000.5);           // "Rp. 75.000,50"

CurrencyUtil::fromRupiah('Rp. 1.500.000,00');  // 1500000.0
CurrencyUtil::fromRupiah('Rp. 75.000,50');     // 75000.5
```

---

### StringUtil

```php
use Cuakx\Core\Utils\StringUtil;

// Case conversion
StringUtil::toCamelCase('hello-world');   // "helloWorld"
StringUtil::toSnakeCase('helloWorld');    // "hello_world"
StringUtil::toKebabCase('Hello World');   // "hello-world"
StringUtil::capitalize('hello');          // "Hello"

// Masking  (maskStart = leading chars to keep, maskEnd = trailing chars to keep)
StringUtil::maskString('1234567890');              // "######7890"  (last 4 kept)
StringUtil::maskString('abcdef', '*', 2, 2);       // "ab**ef"

// Random strings
StringUtil::randomString(10);              // "ekzptqhwry"   (lowercase)
StringUtil::randomString(10, true);        // "e3zp1qhw9y"   (+ digits)
StringUtil::randomString(10, true, true);  // "E3zP1QhW9y"   (+ uppercase)

// GUIDs
StringUtil::generateGuidV4();  // "550e8400-e29b-41d4-a716-446655440000"
StringUtil::generateGuidV7();  // "069a8f59-2d5a-7fb7-8b68-8be3d2e4f3a1"  (time-ordered)

// Base64
StringUtil::toBase64('hello');        // "aGVsbG8="
StringUtil::fromBase64('aGVsbG8=');  // "hello"
```

---

### RedisRepository

An ORM-like wrapper around Laravel's `Redis` facade. Extend it to namespace keys per entity:

```php
use Cuakx\Core\Utils\Redis\RedisRepository;

// Direct usage
$repo = new RedisRepository('sessions:', 3600);  // prefix, TTL in seconds
$repo->set('user:1', ['name' => 'Alice', 'role' => 'admin']);

$user = $repo->get('user:1');   // ['name' => 'Alice', 'role' => 'admin']
$repo->exists('user:1');        // true
$repo->ttl('user:1');           // remaining seconds
$repo->expire('user:1', 7200);  // extend TTL
$repo->delete('user:1');

// Bulk
$repo->getMany(['user:1', 'user:2']);  // ['user:1' => [...], 'user:2' => null]
$repo->keys('user:*');                 // ['user:1', 'user:2']
$repo->flush('user:*');               // deletes all matching keys

// Counters
$repo->increment('page_views');    // 1
$repo->increment('page_views', 5); // 6
$repo->decrement('page_views');    // 5
```

Subclass pattern (recommended):
```php
class UserSessionRepository extends RedisRepository
{
    public function __construct()
    {
        parent::__construct('user_sessions:', 3600);
    }
}
```

---

### RedisPubSub

> **Note:** `subscribe()` and `psubscribe()` are **blocking**. Run them inside an Artisan command, never inside an HTTP request.

**Publishing:**
```php
use Cuakx\Core\Utils\Redis\RedisPubSub;

$pubsub = new RedisPubSub();
$pubsub->publish('orders', ['id' => 1, 'status' => 'paid']);         // array auto-encoded
$pubsub->publish('notifications', 'plain text message');
```

**Subscribing** (inside `php artisan` command `handle()`):
```php
$pubsub = new RedisPubSub();

$pubsub->subscribe('orders', function (string $message, string $channel) {
    $data = json_decode($message, true);
    // process $data ...
});

// Multiple channels at once
$pubsub->subscribe(['orders', 'payments'], function (string $message, string $channel) {
    // $channel tells you which one fired
});
```

**Pattern subscribe:**
```php
$pubsub->psubscribe('orders.*', function (string $message, string $channel, string $pattern) {
    // fires for "orders.created", "orders.updated", etc.
});
```

---

## Testing

```bash
composer test
# or directly:
vendor/bin/phpunit
```

The test suite covers all components (67 tests, 75 assertions). Tests for `BaseController` and `BaseResponseDTO` use [`orchestra/testbench`](https://github.com/orchestral/testbench) to provide a full Laravel application context without needing a running server.

```
Tests: 67, Assertions: 75, OK
```

---

## License

MIT
