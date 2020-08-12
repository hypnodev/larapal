<?php

namespace hypnodev\Larapal\Models;

use App\User;
use hypnodev\Larapal\Transaction;
use Illuminate\Database\Eloquent\Model;

/**
 * \hypnodev\Larapal\Models\v
 *
 * @property int $id
 * @property string $paypal_id
 * @property int $user_id
 * @property string|null $payer_email
 * @property string|null $payer_name
 * @property string $status
 * @property float $amount
 * @property \Illuminate\Support\Carbon $start_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 */
class PaypalTransaction extends Model
{
    /**
     * @inheritdoc
     */
    protected $fillable = [
        'paypal_id', 'user_id', 'payer_email', 'payer_name', 'status', 'amount'
    ];

    /**
     * @inheritdoc
     */
    protected $casts = [
        'amount' => 'float'
    ];

    /**
     * Get subscription as a PayPal object
     *
     * @return mixed
     */
    public function asPaypal() {
        return Transaction::getOrder(
            $this->paypal_id
        );
    }
}
