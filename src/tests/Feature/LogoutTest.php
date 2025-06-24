<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_logout()
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

        $response = $this->post('/logout');

        $response->assertRedirect('/');
    }
}
