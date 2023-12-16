<?php

namespace App\Http\Middleware;

use App\Helpers\AppHelper;
use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;

class AuthTokenMiddleware
{

    private $AdminUser;
    private $AppHelper;

    public function __construct()
    {
        $this->AdminUser = new AdminUser();
        $this->AppHelper = new AppHelper();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ((is_null($request->token) || empty($request->token))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        } else {

            $user = $this->AdminUser->find_by_token($request->token);

            $yesterday = $this->AppHelper->day_time() - 86400;

            if (empty($user) || ($user['login_time'] < $yesterday)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

        return $next($request);
    }
}
