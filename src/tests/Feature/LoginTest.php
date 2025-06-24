<?php

namespace Tests\Feature;

use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_email_required()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $response = $this->post("/login", [
            "email" => "",
            "password" => "password",
        ]);

        $response->assertSessionHasErrors("email");
        $this->assertGuest();
    }

    public function test_password_required()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $response = $this->post("/login", [
            "email" => "test1@example.com",
            "password" => "",
        ]);

        $response->assertSessionHasErrors("password");
        $this->assertGuest();
    }

    public function test_invalid_message()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_correct()
    {
        $user = DB::table("users")->insert([
            'name' => 'Test User',
            'email' => 'test1@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'profile_configured' => false,
        ]);

        $user = User::where("email", "test1@example.com")->first();
        $this->assertNotNull($user);

        $response = $this->post('/login', [
            'email' => 'test1@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);

        $response->assertRedirect('/mypage/profile');
    }
}