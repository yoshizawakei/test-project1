<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Routing\ControllerDispatcher;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        // ユーザーがログインしているかどうかチェック
        if (auth()->check()) {
            $user = auth()->user();
            $profile = Profile::where('user_id', $user->id)->first();

            // プロフィールが存在する場合はその情報を取得し、存在しない場合は空の値を設定
            return view('profile.mypage', [
                'username' => $profile ? $profile->username : '',
                'postal_code' => $profile ? $profile->postal_code : '',
                'address' => $profile ? $profile->address : '',
                'building_name' => $profile ? $profile->building_name : '',
                'profile_image' => $profile && $profile->profile_image ? Storage::url($profile->profile_image) : null,
            ]);
        }
        // ログインしていない場合は、ログイン画面にリダイレクト
        return redirect()->route('login');
    }

    public function edit(Request $request)
    {
        $user = auth()->user();

        $profile = Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'username' => $request->input('username'),
                'postal_code' => $request->input('postal_code'),
                'address' => $request->input('address'),
                'building_name' => $request->input('building_name'),
            ]
        );

        if ($request->hasFile("profile_image")) {
            if ($profile->profile_image) {
                Storage::disk('public')->delete($profile->profile_image);
            }
            $path = $request->file("profile_image")->store("profile_images", "public");
            $profile->profile_image = $path;
            $profile->save();
        }

        $user->forceFill(["profile_configured" => true])->save();

        // プロフィール設定後、マイページにリダイレクト
        return redirect()->route("profile.mypage")->with("success", "プロフィールが更新されました。")->withInput([
            'username' => $profile->username,
            'postal_code' => $profile->postal_code,
            'address' => $profile->address,
            'building_name' => $profile->building_name,
        ]);

    }
}
