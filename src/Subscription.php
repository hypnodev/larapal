<?php

namespace hypnodev\Larapal;

use hypnodev\Larapal\Models\PaypalSubscription;
use hypnodev\Larapal\Exceptions\InvalidNameException;
use hypnodev\Larapal\PayPal\Subscriptions\SubscriptionsCreateRequest;
use Illuminate\Support\{
    Arr,
    Str
};
use hypnodev\Larapal\PayPal\Subscriptions\SubscriptionsGetRequest;

class Subscription
{
    /**
     * Body of subscription request
     *
     * @var array
     */
    private array $requestBody = [];

    /**
     * Subscriber
     */
    private $user;

    /**
     * Subscription constructor.
     *
     * @param string $planId Id of the plan to subscribe
     */
    public function __construct(string $planId)
    {
        $this->requestBody = [
            "application_context" => [
                'brand_name' => config('app.name') ?? $this->config['brand_name'],
                'locale' => config('larapal.locale') ?? $this->config['locale'],
                'shipping_preference' => 'SET_PROVIDED_ADDRESS',
                'user_action' => 'SUBSCRIBE_NOW',
                'payment_method' => [
                    'payer_selected' => 'PAYPAL',
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED'
                ],
                'return_url' => url('/larapal/return?subscription=true'),
                'cancel_url' => url('/larapal/cancel?subscription=true'),
            ],
            'plan_id' => $planId,
            'start_time' => now()->addMinute()->toISOString() // Add minute just to workaround the PayPal error "Start time must be a valid future date and time"
        ];
    }

    /**
     * Set subscriber user
     *
     * @param $user Subscriber user
     * @return $this
     * @throws \hypnodev\Larapal\Exceptions\InvalidNameException
     */
    public function setUser($user): Subscription
    {
        $this->user = $user;
        $name = $this->resolveName();

        $this->requestBody['subscriber'] = [
            'name' => [
                'given_name' => $name['firstName'],
                'surname' => $name['lastName']
            ],
            'email_address' => $user->email
        ];

        return $this;
    }

    /**
     * Resolve user first and lastname by available field in model
     *
     * @return array User's first and lastname
     * @throws \hypnodev\Larapal\Exceptions\InvalidNameException
     */
    protected function resolveName(): array
    {
        if ($this->user->first_name) {
            $firstName = $this->user->first_name;
            $lastName = $this->user->last_name;
        }
        else if ($this->user->name) {
            $firstName = Str::beforeLast($this->user->name, ' ');
            $lastName = Str::afterLast($this->user->name, ' ');
        } else {
            throw new InvalidNameException('Unable to catch user complete name!');
        }

        return compact('firstName', 'lastName');
    }

    /**
     * Finally redirect user to payment page
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function pay()
    {
        $request = new SubscriptionsCreateRequest;
        $request->prefer('return=representation');
        $request->body = $this->requestBody;
        $response = \Larapal::getPaypalClient()->execute($request);

        PaypalSubscription::create([
            'subscription_id' => $response->result->id,
            'plan_id' => $response->result->plan_id,
            'user_id' => $this->user->id,
            'status' => $response->result->status,
            'start_at' => $response->result->start_time
        ]);

        return redirect(
            Arr::first($response->result->links, fn ($link) => $link->rel === 'approve')->href
        );
    }

    /**
     * Retrieve subscription as PayPal object
     *
     * @param string $subscriptionId Subscription to retrieve
     * @return mixed
     */
    public static function get(string $subscriptionId)
    {
        return \Larapal::getPaypalClient()->execute(
            new SubscriptionsGetRequest($subscriptionId)
        );
    }
}
