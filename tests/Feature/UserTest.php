<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testRegisterSuccess()
    {
        $this->post('/api/users', [
            'username' => 'Riko',
            'password' => 'rahasia8@',
            'name' => 'Ariko Marcal',
        ])->assertStatus(201)
            ->assertJson([
                'data' => [
                    'username' => 'Riko',
                    'name' => 'Ariko Marcal'
                ]
            ]);
    }

    public function testRegisterFailed()
    {
        $this->post('/api/users', [
            'username' => '',
            'password' => '',
            'name' => '',
        ])->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'username' => ['The username field is required.'],
                    'name' => ['The name field is required.'],
                    'password' => ['The password field is required.']
                ]
            ]);
    }

    public function testRegisterUsernameAlreadyExists()
    {
        $this->testRegisterSuccess();


        $this->post('/api/users', [
            'username' => 'Riko',
            'password' => 'rahasia8@',
            'name' => 'Ariko Marcal',
        ])->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'username' => ['username already registered'],
                ]
            ]);
    }

    public function testloginSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'rahasia@8',
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'test'
                ]
            ]);

        $user = User::where('username', 'test')->first();
        self::assertNotNull($user->token);
    }

    public function testloginFailed()
    {
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'rahasia@8',
        ])->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => ['username or password wrong']
                ]
            ]);
    }

    public function testloginPasswordWrong()
    {
        $this->seed([UserSeeder::class]);

        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'rahasia@9',
        ])->assertStatus(401)
            ->assertJson([
                "errors" => [
                    'message' => ['username or password wrong']
                ]
            ]);
    }
}
