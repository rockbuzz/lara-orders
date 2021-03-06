# Lara Orders

Order management

<p><img src="https://github.com/rockbuzz/lara-orders/workflows/Main/badge.svg"/></p>

## Requirements

PHP >=7.4

## Install

```bash
$ composer require rockbuzz/lara-orders
```

```php
$ php artisan vendor:publish --provider="Rockbuzz\LaraOrders\ServiceProvider" --tag="migrations"
```

```php
$ php artisan migrate
```

Add the `HasOrder` trait to the template for which you will be ordering

## Usage

```php
use Rockbuzz\LaraOrders\Transaction;
use Rockbuzz\LaraOrders\Models\Order;
use Rockbuzz\LaraOrders\Traits\HasOrder;

class YourBuyer
{
    use HasOrder
}
```

```php
$buyer->orders(): MorphMany;

$buyer->createOrder(): Order;

$buyer->orderById(int $id): ?Order;

$buyer->orderByUuid(string $uuid): ?Order;
```

```php
$order->buyer(): BelongsTo;

$order->coupon(): BelongsTo;

$order->items(): HasMany;

$order->total;

$order->totalWithCoupon;

$order->transactions(): HasMany;
```
- Events

```php
Rockbuzz\LaraOrders\Events\OrderCreated::class
Rockbuzz\LaraOrders\Events\OrderTransactionCreated::class
```

## License

The Lara Orders is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).