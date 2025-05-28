<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Providers\RouteServiceProvider;

class CustomLoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['two_factor' => false], 200);
        }
        // ユーザーがメールアドレスの確認を必要とし、まだ確認していない場合、
        if ($request->user() instanceof MustVerifyEmail && ! $request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }
        // profile_configuredがfalseの場合、プロファイル設定ページにリダイレクト
        if (! $request->user()->profile_configured) {
            return redirect()->route('profile.mypage');
        }
        // ユーザーがメールアドレスの確認を必要としない場合、または既に確認済みの場合、
        return redirect()->intended(Fortify::redirects('login'));
    }
}
