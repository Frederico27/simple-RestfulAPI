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

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->get('/api/users/current', [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'test'
                ]
            ]);
    }

    public function testGetUnauthorized()
    {

        $this->seed([UserSeeder::class]);

        $this->get('/api/users/current', [])
            ->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => ['unauthorized']
                ]
            ]);
    }

    public function testGetInvalidToken()
    {
        $this->seed([UserSeeder::class]);

        $this->get('/api/users/current', [
            'Authorized' => 'sala'
        ])
            ->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => ['unauthorized']
                ]
            ]);
    }

    public function testUpdateNameSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();

        $this->patch('/api/users/current', [
            'name' => 'Joao'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'Joao'
                ]
            ]);

        $newUser = User::where('username', 'test')->first();
        self::assertNotEquals($oldUser->name, $newUser->name);
    }

    public function testUpdatePasswordSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();

        $this->patch('/api/users/current', [
            'password' => 'rahasia8@'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'test'
                ]
            ]);

        $newUser = User::where('username', 'test')->first();
        self::assertNotEquals($oldUser->password, $newUser->password);
    }

    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->patch('/api/users/current', [
            'password' => 'foun'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'password' => [
                        "The password field must be at least 8 characters.",
                        "The password field must contain at least one symbol.",
                        "The password field must contain at least one number."
                    ]
                ]
            ]);
    }

    public function testLogoutSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->delete('/api/users/logout', headers: [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);

        $user = User::where('username', 'test')->first();
        self::assertNull($user->token);
    }

    public function testLogoutFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->delete('/api/users/logout', headers: [
            'Authorization' => 'lalos'
        ])->assertStatus(401)
            ->assertJson([
                "errors" => [
                    'message' => [
                        'unauthorized'
                    ]
                ]
            ]);
    }
}
