<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_name_required()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post("/register", [
            "name" => "",
            "email" => "test1@example.com",
            "password" => "password",
            "password_confirmation" => "password",
        ]);

        $response->assertSessionHasErrors("name", "お名前を入力してください。");

        $this->assertDatabaseMissing("users", ["email" => "test1@example.com"]);
    }

    public function test_email_required()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post("/register", [
            "name" => "Test User",
            "email" => "",
            "password" => "password",
            "password_confirmation" => "password",
        ]);

        $response->assertSessionHasErrors("email", "メールアドレスを入力してください。");

        $this->assertDatabaseMissing("users", ["name" => "Test User"]);
    }

    public function test_password_required()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post("/register", [
            "name" => "Test User",
            "email" => "test1@example.com",
            "password" => "",
            "password_confirmation" => "",
        ]);

        $response->assertSessionHasErrors("password", "パスワードを入力してください。");

        $this->assertDatabaseMissing("users", ["email" => "test1@example.com"]);
    }

    public function test_password_min()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post("/register", [
            "name" => "Test User",
            "email" => "test1@example.com",
            "password" => "pass123",
            "password_confirmation" => "pass123",
        ]);

        $response->assertSessionHasErrors("password", "パスワードは8文字以上で入力してください。");

        $this->assertDatabaseMissing("users", ["email" => "test1@example.com"]);
    }

    public function test_password_confirm()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post("/register", [
            "name" => "Test User",
            "email" => "test1@example.com",
            "password" => "password",
            "password_confirmation" => "pass1234",
        ]);

        $response->assertSessionHasErrors("password", "パスワードと一致しません。");

        $this->assertDatabaseMissing("users", ["email" => "test1@example.com"]);
    }

    public function test_registration_redirect()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'correctpassword',
            'password_confirmation' => 'correctpassword',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'New User',
        ]);

        $response->assertRedirect('/email/verify');
    }
}
