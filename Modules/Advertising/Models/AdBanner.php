<?php

namespace Modules\Advertising\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AdBanner extends Model
{
    /** @var string */
    protected $table = 'ad_banners';

    /** @var array */
    protected $fillable = [
        'status',
        'advertiser_email',
        'destination_url',
        'alt_text',
        'title_text',
        'filename',
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
     * Banners that are active and not yet expired.
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
     * Banners awaiting admin review.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Banners whose expiry date has passed.
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
     * Approve the banner for the given number of months.
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
     * Reject the banner.
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
