<?php

namespace Modules\Advertising\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdSlotAvailability
{
    /**
     * Handle an incoming request.
     *
     * Rejects banner/link order submissions when all slots are already taken.
     *
     * @param Request $request
     * @param Closure $next
     * @param string  $type   'banner' or 'link'
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $type = 'banner')
    {
        if ($type === 'banner') {
            $active = \Modules\Advertising\Models\AdBanner::active()->count();
            $max    = (int) config('advertising.max_banners', 10);

            if ($active >= $max) {
                return redirect()
                    ->route('ads.order.banner')
                    ->with('errormessage', 'All banner slots are currently taken. Please check back later.');
            }
        }

        if ($type === 'link') {
            $active = \Modules\Advertising\Models\AdLink::active()->count();
            $max    = (int) config('advertising.max_links', 20);

            if ($active >= $max) {
                return redirect()
                    ->route('ads.order.link')
                    ->with('errormessage', 'All link slots are currently taken. Please check back later.');
            }
        }

        return $next($request);
    }
}
