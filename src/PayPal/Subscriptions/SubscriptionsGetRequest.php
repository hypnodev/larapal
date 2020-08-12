<?php

namespace hypnodev\Larapal\PayPal\Subscriptions;

use PayPalHttp\HttpRequest;

class SubscriptionsGetRequest extends HttpRequest
{
    function __construct(string $subscriptionId)
    {
        parent::__construct("/v1/billing/subscriptions/$subscriptionId", "GET");
        $this->headers["Content-Type"] = "application/json";
    }
}
