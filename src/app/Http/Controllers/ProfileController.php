<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Routing\ControllerDispatcher;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        // ユーザーがログインしているかどうかチェック
        if (auth()->check()) {
            $user = auth()->user();
            $profile = $user->profile()->firstOrCreate([]);

            // プロフィールが存在する場合はその情報を取得し、存在しない場合は空の値を設定
            return view('mypage.profile', compact("profile"));
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
        return redirect()->route("mypage.index")->with("success", "プロフィールが更新されました。");

    }
}
