<?php

namespace Tests\Feature\Managers\Blogs;

use App\Models\Blog\BlogCategorie;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected BlogCategorie $categorie;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
        $this->categorie = BlogCategorie::factory()->create();
    }

    public function test_store_rejects_empty_title(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.store'), [
                'title' => '',
                'categorie' => $this->categorie->id,
                'available' => 1,
                'date' => now()->format('Y-m-d'),
                'contents' => '<p>Contenido</p>',
                'description' => 'Descripcion',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_store_rejects_nonexistent_category(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.store'), [
                'title' => 'Noticia valida',
                'categorie' => 999999,
                'available' => 1,
                'date' => now()->format('Y-m-d'),
                'contents' => '<p>Contenido</p>',
                'description' => 'Descripcion',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('categorie');
    }

    public function test_category_store_rejects_empty_title(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.blogs.categories.store'), [
                'title' => '',
                'available' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }
}
