<?php

namespace hypnodev\Larapal\Facades;

use Illuminate\Support\Facades\Facade;
/**
 * @method static \PayPalCheckoutSdk\Core\PayPalHttpClient getPaypalClient()
 *
 * @see \hypnodev\Larapal\Larapal
 */
class Larapal extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'larapal';
    }
}
