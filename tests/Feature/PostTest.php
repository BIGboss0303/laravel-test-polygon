<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_index(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $categories = Category::factory(3)->create();
        Post::factory(10)->create(['author_id' => $user->id])->each(function ($post) use ($categories, $user) {
            $post->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->toArray()
            );
        });

        $response = $this->getJson('api/v1/posts');
        $response
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json
                    ->has(10)
                    ->first(
                        fn(AssertableJson $json) =>
                        $json
                            ->whereAllType([
                                'post_id' => 'integer',
                                'name' => 'string',
                                'content' => 'string',
                                'author' => 'array',
                            ])
                            ->has(
                                'author',
                                fn($author) =>
                                $author
                                    ->where('id', $user->id)
                                    ->where('name', $user->name)
                                    ->where('email', $user->email)
                                    ->etc()
                            )
                    )
            );
    }

    public function test_show(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $category = Category::factory()->create();
        $post = Post::factory()->create(['author_id' => $user->id]);
        $post->categories()->attach([$category->id]);
        $response = $this->getJson("api/v1/posts/$post->id");
        $response
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json
                    ->whereAllType([
                        'post_id' => 'integer',
                        'name' => 'string',
                        'content' => 'string',
                        'author' => 'array',
                    ])
                    ->has(
                        'author',
                        fn($author) =>
                        $author
                            ->where('id', $user->id)
                            ->where('name', $user->name)
                            ->where('email', $user->email)
                            ->etc()
                    )
            );
    }

    public function test_store(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $category = Category::factory()->create();
        $response = $this->postJson("api/v1/posts",[
            'name' => 'Some post',
            'content' => 'Some content',
            'categories' => [$category->id]
        ]);
        $response
            ->assertStatus(201)
            ->assertJson(
                [
                    'post_id' => 1,
                    'name' => 'Some post',
                    'content' => 'Some content',
                    'author' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email
                    ]
                ]
            );
    }

    public function test_update(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $categories = Category::factory(2)->create();
        $post = Post::factory()->create(['author_id' => $user->id]);
        $post->categories()->attach([$categories[0]->id]);

        $response = $this->putJson("api/v1/posts/$post->id",[
            'name' => 'Some post updated',
            'content' => 'Some content updated',
            'categories' => [$categories[1]->id]
        ]);
        $response
            ->assertStatus(200)
            ->assertJson(
                [
                    'post_id' => 1,
                    'name' => 'Some post updated',
                    'content' => 'Some content updated',
                    'author' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email
                    ]
                ]
            );
    }

    public function test_update_access_denied(): void
    {
        $users = User::factory(2)->create();
        Sanctum::actingAs($users[1]);
        $categories = Category::factory(2)->create();
        $post = Post::factory()->create(['author_id' => $users[0]->id]);
        $post->categories()->attach([$categories[0]->id]);

        $response = $this->putJson("api/v1/posts/$post->id",[
            'name' => 'Some post updated',
            'content' => 'Some content updated',
            'categories' => [$categories[1]->id]
        ]);
        $response->assertStatus(403);
    }

    public function test_delete(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $post = Post::factory()->create(['author_id' => $user->id]);
        $response = $this->deleteJson("api/v1/posts/$post->id");
        $response->assertStatus(204);
    }

    public function test_delete_access_denied(): void
    {
        $users = User::factory(2)->create();
        Sanctum::actingAs($users[0]);
        $post = Post::factory()->create(['author_id' => $users[1]->id]);
        $response = $this->deleteJson("api/v1/posts/$post->id");
        $response->assertStatus(403);
    }
}
