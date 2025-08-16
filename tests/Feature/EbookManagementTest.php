<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Ebook;
use App\Models\User;
use App\Models\AudiobookFile;
use App\Models\EbookInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EbookManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_list_ebooks()
    {
        $user = User::factory()->create();
        Ebook::factory()->count(3)->create(['created_by' => $user->id]);

        $response = $this->getJson('/api/ebooks');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function it_can_filter_ebooks_by_price_type()
    {
        $user = User::factory()->create();
        Ebook::factory()->free()->create(['created_by' => $user->id]);
        Ebook::factory()->paid()->create(['created_by' => $user->id]);

        $response = $this->getJson('/api/ebooks?price_type=free');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(0, $response->json('data.0.price'));

        $response = $this->getJson('/api/ebooks?price_type=paid');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertGreaterThan(0, $response->json('data.0.price'));
    }

    /** @test */
    public function it_can_search_ebooks()
    {
        $user = User::factory()->create();
        Ebook::factory()->create([
            'title' => 'Islamic Finance Guide',
            'created_by' => $user->id
        ]);
        Ebook::factory()->create([
            'title' => 'Cooking Recipes',
            'created_by' => $user->id
        ]);

        $response = $this->getJson('/api/ebooks?search=Islamic');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertStringContainsString('Islamic', $response->json('data.0.title'));
    }

    /** @test */
    public function it_can_get_popular_ebooks()
    {
        $user = User::factory()->create();
        $ebook1 = Ebook::factory()->create(['created_by' => $user->id]);
        $ebook2 = Ebook::factory()->create(['created_by' => $user->id]);

        // Create interactions for ebook1
        EbookInteraction::factory()->count(5)->create(['ebook_id' => $ebook1->id]);
        EbookInteraction::factory()->count(2)->create(['ebook_id' => $ebook2->id]);

        $response = $this->getJson('/api/ebooks/popular?limit=2');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals($ebook1->id, $response->json('data.0.id'));
    }

    /** @test */
    public function it_can_get_free_ebooks()
    {
        $user = User::factory()->create();
        Ebook::factory()->free()->count(2)->create(['created_by' => $user->id]);
        Ebook::factory()->paid()->count(3)->create(['created_by' => $user->id]);

        $response = $this->getJson('/api/ebooks/free');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        foreach ($response->json('data') as $ebook) {
            $this->assertEquals(0, $ebook['price']);
        }
    }

    /** @test */
    public function it_can_get_ebooks_with_audiobooks()
    {
        $user = User::factory()->create();
        $ebook1 = Ebook::factory()->create(['created_by' => $user->id]);
        $ebook2 = Ebook::factory()->create(['created_by' => $user->id]);

        // Add audiobook files to ebook1
        AudiobookFile::factory()->count(3)->create(['ebook_id' => $ebook1->id]);

        $response = $this->getJson('/api/ebooks/with-audiobook');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($ebook1->id, $response->json('data.0.id'));
    }

    /** @test */
    public function it_can_show_ebook_details()
    {
        $user = User::factory()->create();
        $ebook = Ebook::factory()->create(['created_by' => $user->id]);

        $response = $this->getJson("/api/ebooks/{$ebook->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $ebook->id,
                    'title' => $ebook->title,
                ]
            ]);
    }

    /** @test */
    public function it_can_create_ebook()
    {
        $user = User::factory()->create();
        
        $ebookFile = UploadedFile::fake()->create('test.pdf', 1000);
        $coverImage = UploadedFile::fake()->image('cover.jpg', 800, 600);

        $response = $this->postJson('/api/ebooks', [
            'title' => 'Test Ebook',
            'description' => 'Test description',
            'price' => 50000,
            'ebook_file' => $ebookFile,
            'cover_image' => $coverImage,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Ebook created successfully'
            ]);

        $this->assertDatabaseHas('ebooks', [
            'title' => 'Test Ebook',
            'price' => 50000,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_on_create()
    {
        $response = $this->postJson('/api/ebooks', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'price', 'ebook_file']);
    }

    /** @test */
    public function it_validates_file_types_on_create()
    {
        $invalidFile = UploadedFile::fake()->create('test.txt', 1000);

        $response = $this->postJson('/api/ebooks', [
            'title' => 'Test Ebook',
            'price' => 50000,
            'ebook_file' => $invalidFile,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ebook_file']);
    }

    /** @test */
    public function it_can_update_ebook()
    {
        $user = User::factory()->create();
        $ebook = Ebook::factory()->create(['created_by' => $user->id]);

        $response = $this->putJson("/api/ebooks/{$ebook->id}", [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'price' => 75000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ebook updated successfully'
            ]);

        $this->assertDatabaseHas('ebooks', [
            'id' => $ebook->id,
            'title' => 'Updated Title',
            'price' => 75000,
        ]);
    }

    /** @test */
    public function it_can_update_ebook_cover_image()
    {
        $user = User::factory()->create();
        $ebook = Ebook::factory()->create(['created_by' => $user->id]);
        $newCover = UploadedFile::fake()->image('new_cover.jpg', 800, 600);

        $response = $this->putJson("/api/ebooks/{$ebook->id}", [
            'cover_image' => $newCover,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('ebooks', [
            'id' => $ebook->id,
        ]);
    }

    /** @test */
    public function it_can_delete_ebook()
    {
        $user = User::factory()->create();
        $ebook = Ebook::factory()->create(['created_by' => $user->id]);

        $response = $this->deleteJson("/api/ebooks/{$ebook->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ebook deleted successfully'
            ]);

        $this->assertDatabaseMissing('ebooks', ['id' => $ebook->id]);
    }

    /** @test */
    public function it_can_record_user_interaction()
    {
        $user = User::factory()->create();
        $ebook = Ebook::factory()->create();

        $response = $this->postJson("/api/ebooks/{$ebook->id}/interact", [
            'action' => 'read',
            'user_id' => $user->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Interaction recorded successfully'
            ]);

        $this->assertDatabaseHas('ebook_interactions', [
            'ebook_id' => $ebook->id,
            'user_id' => $user->id,
            'action' => 'read',
        ]);
    }

    /** @test */
    public function it_validates_interaction_action()
    {
        $user = User::factory()->create();
        $ebook = Ebook::factory()->create();

        $response = $this->postJson("/api/ebooks/{$ebook->id}/interact", [
            'action' => 'invalid_action',
            'user_id' => $user->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['action']);
    }

    /** @test */
    public function it_can_get_ebook_statistics()
    {
        $user = User::factory()->create();
        $ebook = Ebook::factory()->create(['created_by' => $user->id]);

        // Create interactions
        EbookInteraction::factory()->count(3)->read()->create(['ebook_id' => $ebook->id]);
        EbookInteraction::factory()->count(2)->download()->create(['ebook_id' => $ebook->id]);
        EbookInteraction::factory()->count(1)->listen()->create(['ebook_id' => $ebook->id]);

        $response = $this->getJson("/api/ebooks/{$ebook->id}/statistics");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'read_count' => 3,
                    'download_count' => 2,
                    'listen_count' => 1,
                    'total_interactions' => 6,
                ]
            ]);
    }

    /** @test */
    public function it_can_upload_audiobook_files()
    {
        $user = User::factory()->create();
        $ebook = Ebook::factory()->create(['created_by' => $user->id]);

        $audioFiles = [
            UploadedFile::fake()->create('chapter1.mp3', 1000),
            UploadedFile::fake()->create('chapter2.mp3', 1000),
        ];

        $response = $this->postJson("/api/ebooks/{$ebook->id}/audiobook/upload", [
            'audiobook_files' => $audioFiles,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '2 audiobook file(s) uploaded successfully'
            ]);

        $this->assertDatabaseCount('audiobook_files', 2);
    }

    /** @test */
    public function it_validates_audiobook_file_types()
    {
        $user = User::factory()->create();
        $ebook = Ebook::factory()->create(['created_by' => $user->id]);

        $invalidFile = UploadedFile::fake()->create('chapter.txt', 1000);

        $response = $this->postJson("/api/ebooks/{$ebook->id}/audiobook/upload", [
            'audiobook_files' => [$invalidFile],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['audiobook_files.0']);
    }

    /** @test */
    public function it_can_delete_audiobook_file()
    {
        $user = User::factory()->create();
        $ebook = Ebook::factory()->create(['created_by' => $user->id]);
        $audioFile = AudiobookFile::factory()->create(['ebook_id' => $ebook->id]);

        $response = $this->deleteJson("/api/ebooks/{$ebook->id}/audiobook", [
            'audiobook_file_id' => $audioFile->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Audiobook file deleted successfully'
            ]);

        $this->assertDatabaseMissing('audiobook_files', ['id' => $audioFile->id]);
    }

    /** @test */
    public function it_can_sort_ebooks_by_title()
    {
        $user = User::factory()->create();
        Ebook::factory()->create(['title' => 'Zebra Book', 'created_by' => $user->id]);
        Ebook::factory()->create(['title' => 'Alpha Book', 'created_by' => $user->id]);

        $response = $this->getJson('/api/ebooks?sort_by=title&sort_order=asc');

        $response->assertStatus(200);
        $this->assertEquals('Alpha Book', $response->json('data.0.title'));
        $this->assertEquals('Zebra Book', $response->json('data.1.title'));
    }

    /** @test */
    public function it_can_sort_ebooks_by_popularity()
    {
        $user = User::factory()->create();
        $ebook1 = Ebook::factory()->create(['created_by' => $user->id]);
        $ebook2 = Ebook::factory()->create(['created_by' => $user->id]);

        // Create more interactions for ebook2
        EbookInteraction::factory()->count(5)->create(['ebook_id' => $ebook2->id]);
        EbookInteraction::factory()->count(2)->create(['ebook_id' => $ebook1->id]);

        $response = $this->getJson('/api/ebooks?sort_by=popularity');

        $response->assertStatus(200);
        $this->assertEquals($ebook2->id, $response->json('data.0.id'));
    }

    /** @test */
    public function it_can_paginate_ebooks()
    {
        $user = User::factory()->create();
        Ebook::factory()->count(15)->create(['created_by' => $user->id]);

        $response = $this->getJson('/api/ebooks?per_page=5');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
        $this->assertEquals(15, $response->json('pagination.total'));
        $this->assertEquals(3, $response->json('pagination.last_page'));
    }
}
