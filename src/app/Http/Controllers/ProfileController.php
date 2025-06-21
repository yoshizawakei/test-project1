<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Routing\ControllerDispatcher;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\ProfileRequest;


class ProfileController extends Controller
{
    public function index()
    {
        // ユーザーがログインしているかどうかチェック
        if (!auth()->check()) {
            return redirect()->route('login'); // ログインしていない場合はログイン画面にリダイレクト
        }

        $user = auth()->user();

        $profile = $user->profile()->firstOrCreate(
            [],
            [
                "username" => $user->name ?? '',
                "postal_code" => "",
                "address" => "",
                "building_name" => null,
                "profile_image" => null,
            ]
        );

        // プロフィールをビューに渡す
        return view('mypage.profile', compact("profile"));
    }

    public function edit(ProfileRequest $profileRequest, AddressRequest $addressRequest)
    {
        $user = auth()->user();

        $profile = Profile::firstOrCreate(["user_id" => $user->id]);
        $profile->username = $addressRequest->input("username");
        $profile->postal_code = str_replace('-', '', $addressRequest->input("postal_code"));
        $profile->address = $addressRequest->input("address");
        $profile->building_name = $addressRequest->input("building_name");

        if ($profileRequest->hasFile("profile_image")) {
            if ($profile->profile_image) {
                Storage::disk('public')->delete($profile->profile_image);
            }
            $path = $profileRequest->file("profile_image")->store("profile_images", "public");
            $profile->profile_image = $path;
        }

        $profile->save();

        $user->forceFill(["profile_configured" => true])->save();

        // プロフィール設定後、マイページにリダイレクト
        return redirect()->route("mypage.index")->with("success", "プロフィールが更新されました。");

    }

    public function addressEdit(Request $request)
    {
        $user = auth::user();
        $profile = $user->profile;

        if(!$profile) {
            // プロフィールが存在しない場合はプロフィール設定画面にリダイレクト
            return redirect()->route("mypage.profile")->with("error", "プロフィールが設定されていません。");
        }

        $item_id = $request->query('item_id');

        return view("mypage.edit_address", compact("profile", "item_id"));
    }

    public function addressUpdate(AddressRequest $request)
    {
        $user = auth()->user();

        $postalCodeCleaned = str_replace('-', '', $request->input('postal_code'));

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'username' => $request->input('username'),
                'postal_code' => $postalCodeCleaned,
                'address' => $request->input('address'),
                'building_name' => $request->input('building_name'),
            ]
        );

        if ($request->has('item_id') && $request->item_id) {
            return redirect()->route('items.purchase', ['item' => $request->item_id])->with('success', '住所情報が更新されました。');
        }

        return redirect()->route('mypage')->with("success", "住所情報が更新されました。");
    }
}
