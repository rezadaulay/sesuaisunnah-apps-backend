<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\OtpService;
use App\Services\WAMasbro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected $waMasbroMock;
    protected $otpServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the WAMasbro service
        $this->waMasbroMock = Mockery::mock(WAMasbro::class);
        $this->app->instance(WAMasbro::class, $this->waMasbroMock);

        // Mock the OtpService
        $this->otpServiceMock = Mockery::mock(OtpService::class);
        $this->app->instance(OtpService::class, $this->otpServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_register_new_user_successfully()
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'country_code' => '+62',
            'phone' => '8399999453',
            'email' => 'john@example.com',
            'gender' => 'male',
        ];

        $this->otpServiceMock->shouldReceive('sendOtp')
            ->once()
            ->with('8399999453')
            ->andReturn([
                'success' => true,
                'data' => [
                    'expires_in' => 600,
                    'expires_at' => now()->addMinutes(10)->toISOString(),
                    'delivery_method' => 'whatsapp',
                ],
            ]);

        // Act
        $response = $this->postJson('/api/auth/register', $userData);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Registration successful! OTP has been sent to your WhatsApp for verification.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'phone',
                        'email',
                        'gender',
                    ],
                    'otp_info' => [
                        'phone',
                        'expires_in',
                        'expires_at',
                        'delivery_method',
                    ],
                    'next_step',
                ],
            ]);

        // Check if user was created in database
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'country_code' => '+62',
            'phone' => '8399999453',
            'email' => 'john@example.com',
            'gender' => 'male',
        ]);

        // Check if user has a password (random generated)
        $user = User::where('phone', '8399999453')->first();
        $this->assertNotNull($user->password);
        $this->assertNotEmpty($user->password);
    }

    /** @test */
    public function it_can_register_user_without_email()
    {
        // Arrange
        $userData = [
            'name' => 'Jane Doe',
            'country_code' => '+62',
            'phone' => '81234567891',
            'gender' => 'female',
        ];

        $this->otpServiceMock->shouldReceive('sendOtp')
            ->once()
            ->with('81234567891')
            ->andReturn([
                'success' => true,
                'data' => [
                    'expires_in' => 600,
                    'expires_at' => now()->addMinutes(10)->toISOString(),
                    'delivery_method' => 'whatsapp',
                ],
            ]);

        // Act
        $response = $this->postJson('/api/auth/register', $userData);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'name' => 'Jane Doe',
                        'phone' => '81234567891',
                        'gender' => 'female',
                        'email' => null,
                    ],
                ],
            ]);

        // Check if user was created in database
        $this->assertDatabaseHas('users', [
            'name' => 'Jane Doe',
            'phone' => '81234567891',
            'gender' => 'female',
            'email' => null,
        ]);
    }

    /** @test */
    public function it_can_register_user_without_gender()
    {
        // Arrange
        $userData = [
            'name' => 'Bob Smith',
            'country_code' => '+62',
            'phone' => '81234567892',
            'email' => 'bob@example.com',
        ];

        $this->otpServiceMock->shouldReceive('sendOtp')
            ->once()
            ->with('81234567892')
            ->andReturn([
                'success' => true,
                'data' => [
                    'expires_in' => 600,
                    'expires_at' => now()->addMinutes(10)->toISOString(),
                    'delivery_method' => 'whatsapp',
                ],
            ]);

        // Act
        $response = $this->postJson('/api/auth/register', $userData);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'name' => 'Bob Smith',
                        'phone' => '81234567892',
                        'email' => 'bob@example.com',
                        'gender' => null,
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_prevents_duplicate_phone_registration()
    {
        // Arrange - Create existing user
        User::create([
            'name' => 'Existing User',
            'phone' => '81234567890',
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
        ]);

        $userData = [
            'name' => 'New User',
            'country_code' => '+62',
            'phone' => '81234567890', // Same phone number
            'email' => 'new@example.com',
        ];

        // Act
        $response = $this->postJson('/api/auth/register', $userData);

        // Assert
        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'User with this phone number already exists.',
            ]);

        // Check that no new user was created
        $this->assertDatabaseCount('users', 1);
    }

    /** @test */
    public function it_prevents_duplicate_email_registration()
    {
        // Arrange - Create existing user
        User::create([
            'name' => 'Existing User',
            'phone' => '81234567890',
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
        ]);

        $userData = [
            'name' => 'New User',
            'country_code' => '+62',
            'phone' => '81234567891', // Different phone
            'email' => 'existing@example.com', // Same email
        ];

        // Act
        $response = $this->postJson('/api/auth/register', $userData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Check that no new user was created
        $this->assertDatabaseCount('users', 1);
    }

    /** @test */
    public function it_handles_otp_sending_failure_gracefully()
    {
        // Arrange
        $userData = [
            'name' => 'Test User',
            'country_code' => '+62',
            'phone' => '81234567893',
            'email' => 'test@example.com',
        ];

        $this->otpServiceMock->shouldReceive('sendOtp')
            ->once()
            ->with('81234567893')
            ->andReturn([
                'success' => false,
                'message' => 'WhatsApp service temporarily unavailable',
                'code' => 500,
            ]);

        // Act
        $response = $this->postJson('/api/auth/register', $userData);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Registration successful! However, there was an issue sending OTP. Please try requesting OTP again.',
                'data' => [
                    'otp_status' => 'failed',
                    'otp_error' => 'WhatsApp service temporarily unavailable',
                ],
            ]);

        // Check if user was still created despite OTP failure
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'phone' => '81234567893',
            'email' => 'test@example.com',
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        // Act
        $response = $this->postJson('/api/auth/register', []);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'phone']);
    }

    /** @test */
    public function it_validates_name_format()
    {
        // Arrange
        $userData = [
            'name' => 'John123', // Invalid name with numbers
            'country_code' => '+62',
            'phone' => '81234567894',
        ];

        // Act
        $response = $this->postJson('/api/auth/register', $userData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_validates_phone_format()
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'country_code' => '+62',
            'phone' => 'invalid-phone', // Invalid phone format
        ];

        // Act
        $response = $this->postJson('/api/auth/register', $userData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    /** @test */
    public function it_validates_email_format()
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'country_code' => '+62',
            'phone' => '81234567895',
            'email' => 'invalid-email', // Invalid email format
        ];

        // Act
        $response = $this->postJson('/api/auth/register', $userData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_validates_gender_values()
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'country_code' => '+62',
            'phone' => '81234567896',
            'gender' => 'invalid-gender', // Invalid gender value
        ];

        // Act
        $response = $this->postJson('/api/auth/register', $userData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['gender']);
    }

    /** @test */
    public function it_cleans_input_data_before_validation()
    {
        // Arrange
        $userData = [
            'name' => '  John Doe  ', // With extra spaces
            'country_code' => '+62',
            'phone' => ' 81234567897 ', // With extra spaces
            'email' => '  JOHN@EXAMPLE.COM  ', // With extra spaces and uppercase
        ];

        $this->otpServiceMock->shouldReceive('sendOtp')
            ->once()
            ->with('81234567897')
            ->andReturn([
                'success' => true,
                'data' => [
                    'expires_in' => 600,
                    'expires_at' => now()->addMinutes(10)->toISOString(),
                    'delivery_method' => 'whatsapp',
                ],
            ]);

        // Act
        $response = $this->postJson('/api/auth/register', $userData);

        // Assert
        $response->assertStatus(201);

        // Check that data was cleaned and stored properly
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'phone' => '81234567897',
            'email' => 'john@example.com',
        ]);
    }
}
