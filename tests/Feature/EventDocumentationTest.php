<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Event;
use App\Models\User;
use App\Models\EventGallery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EventDocumentationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_upload_event_documentation()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['created_by' => $user->id]);

        $file = UploadedFile::fake()->image('event_photo.jpg', 800, 600);

        $response = $this->postJson("/api/events/{$event->id}/documentation/upload", [
            'files' => [$file],
            'type' => 'photo',
            'description' => 'Test event photo'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '1 file(s) uploaded successfully'
            ]);

        $this->assertDatabaseHas('event_galleries', [
            'event_id' => $event->id,
            'type' => 'photo',
            'description' => 'Test event photo'
        ]);
    }

    /** @test */
    public function it_validates_file_type_on_upload()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['created_by' => $user->id]);

        $file = UploadedFile::fake()->create('document.txt', 100);

        $response = $this->postJson("/api/events/{$event->id}/documentation/upload", [
            'files' => [$file],
            'type' => 'photo'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['files.0']);
    }

    /** @test */
    public function it_validates_file_size_on_upload()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['created_by' => $user->id]);

        $file = UploadedFile::fake()->image('large_photo.jpg')->size(11000); // 11MB

        $response = $this->postJson("/api/events/{$event->id}/documentation/upload", [
            'files' => [$file],
            'type' => 'photo'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['files.0']);
    }

    /** @test */
    public function it_can_retrieve_event_documentation()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['created_by' => $user->id]);
        
        EventGallery::factory()->create([
            'event_id' => $event->id,
            'type' => 'photo',
            'description' => 'Test photo'
        ]);

        $response = $this->getJson("/api/events/{$event->id}/documentation");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'event' => [
                        'id' => $event->id,
                        'title' => $event->title
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_filter_documentation_by_type()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['created_by' => $user->id]);
        
        EventGallery::factory()->create([
            'event_id' => $event->id,
            'type' => 'photo'
        ]);
        
        EventGallery::factory()->create([
            'event_id' => $event->id,
            'type' => 'video'
        ]);

        $response = $this->getJson("/api/events/{$event->id}/documentation?type=photo");

        $response->assertStatus(200);
        
        $data = $response->json('data.documentation');
        $this->assertCount(1, $data);
        $this->assertEquals('photo', $data[0]['type']);
    }

    /** @test */
    public function it_can_update_documentation_description()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['created_by' => $user->id]);
        $gallery = EventGallery::factory()->create([
            'event_id' => $event->id,
            'type' => 'photo'
        ]);

        $response = $this->putJson("/api/events/{$event->id}/documentation/description", [
            'documentation_id' => $gallery->id,
            'description' => 'Updated description'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Description updated successfully'
            ]);

        $this->assertDatabaseHas('event_galleries', [
            'id' => $gallery->id,
            'description' => 'Updated description'
        ]);
    }

    /** @test */
    public function it_can_delete_documentation()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['created_by' => $user->id]);
        $gallery = EventGallery::factory()->create([
            'event_id' => $event->id,
            'type' => 'photo'
        ]);

        $response = $this->deleteJson("/api/events/{$event->id}/documentation", [
            'documentation_id' => $gallery->id
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Documentation deleted successfully'
            ]);

        $this->assertDatabaseMissing('event_galleries', ['id' => $gallery->id]);
    }

    /** @test */
    public function it_can_get_events_with_documentation_summary()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['created_by' => $user->id]);
        
        EventGallery::factory()->count(3)->create([
            'event_id' => $event->id,
            'type' => 'photo'
        ]);

        $response = $this->getJson("/api/events/with-documentation");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    [
                        'id' => $event->id,
                        'documentation_summary' => [
                            'total_files' => 3,
                            'photos' => 3,
                            'videos' => 0,
                            'documents' => 0
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_validates_documentation_id_belongs_to_event()
    {
        $user = User::factory()->create();
        $event1 = Event::factory()->create(['created_by' => $user->id]);
        $event2 = Event::factory()->create(['created_by' => $user->id]);
        $gallery = EventGallery::factory()->create([
            'event_id' => $event1->id,
            'type' => 'photo'
        ]);

        $response = $this->deleteJson("/api/events/{$event2->id}/documentation", [
            'documentation_id' => $gallery->id
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Documentation not found for this event'
            ]);
    }

    /** @test */
    public function it_handles_multiple_file_uploads()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['created_by' => $user->id]);

        $files = [
            UploadedFile::fake()->image('photo1.jpg'),
            UploadedFile::fake()->image('photo2.jpg'),
            UploadedFile::fake()->image('photo3.jpg')
        ];

        $response = $this->postJson("/api/events/{$event->id}/documentation/upload", [
            'files' => $files,
            'type' => 'photo',
            'description' => 'Multiple photos'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '3 file(s) uploaded successfully'
            ]);

        $this->assertDatabaseCount('event_galleries', 3);
    }

    /** @test */
    public function it_supports_different_file_types()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['created_by' => $user->id]);

        $photo = UploadedFile::fake()->image('photo.jpg');
        $video = UploadedFile::fake()->create('video.mp4', 1000);
        $document = UploadedFile::fake()->create('document.pdf', 1000);

        // Test photo upload
        $response = $this->postJson("/api/events/{$event->id}/documentation/upload", [
            'files' => [$photo],
            'type' => 'photo'
        ]);
        $response->assertStatus(200);

        // Test video upload
        $response = $this->postJson("/api/events/{$event->id}/documentation/upload", [
            'files' => [$video],
            'type' => 'video'
        ]);
        $response->assertStatus(200);

        // Test document upload
        $response = $this->postJson("/api/events/{$event->id}/documentation/upload", [
            'files' => [$document],
            'type' => 'document'
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseCount('event_galleries', 3);
    }
}
