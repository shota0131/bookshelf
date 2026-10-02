<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 会員登録画面にアクセスできる()
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    /** @test */
    public function 正しい情報で会員登録できる()
    {
        $response = $this->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);

        $this->assertAuthenticated();
    }

    /** @test */
    public function 不正な情報では会員登録できない()
    {
        $response = $this->from(route('register'))
            ->post(route('register'), [
                'name' => '',
                'email' => 'invalid-email',
                'password' => '123',
                'password_confirmation' => '456',
            ]);

        $response->assertSessionHasErrors([
            'name',
            'email',
            'password',
        ]);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    /** @test */
    public function ログイン画面にアクセスできる()
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    /** @test */
    public function 正しい情報でログインできる()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect();

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function 誤ったパスワードではログインできない()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->from(route('login'))
            ->post(route('login'), [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /** @test */
    public function ログインユーザーはログアウトできる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('logout'));

        $response->assertRedirect();
        $this->assertGuest();
    }
}