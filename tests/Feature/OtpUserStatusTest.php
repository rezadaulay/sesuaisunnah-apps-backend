<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\OtpCode;
use App\Models\User;
use App\Services\OtpService;
use App\Services\WAMasbro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class OtpUserStatusTest extends TestCase
{
    use RefreshDatabase;

    protected $waMasbroMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the WAMasbro service
        $this->waMasbroMock = Mockery::mock(WAMasbro::class);
        $this->app->instance(WAMasbro::class, $this->waMasbroMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_does_not_send_otp_for_unregistered_user()
    {
        // Arrange
        $phone = '081234567890';

        // WAMasbro should NOT be called for unregistered user
        $this->waMasbroMock->shouldNotReceive('sendTextMessage');

        $otpService = new OtpService($this->waMasbroMock);

        // Act
        $result = $otpService->sendOtp($phone);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals(404, $result['code']);
        $this->assertEquals('Phone number not registered. Please register first.', $result['message']);
        $this->assertEquals('not_registered', $result['user_status']);
        $this->assertEquals('redirect_to_registration', $result['action_required']);

        // Check that no OTP was created in database
        $this->assertDatabaseMissing('otp_codes', [
            'phone' => $phone,
        ]);
    }

    /** @test */
    public function it_sends_otp_for_registered_user()
    {
        // Arrange
        $phone = '081234567890';

        // Create a registered user
        User::create([
            'name' => 'John Doe',
            'phone' => $phone,
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        // WAMasbro should be called for registered user
        $this->waMasbroMock->shouldReceive('sendTextMessage')
            ->once()
            ->andReturn(true);

        $otpService = new OtpService($this->waMasbroMock);

        // Act
        $result = $otpService->sendOtp($phone);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('OTP sent successfully via WhatsApp for login', $result['message']);
        $this->assertEquals('existing', $result['user_status']);
        $this->assertEquals('Verify OTP using /api/auth/verify-otp endpoint', $result['next_step']);

        // Check that OTP was created in database
        $this->assertDatabaseHas('otp_codes', [
            'phone' => $phone,
            'is_used' => false,
        ]);
    }

    /** @test */
    public function it_returns_proper_response_structure_for_unregistered_user()
    {
        // Arrange
        $phone = '081234567890';

        $otpService = new OtpService($this->waMasbroMock);

        // Act
        $result = $otpService->sendOtp($phone);

        // Assert
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('user_status', $result);
        $this->assertArrayHasKey('action_required', $result);
        $this->assertArrayHasKey('data', $result);

        $this->assertFalse($result['success']);
        $this->assertEquals(404, $result['code']);
        $this->assertEquals('not_registered', $result['user_status']);
        $this->assertEquals('redirect_to_registration', $result['action_required']);
        $this->assertEquals($phone, $result['data']['phone']);
    }

    /** @test */
    public function it_returns_proper_response_structure_for_registered_user()
    {
        // Arrange
        $phone = '081234567890';

        // Create a registered user
        User::create([
            'name' => 'John Doe',
            'phone' => $phone,
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->waMasbroMock->shouldReceive('sendTextMessage')
            ->once()
            ->andReturn(true);

        $otpService = new OtpService($this->waMasbroMock);

        // Act
        $result = $otpService->sendOtp($phone);

        // Assert
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('user_status', $result);
        $this->assertArrayHasKey('next_step', $result);

        $this->assertTrue($result['success']);
        $this->assertEquals('existing', $result['user_status']);
        $this->assertArrayHasKey('phone', $result['data']);
        $this->assertArrayHasKey('expires_in', $result['data']);
        $this->assertArrayHasKey('expires_at', $result['data']);
        $this->assertArrayHasKey('delivery_method', $result['data']);
    }

    /** @test */
    public function it_handles_multiple_phone_numbers_correctly()
    {
        // Arrange
        $registeredPhone = '081234567890';
        $unregisteredPhone = '081234567891';

        // Create a registered user
        User::create([
            'name' => 'John Doe',
            'phone' => $registeredPhone,
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->waMasbroMock->shouldReceive('sendTextMessage')
            ->once()
            ->with(Mockery::pattern('/^62/'), Mockery::any())
            ->andReturn(true);

        $otpService = new OtpService($this->waMasbroMock);

        // Act & Assert for registered user
        $result1 = $otpService->sendOtp($registeredPhone);
        $this->assertTrue($result1['success']);
        $this->assertEquals('existing', $result1['user_status']);

        // Act & Assert for unregistered user
        $result2 = $otpService->sendOtp($unregisteredPhone);
        $this->assertFalse($result2['success']);
        $this->assertEquals('not_registered', $result2['user_status']);
        $this->assertEquals(404, $result2['code']);
    }
}
