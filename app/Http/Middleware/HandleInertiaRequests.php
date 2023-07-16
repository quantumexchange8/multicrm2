<?php

namespace App\Http\Middleware;

use App\Models\IbAccountType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        if ($user) {
            $IBAccountTypes = IbAccountType::where('user_id', $user->id)->with('accountType')->get();
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user,
            ],
            'auth.user.media' => fn() => $request->user() ? $request->user()->hasMedia('front_identity') : null,
            'auth.user.roles' => fn() => $request->user() ? $request->user()->getRoleNames() : null,
            'auth.user.permissions' => fn() => $request->user() ? $request->user()->getPermissionNames() : null,
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            'toast' => session('toast'),
            'monthlyDeposit' => $user ? $user->getMonthlyDeposit() : null,
            'monthlyWithdrawal' => $user ? $user->getMonthlyWithdrawal() : null,
            'IBAccountTypes' => $IBAccountTypes ?? null,
        ]);
    }

}
