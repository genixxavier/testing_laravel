<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Post;
use App\User;

class PostControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_store()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user,'api')->json('POST','/api/posts',[
            'title' => 'El post title'
        ]);

        $response->assertJsonStructure(['id','title','created_at','updated_at'])
                ->assertJson(['title' =>'El post title'])
                ->assertStatus(201);

        $this->assertDatabaseHas('posts',['title'=>'El post title']);
    }

    public function test_validate_title() {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('POST','/api/posts',[
            'title' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    public function test_show() {

        $user = factory(User::class)->create();
        $post = factory(Post::class)->create();

        $response = $this->actingAs($user,'api')->json('GET',"/api/posts/$post->id");
        $response->assertJsonStructure(['id','title','created_at','updated_at'])
                ->assertJson(['title' => $post->title])
                ->assertStatus(200);
    }

    public function test_404_show() {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user,'api')->json('GET',"/api/posts/100");
        $response->assertStatus(404);
    }

    public function test_update()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->create();
        $post = factory(Post::class)->create();

        $response = $this->actingAs($user,'api')->json('PUT',"/api/posts/$post->id",[
            'title' => 'Nuevo titulo'
        ]);

        $response->assertJsonStructure(['id','title','created_at','updated_at'])
                ->assertJson(['title' =>'Nuevo titulo'])
                ->assertStatus(200);

        $this->assertDatabaseHas('posts',['title'=>'Nuevo titulo']);
    }

    public function test_destroy()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->create();
        $post = factory(Post::class)->create();

        $response = $this->actingAs($user,'api')->json('DELETE',"/api/posts/$post->id");

        $response->assertSee(null)
            ->assertStatus(204);

        $this->assertDatabaseMissing('posts',['id' => $post->id]);
    }

    public function test_index() {
        factory(Post::class,5)->create();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user,'api')->json('GET','/api/posts');

        $response->assertJsonStructure([
            'data' => [
                '*' => ['id','title','created_at', 'updated_at']
            ]
        ])->assertStatus(200);

    }

    public function test_guest() {
        $this->json('GET','/api/posts')->assertStatus(401);
        $this->json('POST','/api/posts')->assertStatus(401);
        $this->json('GET','/api/posts/1000')->assertStatus(401);
        $this->json('PUT','/api/posts/1000')->assertStatus(401);
        $this->json('DELETE','/api/posts/100')->assertStatus(401);
    }
}
