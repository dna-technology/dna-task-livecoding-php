<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $paymentId
 * @property string $userId
 * @property string $merchantId
 * @property float $amount
 * @method static Builder|Payment query()
 */
class Payment extends Model
{
    public const CREATED_AT = 'createdAt';
    public const UPDATED_AT = null;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payments';

    protected $fillable = ['paymentId', 'userId', 'merchantId', 'amount'];
}
