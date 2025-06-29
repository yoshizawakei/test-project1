<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserProfileUpdateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_profile_display_initial_values()
    {
        Storage::fake("public");

        $user = User::factory()->create();
        $profileImage = UploadedFile::fake()->image("profile.jpg", 100, 100);
        $profileImagePath = $profileImage->store("profile_images", "public");

        $profile = Profile::factory()->create([
            "user_id" => $user->id,
            "username" => "テストユーザー",
            "profile_image" => $profileImagePath,
            "postal_code" => "1234567",
            "address" => "東京都新宿区テスト町1-2-3",
            "building_name" => "テストビル",
        ]);

        $response = $this->actingAs($user)->get(route("mypage.profile"));
        $response->assertStatus(200);

        $response->assertSee('value="' . htmlspecialchars($profile->username) . '"', false);
        $response->assertSee('value="' . htmlspecialchars($profile->postal_code) . '"', false);
        $response->assertSee('value="' . htmlspecialchars($profile->address) . '"', false);
        $response->assertSee('value="' . htmlspecialchars($profile->building_name) . '"', false);

        $response->assertSee('src="' . asset("storage/" . $profile->profile_image) . '"', false);
        Storage::disk("public")->assertExists($profile->profile_image);
    }
}
