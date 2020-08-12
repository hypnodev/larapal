<?php

namespace hypnodev\Larapal\Models;

use hypnodev\Larapal\Subscription;
use Illuminate\Database\Eloquent\Model;

/**
 * \hypnodev\Larapal\Models\PaypalSubscription
 *
 * @property int $id
 * @property string|null $paypal_id
 * @property string $subscription_id
 * @property string $plan_id
 * @property int $user_id
 * @property string status
 * @property \Illuminate\Support\Carbon $start_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 */
class PaypalSubscription extends Model
{
    /**
     * @inheritdoc
     */
    protected $fillable = [
        'paypal_id', 'subscription_id', 'plan_id', 'user_id', 'status', 'start_at'
    ];

    /**
     * @inheritdoc
     */
    protected $casts = [
        'start_at' => 'datetime'
    ];

    /**
     * Get subscription as a PayPal object
     *
     * @return mixed
     */
    public function asPaypal() {
        return Subscription::get(
            $this->subscription_id
        );
    }
}
