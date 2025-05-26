<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Fortify;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class CustomRegisterResponse implements RegisterResponseContract
{
    /**
     * 新規登録後のレスポンスを返します。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        // return $request->wantsJson()
        //             ? new JsonResponse('', 201)
        //             : redirect()->intended(Fortify::redirects('register'));
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        // ユーザーがメールアドレスの確認を必要とする場合、適切なリダイレクトを行います。
        if ($request->user() instanceof MustVerifyEmail && ! $request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }
        // ユーザーがメールアドレスの確認を必要としない場合、または既に確認済みの場合、
        return redirect()->intended(Fortify::redirects('register'));
    }
}
