# Larapal

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]

A modern, easy and fluent way to allow your clients to pay with PayPal.

## Installation

Via Composer

``` bash
$ composer require hypnodev/larapal
```

Publish the configuration with command:
```bash
$ php artisan vendor:publish --provider="hypnodev\Larapal\LarapalServiceProvider" 
```

Add these keys in your .env:
```dotenv
PAYPAL_MODE=sandbox

PAYPAL_SANDBOX_ID=
PAYPAL_SANDBOX_SECRET=

PAYPAL_PRODUCTION_ID=
PAYPAL_PRODUCTION_SECRET=
```
_If you don't have yet credentials for PayPal API, please refer to [Get Started - PayPal Developer](https://developer.paypal.com/docs/api/overview/#get-credentials)_

## Usage
Add `BillableWithPaypal` trait to your User model
```php
<?php

namespace App;

use hypnodev\Larapal\Traits\BillableWithPaypal;
// ...

class User extends Authenticatable
{
    use Notifiable, BillableWithPaypal;
    
    // ...
}
```

This will add `chargeWithPaypal`, `subscribeWithPaypal`, `getPaypalCurrency`, `getShippingFields` to your user.

Then you can charge your user with method:
```php
<?php
auth()->user()->chargeWithPaypal('Charge description', [ // Array of items
    ['name' => 'pkg base', 'description' => 'base package', 'price' => 10.00, 'tax' => 2]
]);

// If your charge has shipping, you need to add an extra param with name and amount
auth()->user()->chargeWithPaypal('Charge description', [ // Array of items
    ['name' => 'pkg base', 'description' => 'base package', 'price' => 10.00, 'tax' => 2]
], [ // Shipping
    'name' => 'Courier name',
    'amount' => 100.50,
    'address' => [ // Optional, you can skip this key
        'address' => '4178 Libby Street',
        'city' => 'Hermosa Beach',
        'state' => 'CA',
        'postal_code' => '90254',
        'country' => 'USA'
    ]
]);
```

Or for subscription:
```php
auth()->user()->subscribeWithPaypal('Plan id');
```
_You can create a plan under "App Center" in your PayPal Merchant Dashboard_ 

If you need to charge user with another currency different from the configuration, you can override `getPaypalCurrency` method:
```php
<?php

namespace App;

use hypnodev\Larapal\Traits\BillableWithPaypal;
// ...

class User extends Authenticatable
{
    use Notifiable, BillableWithPaypal;
    
    // ...
    
    /**
     * @inheritDoc
     */
    protected function getPaypalCurrency(): string
    {
        return 'USD';
    }
}
```

You can set default shipping info with `getShippingFields` method
```php
<?php

namespace App;

use hypnodev\Larapal\Traits\BillableWithPaypal;
// ...

class User extends Authenticatable
{
    use Notifiable, BillableWithPaypal;
    
    // ...
    
    /**
     * @inheritDoc
     */
    protected function getShippingFields(): array
    {
        return [
            'address' => $this->shipping_address,
            'city' => $this->shipping_city,
            'state' => $this->shipping_state,
            'postal_code' => $this->shipping_postal_code,
            'country' => $this->shipping_country
        ];
    }
}
```

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email me@cristiancosenza.com instead of using the issue tracker.

## Credits

- [Cristian Cosenza][link-author]
- [All Contributors][link-contributors]

## License

license. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/hypnodev/larapal.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/hypnodev/larapal.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/hypnodev/larapal/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/hypnodev/larapal
[link-downloads]: https://packagist.org/packages/hypnodev/larapal
[link-author]: https://github.com/hypnodev
[link-contributors]: ../../contributors
