<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\OtpCode;
use App\Models\User;
use App\Services\OtpService;
use App\Services\WAMasbro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class OtpWhatsAppTest extends TestCase
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
    public function it_can_send_otp_via_whatsapp()
    {
        // Arrange
        $phone = '081234567890';

        // Create a user first (required for OTP sending)
        User::create([
            'name' => 'Test User',
            'phone' => $phone,
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->waMasbroMock->shouldReceive('sendTextMessage')
            ->once()
            ->andReturn(true);

        $otpService = new OtpService($this->waMasbroMock);

        // Act
        $result = $otpService->sendOtp($phone);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('OTP sent successfully via WhatsApp for login', $result['message']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($phone, $result['data']['phone']);
        $this->assertEquals('whatsapp', $result['data']['delivery_method']);

        // Check if OTP was created in database
        $this->assertDatabaseHas('otp_codes', [
            'phone' => $phone,
            'is_used' => false,
        ]);
    }

    /** @test */
    public function it_prevents_sending_otp_too_frequently()
    {
        // Arrange
        $phone = '081234567890';

        // Create a user first (required for OTP sending)
        User::create([
            'name' => 'Test User',
            'phone' => $phone,
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create an existing valid OTP
        OtpCode::create([
            'phone' => $phone,
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'is_used' => false,
        ]);

        $otpService = new OtpService($this->waMasbroMock);

        // Act
        $result = $otpService->sendOtp($phone);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals(429, $result['code']);
        $this->assertEquals('OTP already sent. Please wait before requesting another.', $result['message']);
    }

    /** @test */
    public function it_can_verify_valid_otp()
    {
        // Arrange
        $phone = '081234567890';
        $code = '123456';

        OtpCode::create([
            'phone' => $phone,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
            'is_used' => false,
        ]);

        $otpService = new OtpService($this->waMasbroMock);

        // Act
        $result = $otpService->verifyOtp($phone, $code);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('OTP verified successfully', $result['message']);

        // Check if OTP was marked as used
        $this->assertDatabaseHas('otp_codes', [
            'phone' => $phone,
            'code' => $code,
            'is_used' => true,
        ]);
    }

    /** @test */
    public function it_rejects_expired_otp()
    {
        // Arrange
        $phone = '081234567890';
        $code = '123456';

        OtpCode::create([
            'phone' => $phone,
            'code' => $code,
            'expires_at' => now()->subMinutes(1), // Expired
            'is_used' => false,
        ]);

        $otpService = new OtpService($this->waMasbroMock);

        // Act
        $result = $otpService->verifyOtp($phone, $code);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals(401, $result['code']);
        $this->assertEquals('Invalid or expired OTP code', $result['message']);
    }

    /** @test */
    public function it_handles_whatsapp_send_failure()
    {
        // Arrange
        $phone = '081234567890';

        // Create a user first (required for OTP sending)
        User::create([
            'name' => 'Test User',
            'phone' => $phone,
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->waMasbroMock->shouldReceive('sendTextMessage')
            ->once()
            ->andReturn(false);

        $otpService = new OtpService($this->waMasbroMock);

        // Act
        $result = $otpService->sendOtp($phone);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals(500, $result['code']);
        $this->assertEquals('Failed to send OTP via WhatsApp. Please try again.', $result['message']);

        // Check if OTP was marked as used due to failure
        $this->assertDatabaseHas('otp_codes', [
            'phone' => $phone,
            'is_used' => true,
        ]);
    }

    /** @test */
    public function it_formats_phone_number_correctly_for_whatsapp()
    {
        // Arrange
        $otpService = new OtpService($this->waMasbroMock);

        // Test cases: input => expected output
        $testCases = [
            '081234567890' => '6281234567890',
            '+6281234567890' => '6281234567890',
            '6281234567890' => '6281234567890',
            '81234567890' => '6281234567890',
        ];

        foreach ($testCases as $input => $expected) {
            // Use reflection to access protected method
            $reflection = new \ReflectionClass($otpService);
            $method = $reflection->getMethod('formatPhoneForWhatsApp');
            $method->setAccessible(true);

            $result = $method->invoke($otpService, $input);
            $this->assertEquals($expected, $result, "Failed for input: {$input}");
        }
    }
}
