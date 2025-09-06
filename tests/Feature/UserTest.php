<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_index(): void
    {
        $users = User::factory(10)->create();
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
                            'email' => 'string'
                        ])
                    )

            );
    }

    public function test_show(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/v1/users/' . $user->id);
        $response
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->whereAllType([
                    'user_id' => 'integer',
                    'name' => 'string',
                    'email' => 'string'
                ])
            );
    }

    public function test_store(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/users', [
            'name' => 'John',
            'email' => 'john@gmail.com',
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
                    )

            );
    }

    public function test_register(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John',
            'email' => 'john@gmail.com',
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
                    )
            );
    }

    public function test_update(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->putJson('/api/v1/users/' . $user->id, [
            'name' => 'John Smith'
        ]);
        $response
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json
                    ->where('user_id', 1)
                    ->where('name', 'John Smith')
                    ->etc()
            );
    }

    public function test_delete(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->deleteJson('/api/v1/users/' . $user->id);
        $response->assertStatus(204);
    }
}
