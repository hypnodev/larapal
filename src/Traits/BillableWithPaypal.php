<?php

namespace hypnodev\Larapal\Traits;

use hypnodev\Larapal\{
    Transaction,
    Subscription
};
use hypnodev\Larapal\Models\{
    PaypalSubscription,
    PaypalTransaction
};

trait BillableWithPaypal
{
    /**
     * Create the transaction and redirect user to Paypal's payment page
     *
     * @param string $description A description of the purchase
     * @param array $items List of items that user are buying
     * @param array $shipping Shipping settings
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Throwable
     */
    public function chargeWithPaypal(string $description, array $items, array $shipping = [])
    {
        $transaction = (new Transaction)
            ->setDescription($description)
            ->addItems($items);

        if (!empty($shipping)) {
            throw_if(
                !in_array([ 'amount', 'method' ], array_keys($shipping)),
                new \InvalidArgumentException('Missing required parameter for shipping!')
            );

            $transaction = $transaction->withShipping($shipping['amount'], $shipping['method'])
                ->setShippingAddress($shipping['address'] ?? $this->getShippingFields());
        }

        return $transaction->setCurrency($this->getPaypalCurrency())
                ->setUser($this)
                ->pay();
    }

    /**
     * Create subscription for this user and redirect user to Paypal's payment page
     *
     * @param string $planId Plan to subscribe
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function subscribeWithPaypal(string $planId)
    {
        return (new Subscription($planId))
            ->setUser($this)
            ->pay();
    }

    /**
     * Retrieve all user's subscriptions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paypalSubscriptions()
    {
        return $this->hasMany(PaypalSubscription::class);
    }

    /**
     * Retrieve all user's transactions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paypalTransactions()
    {
        return $this->hasMany(PaypalTransaction::class);
    }

    /**
     * Get user currency
     *
     * @return string
     */
    protected function getPaypalCurrency(): string
    {
        return config('larapal.currency');
    }

    /**
     * Get user default shipping fields
     *
     * @return array
     */
    protected function getShippingFields(): array
    {
        return [];
    }
}
