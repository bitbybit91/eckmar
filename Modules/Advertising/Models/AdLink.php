<?php

namespace Modules\Advertising\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AdLink extends Model
{
    /** @var string */
    protected $table = 'ad_links';

    /** @var array */
    protected $fillable = [
        'status',
        'advertiser_email',
        'destination_url',
        'anchor_text',
        'order_id',
        'month_count',
        'approved_at',
        'expires_at',
    ];

    /** @var array */
    protected $dates = [
        'approved_at',
        'expires_at',
    ];

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Links that are active and not yet expired.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
                     ->where('expires_at', '>', Carbon::now());
    }

    /**
     * Links awaiting admin review.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Links whose expiry date has passed.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired');
    }

    // ──────────────────────────────────────────────
    // Domain methods
    // ──────────────────────────────────────────────

    /**
     * Approve the link for the given number of months.
     *
     * @param int $months
     * @return void
     */
    public function approve(int $months): void
    {
        $this->update([
            'status'      => 'active',
            'approved_at' => Carbon::now(),
            'expires_at'  => Carbon::now()->addMonths($months),
        ]);
    }

    /**
     * Reject the link.
     *
     * @return void
     */
    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(AdOrder::class, 'order_id');
    }
}
