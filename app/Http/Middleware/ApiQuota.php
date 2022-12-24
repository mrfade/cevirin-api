<?php

namespace App\Http\Middleware;

use Closure;
use DateTime;
use App\Models\ApiRequest;
use Illuminate\Http\Request;

class ApiQuota
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $ip = get_ip();
        $totalQuota = 1000;

        $todaysDate = (new DateTime())->format('Y-m-d');
        $quotaUsed = ApiRequest::where('ip', $ip)
            ->whereDate('created_at', $todaysDate)
            ->count();
        $quotaLeft = $totalQuota - $quotaUsed;

        // Quota exceeded
        if ($quotaLeft <= 0) {
            response()->json([
                'status' => 'error',
                'code' => 2005,
                'message' => 'Rate limit exceeded'
            ], 429);
        }

        return $next($request);
    }
}
