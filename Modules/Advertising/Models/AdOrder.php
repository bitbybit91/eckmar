<?php

namespace Modules\Advertising\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AdOrder extends Model
{
    /** @var string */
    protected $table = 'ad_orders';

    /** @var string */
    protected $primaryKey = 'id';

    /** @var bool */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var array */
    protected $fillable = [
        'id',
        'type',
        'item_id',
        'usd_amount',
        'xmr_amount',
        'xmr_rate',
        'wallet',
        'status',
        'noted_at',
    ];

    /** @var array */
    protected $dates = [
        'noted_at',
    ];

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeAwaitingPayment(Builder $query): Builder
    {
        return $query->where('status', 'awaiting_payment');
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopePaymentNoted(Builder $query): Builder
    {
        return $query->where('status', 'payment_noted');
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    /**
     * Resolve to the associated AdBanner or AdLink based on the type column.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderable()
    {
        if ($this->type === 'banner') {
            return $this->belongsTo(AdBanner::class, 'item_id');
        }

        return $this->belongsTo(AdLink::class, 'item_id');
    }
}
