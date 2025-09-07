<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PhoneNumber;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['mysql', 'safe_mysql'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    public function test_index(): void
    {
        $users = User::factory(10)->create();
        PhoneNumber::create(['user_id' => $users[0]->id, 'number' => 89286977797]);
        Sanctum::actingAs($users[0]);
        $response = $this->getJson('/api/v1/users');
        $response
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json
                    ->has(10)
                    ->first(
                        fn(AssertableJson $json) =>
                        $json->whereAllType([
                            'user_id' => 'integer',
                            'name' => 'string',
                            'email' => 'string',
                            'number' => 'string|null'
                        ])
                    )

            );
    }

    public function test_store(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/users', [
            'name' => 'John',
            'email' => 'john@gmail.com',
            'number' => '89286977797',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response
            ->assertStatus(201)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json
                    ->whereAllType([
                        'message' => 'string',
                        'user' => 'array',
                    ])
                    ->has(
                        'user',
                        fn($user) =>
                        $user
                            ->where('user_id', 2)
                            ->where('name', 'John')
                            ->where('email', 'john@gmail.com')
                            ->where('number', '89286977797')
                    )
            );
    }

    public function test_show(): void
    {
        $user = User::factory()->create();
        PhoneNumber::create(['user_id' => $user->id, 'number' => 89286977797]);
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/v1/users/' . $user->id);
        $response
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->whereAllType([
                    'user_id' => 'integer',
                    'name' => 'string',
                    'email' => 'string',
                    'number' => 'string'
                ])
            );
    }



    public function test_register(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John',
            'email' => 'john@gmail.com',
            'number' => '89286977797',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response
            ->assertStatus(201)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json
                    ->whereAllType([
                        'message' => 'string',
                        'user' => 'array',
                        'token' => 'string',
                    ])
                    ->has(
                        'user',
                        fn($user) =>
                        $user
                            ->where('user_id', 1)
                            ->where('name', 'John')
                            ->where('email', 'john@gmail.com')
                            ->where('number' ,'89286977797')
                    )
            );
    }

    public function test_update(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->putJson('/api/v1/users/' . $user->id, [
            'name' => 'John Smith',
            'number' => '89286977797',
        ]);
        $response
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json
                    ->where('user_id', 1)
                    ->where('name', 'John Smith')
                    ->where('number', '89286977797')
                    ->etc()
            );
    }

    public function test_delete(): void
    {
        $user = User::factory()->create();
        PhoneNumber::create(['user_id' => $user->id, 'number' => 89286977797]);
        Sanctum::actingAs($user);
        $response = $this->deleteJson('/api/v1/users/' . $user->id);
        $response->assertStatus(204);
    }
}
