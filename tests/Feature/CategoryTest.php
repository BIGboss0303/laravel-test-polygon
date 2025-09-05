<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use Laravel\Sanctum\Sanctum;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_index(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->seed(CategorySeeder::class);
        $response = $this->getJson('/api/v1/categories');
        $response
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has(5)
                    ->first(
                        fn(AssertableJson $json) => $json
                            ->whereAllType([
                                'category_id' => 'integer',
                                'name' => 'string',
                                'parent' => 'array|null'
                            ])
                    )
            );
    }

    public function test_index_guest_cannot_access(): void
    {
        $this->seed(CategorySeeder::class);
        $response = $this->getJson('/api/v1/categories');
        $response->assertStatus(401);
    }

    public function test_show(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->seed(CategorySeeder::class);
        $response = $this->getJson('/api/v1/categories/1');
        $response
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->whereAllType([
                    'category_id' => 'integer',
                    'name' => 'string',
                    'parent' => 'array|null'
                ])
            );
    }

    public function test_store(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/categories', [
            'name' => 'Фильмы',
        ]);
        $response
            ->assertStatus(201)
            ->assertJson(
                [
                    'category_id' => '1',
                    'name' => 'Фильмы',
                    'parent' => []
                ]
            );
    }

    public function test_store_with_parent_id(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $parent = Category::factory()->create();
        $response = $this->postJson('/api/v1/categories', [
            'name' => 'Фильмы',
            'parent_id' => $parent->id
        ]);
        $response
            ->assertStatus(201)
            ->assertJson(
                [
                    'category_id' => '2',
                    'name' => 'Фильмы',
                    'parent' => [
                        'category_id' => $parent->id,
                        'name' => $parent->name,
                        'parent' => [],

                    ]
                ]
            );
    }

    public function test_update(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $category = Category::factory()->create();
        $parent = Category::factory()->create();
        $response = $this->putJson("/api/v1/categories/$category->id", [
            'name' => 'Фильмы',
            'parent_id' => $parent->id
        ]);
        $response
            ->assertStatus(200)
            ->assertJson(
                [
                    'category_id' => $category->id,
                    'name' => 'Фильмы',
                    'parent' => [
                        'category_id' => $parent->id,
                        'name' => $parent->name,
                        'parent' => [],

                    ]
                ]
            );
    }

    public function test_delete(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $category = Category::factory()->create();
        $response = $this->deleteJson("/api/v1/categories/$category->id");
        $response->assertStatus(204);
    }

    public function test_delete_not_found(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->deleteJson('/api/v1/categories/1');
        $response->assertStatus(404);
    }

}
