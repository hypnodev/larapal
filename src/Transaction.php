<?php

namespace hypnodev\Larapal;

use App\User;
use hypnodev\Larapal\Models\PaypalTransaction;
use Illuminate\Support\Arr;
use PayPalCheckoutSdk\Orders\{
    OrdersCreateRequest,
    OrdersGetRequest
};

class Transaction
{
    /**
     * Transaction settings
     *
     * @var array
     */
    private array $config = [];

    /**
     * Body of subscription request
     *
     * @var array
     */
    private array $requestBody = [];
    /**
     * User paying this transaction
     *
     * @var \App\User
     */
    private User $user;

    /**
     * Transaction constructor.
     *
     * @param array $config Transaction config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->config['currency'] = config('larapal.currency');

        $this->requestBody = [
            'intent' => 'CAPTURE',
            'application_context' => [
                'brand_name' => $this->config['brand_name'] ?? config('app.name'),
                'locale' => $this->config['locale'] ?? config('larapal.locale'),
                'landing_page' => 'BILLING',
                'shipping_preferences' => 'SET_PROVIDED_ADDRESS',
                'user_action' => 'PAY_NOW',
                'return_url' => url('/larapal/return'),
                'cancel_url' => url('/larapal/cancel')
            ],
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $this->config['currency'],
                        'value' => 0,
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => $this->config['currency'],
                                'value' => 0,
                            ],
                            'tax_total' => [
                                'currency_code' => $this->config['currency'],
                                'value' => 0,
                            ],
                        ]
                    ],
                    'items' => []
                ]
            ],
        ];
    }

    /**
     * Invoice description
     *
     * @param string $description A description of the purchase
     * @return $this
     */
    public function setDescription(string $description): Transaction
    {
        $this->requestBody['purchase_units'][0]['description'] = $description;

        return $this;
    }

    /**
     * Attach multiple items at one-time to invoice
     *
     * @param array $items List of items that user are buying
     * @return $this
     */
    public function addItems(array $items): Transaction
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * Attach item to invoice
     *
     * @param array $item Item to attach to invoice
     * @return $this
     * @throws \Throwable
     */
    public function addItem(array $item): Transaction
    {
        throw_if(
            !isset($item['name'], $item['description'], $item['price'], $item['tax']),
            new \InvalidArgumentException('Missing required parameter for item!')
        );

        $this->requestBody['purchase_units'][0]['items'][] = [
            'name' => $item['name'],
            'description' => $item['description'],
            'unit_amount' => [
                'currency_code' => $this->config['currency'],
                'value' => "{$item['price']}"
            ],
            'tax' => [
                'currency_code' => $this->config['currency'],
                'value' => "{$item['tax']}"
            ],
            'quantity' => '' . ($item['quantity'] ?? 1) . ''
        ];

        $this->requestBody['purchase_units'][0]['amount']['breakdown']['item_total']['value'] += $item['price'];
        $this->requestBody['purchase_units'][0]['amount']['breakdown']['tax_total']['value'] += $item['tax'];

        $this->requestBody['purchase_units'][0]['amount']['value'] +=
            $this->requestBody['purchase_units'][0]['amount']['breakdown']['item_total']['value'] +
            $this->requestBody['purchase_units'][0]['amount']['breakdown']['tax_total']['value'];

        return $this;
    }

    /**
     * Define shipping amount and method for this invoice if needed
     *
     * @param float $amount Price of shipping
     * @param string $method Courier name or method of shipping
     * @return $this
     */
    public function withShipping(float $amount, string $method): Transaction
    {
        $this->requestBody['purchase_units'][0]['amount']['breakdown']['shipping'] = [
            'currency_code' => $this->config['currency'],
            'value' => "$amount"
        ];

        $this->requestBody['purchase_units'][0]['amount']['value'] += $amount;

        $this->requestBody['purchase_units'][0]['shipping'] = [
            'method' => $method
        ];

        return $this;
    }

    /**
     * Set location for shipping
     *
     * @param array $shipping Shipping info
     * @return $this
     * @throws \Throwable
     */
    public function setShippingAddress(array $shipping): Transaction
    {
        throw_if(
            !array_key_exists('shipping', $this->requestBody['purchase_units'][0]),
            new \BadMethodCallException('You must provide a shipping method!')
        );

        throw_if(
            !isset($shipping['address'], $shipping['city'], $shipping['state'], $shipping['postal_code'], $shipping['country']),
            new \InvalidArgumentException('Missing required parameter for shipping!')
        );

        $this->requestBody['purchase_units'][0]['shipping'] = [
            'address_line_1' => $shipping['address'],
            'address_line_2' => $shipping['address_2'] ?? '',
            'admin_area_2' => $shipping['city'],
            'admin_area_1' => $shipping['state'],
            'postal_code' => $shipping['postal_code'],
            'country_code' => $shipping['country'],
        ];

        return $this;
    }

    /**
     * Set currency for this transaction
     *
     * @param string $currency Abbreviation of currency (ex. "EUR", "USD")
     * @return $this
     */
    public function setCurrency(string $currency): Transaction
    {
       $this->config['currency'] = $this->requestBody['purchase_units'][0]['amount']['currency_code'] =
            $this->requestBody['purchase_units'][0]['amount']['breakdown']['item_total']['currency_code'] =
            $this->requestBody['purchase_units'][0]['amount']['breakdown']['tax-total']['currency_code'] = $currency;

        return $this;
    }

    /**
     * Set paying user
     *
     * @param User $user Paying user
     * @return $this
     */
    public function setUser(User $user): Transaction
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Finally pay this transaction
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function pay()
    {
        $request = new OrdersCreateRequest;
        $request->prefer('return=representation');
        $request->body = $this->requestBody;
        $response = \Larapal::getPaypalClient()->execute($request);

        PaypalTransaction::create([
            'paypal_id' => $response->result->id,
            'user_id' => $this->user->id,
            'status' => $response->result->status,
            'amount' => $response->result->purchase_units[0]->amount->value
        ]);

        return redirect(
            Arr::first($response->result->links, fn ($link) => $link->rel === 'approve')->href
        );
    }

    /**
     * Retrieve transaction as PayPal object
     *
     * @param string $orderId Order to retrieve
     * @return mixed
     */
    public static function getOrder(string $orderId)
    {
        $response = \Larapal::getPaypalClient()->execute(
            new OrdersGetRequest($orderId)
        );

        return $response->result;
    }
}
