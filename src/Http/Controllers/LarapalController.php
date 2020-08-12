<?php

namespace hypnodev\Larapal\Http\Controllers;

use App\Http\Controllers\Controller;
use hypnodev\Larapal\Exceptions\UncompleteTransactionException;
use hypnodev\Larapal\Subscription;
use hypnodev\Larapal\Models\{
    PaypalSubscription,
    PaypalTransaction
};
use Illuminate\Http\Request;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class LarapalController extends Controller
{
    /**
     * Capture transaction for approve payment
     * and redirect user to preferred url by hooking the tx id
     *
     * @param Request $request HTTP Request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \hypnodev\Larapal\Exceptions\UncompleteTransactionException
     */
    public function paid(Request $request)
    {
        $token = $request->query('token');
        $isSubscription = $request->query('subscription', false);

        if (!$isSubscription) {
            $paypalRequest = new OrdersCaptureRequest($token);
            $response = \Larapal::getPaypalClient()->execute($paypalRequest);
            if ($response->result->status !== 'COMPLETED') {
                throw new UncompleteTransactionException("Transaction isn't completed!");
            }

            PaypalTransaction::where('paypal_id', $token)->first()->update([
                'payer_name' => $response->result->payer->name->given_name . ' ' . $response->result->payer->name->surname,
                'payer_email' => $response->result->payer->email_address,
                'status' => $response->result->status,
            ]);
        } else {
            $subscriptionId = $request->query('subscription_id');

            PaypalSubscription::where('subscription_id', $subscriptionId)->first()->update([
                'paypal_id' => $token,
                'status' => Subscription::get($subscriptionId)->result->status
            ]);
        }

        return redirect(
            config('larapal.return_url') . "?transaction_id={$request->query('token')}" .
                ($isSubscription ? '&subscription_id=' . $subscriptionId : '')
        );
    }

    /**
     * Edit transaction status and redirect user to preferred url by hooking the tx id
     *
     * @param Request $request HTTP Request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function cancel(Request $request)
    {
        $token = $request->query('token');
        $isSubscription = $request->query('subscription', false);

        if (!$isSubscription) {
            PaypalTransaction::where('paypal_id', $request->query('token'))->first()->update([
                'status' => 'CANCELLED',
            ]);
        } else {
            $subscriptionId = $request->query('subscription_id');

            PaypalSubscription::where('subscription_id', $subscriptionId)->first()->update([
                'paypal_id' => $token,
                'status' => 'CANCALLED'
            ]);
        }

        return redirect(
            config('larapal.cancel_url') . "?transaction_id=$token" .
            ($isSubscription ? '&subscription_id=' . $subscriptionId : '')
        );
    }
}
