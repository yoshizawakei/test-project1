<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $user = Auth::user();

        // ユーザーがメールアドレスの確認を必要とし、まだ確認していない場合、
        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }
        // profile_configuredがfalseの場合、プロファイル設定ページにリダイレクト
        if (! $user->profile_configured) {
            $user->profile_configured = true;
            $user->save();

            // プロファイル設定ページへリダイレクト
            return redirect()->route('profile.mypage');
        }
        // ユーザーがメールアドレスの確認を必要としない場合、または既に確認済みの場合、
        return redirect()->intended(Fortify::redirects('login'));
    }
}
