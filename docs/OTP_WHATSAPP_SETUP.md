# OTP WhatsApp Integration & User Registration Setup

This document explains how to set up and configure the OTP (One-Time Password) system using WhatsApp via the WAMasbro service, along with the passwordless user registration system.

## Overview

The OTP system has been integrated with the existing WAMasbro service to send OTP codes via WhatsApp messages. This provides a more reliable and cost-effective alternative to SMS-based OTP.

The system also includes a user registration endpoint that automatically sends OTP codes via WhatsApp after successful registration, eliminating the need for password-based authentication.

### Key Benefits
- **Cost Effective**: WhatsApp messaging is typically cheaper than SMS
- **Higher Delivery Rate**: WhatsApp has better delivery success rates
- **User Friendly**: Users prefer WhatsApp over SMS
- **Secure**: OTP-based authentication is more secure than passwords
- **Scalable**: Easy to extend for additional features
- **Compliant**: Built with security and privacy best practices

### System Architecture
```
Frontend App → API Gateway → Laravel Backend → OTP Service → WAMasbro → WhatsApp
                                    ↓
                              Database (Users, OTP Codes)
```

## Configuration

### Environment Variables

Add the following environment variables to your `.env` file:

```env
# OTP Configuration
OTP_EXPIRATION_MINUTES=10
OTP_RATE_LIMIT_MINUTES=2
OTP_RESEND_RATE_LIMIT_MINUTES=2
OTP_CODE_LENGTH=6
OTP_ENABLE_WHATSAPP=true
OTP_ENABLE_SMS_FALLBACK=false
OTP_MAX_DAILY_ATTEMPTS=10
OTP_LOG_ACTIVITIES=true

# WhatsApp Masbro Configuration
WA_MASBRO_URL=https://your-wamasbro-instance.com
WA_MASBRO_KEY_ID=your_credential_id

# App Configuration
APP_NAME="Sesuai Sunnah"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sesuaisunnah
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Optional: Custom Message Templates
OTP_WHATSAPP_MESSAGE_TEMPLATE="🔐 *Kode OTP Anda*\n\nKode OTP Anda adalah: *{code}*\n\nKode ini berlaku selama {expiration} menit.\nJangan bagikan kode ini kepada siapapun.\n\nSalam,\nTim {app_name}"
OTP_SMS_MESSAGE_TEMPLATE="Kode OTP Anda adalah: {code}. Berlaku {expiration} menit. Jangan bagikan kode ini."
```

### Configuration Files

The system uses several configuration files for centralized settings:

- **`config/otp.php`**: OTP configuration (expiration, rate limits, templates)
- **`config/services.php`**: Third-party service configurations (WAMasbro)
- **`config/app.php`**: Application-level settings
- **`config/database.php`**: Database connection settings
- **`config/auth.php`**: Authentication configuration

You can modify these settings without changing the code.

### Configuration Validation
Validate your configuration:
```bash
# Check OTP configuration
php artisan config:show otp

# Check services configuration
php artisan config:show services

# Check all configurations
php artisan config:show

# Validate configuration files
php artisan config:cache
```

## Features

### 1. User Registration
- Passwordless registration for members
- Automatic OTP sending via WhatsApp after registration
- Comprehensive validation and data cleaning
- Duplicate prevention (phone and email)

### 2. WhatsApp OTP Delivery
- Automatically formats phone numbers for Indonesian WhatsApp format (62xxx)
- Sends formatted OTP messages via WhatsApp
- Handles delivery failures gracefully

### 2. Rate Limiting
- Prevents spam OTP requests
- Configurable time limits between requests
- Separate limits for resend operations

### 3. Security Features
- Daily attempt limits per phone number
- Automatic OTP expiration
- One-time use OTP codes
- Comprehensive logging

### 4. Phone Number Formatting
The system automatically converts various phone number formats to WhatsApp-compatible format for Indonesian numbers:

#### Input Formats Supported
- `081234567890` → `6281234567890`
- `+6281234567890` → `6281234567890`
- `6281234567890` → `6281234567890`
- `81234567890` → `6281234567890`
- `0812-3456-7890` → `6281234567890`
- `0812 3456 7890` → `6281234567890`
- `(0812) 3456-7890` → `6281234567890`

#### Formatting Rules
1. Remove all non-numeric characters (spaces, hyphens, parentheses)
2. If starts with `0`, replace with `62`
3. If starts with `+62`, remove the `+`
4. If doesn't start with `62`, add `62` prefix
5. Ensure final format is `62xxxxxxxxxx`

#### International Support
The system can be extended to support other country codes by modifying the `formatPhoneForWhatsApp` method in `OtpService`.

## API Endpoints

### Register User
```
POST /api/auth/register
{
    "name": "John Doe",
    "phone": "081234567890",
    "email": "john@example.com",
    "gender": "male"
}
```

**Response Success (201):**
```json
{
    "success": true,
    "message": "Registration successful! OTP has been sent to your WhatsApp for verification.",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "phone": "081234567890",
            "email": "john@example.com",
            "gender": "male"
        },
        "otp_info": {
            "phone": "081234567890",
            "expires_in": 600,
            "expires_at": "2025-01-20T10:30:00.000000Z",
            "delivery_method": "whatsapp"
        },
        "next_step": "Verify OTP using /api/auth/verify-otp endpoint to complete authentication."
    }
}
```

### Send OTP
```
POST /api/auth/send-otp
{
    "phone": "081234567890"
}
```

### Verify OTP
```
POST /api/auth/verify-otp
{
    "phone": "081234567890",
    "otp": "123456",
    "name": "John Doe",
    "email": "john@example.com",
    "gender": "male"
}
```

### Resend OTP
```
POST /api/auth/resend-otp
{
    "phone": "081234567890"
}
```

## Message Templates

### WhatsApp OTP Template

The WhatsApp message template can be customized via the `OTP_WHATSAPP_MESSAGE_TEMPLATE` environment variable. Default template:

```
🔐 *Kode OTP Anda*

Kode OTP Anda adalah: *{code}*

Kode ini berlaku selama {expiration} menit.
Jangan bagikan kode ini kepada siapapun.

Jika Anda tidak meminta kode ini, abaikan pesan ini.

Salam,
Tim Sesuai Sunnah
```

### SMS Fallback Template

If SMS fallback is enabled, you can customize the SMS template via `OTP_SMS_MESSAGE_TEMPLATE`:

```
Kode OTP Anda adalah: {code}. Berlaku {expiration} menit. Jangan bagikan kode ini.
```

### Template Variables

Available variables for customization:
- `{code}`: The generated OTP code
- `{expiration}`: OTP expiration time in minutes
- `{app_name}`: Application name (if configured)

## Error Handling

The system provides comprehensive error handling:

### HTTP Status Codes
- **200**: Success
- **201**: Created (Registration successful)
- **400**: Bad Request
- **401**: Invalid or expired OTP
- **409**: Conflict (User already exists)
- **422**: Validation Error
- **429**: Rate limit exceeded
- **500**: WhatsApp delivery failure
- **503**: WhatsApp service disabled

### Common Error Responses

**Validation Error (422):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "name": ["Nama wajib diisi."],
        "phone": ["Format nomor telepon tidak valid."],
        "email": ["Format email tidak valid."]
    }
}
```

**User Already Exists (409):**
```json
{
    "success": false,
    "message": "User with this phone number already exists."
}
```

**Rate Limit Exceeded (429):**
```json
{
    "success": false,
    "message": "OTP already sent. Please wait before requesting another."
}
```

**WhatsApp Service Error (500):**
```json
{
    "success": false,
    "message": "Failed to send OTP via WhatsApp. Please try again."
}
```

## Testing & Quality Assurance

### Running Tests

#### Individual Test Suites
```bash
# OTP WhatsApp functionality
php artisan test --filter=OtpWhatsAppTest

# User registration functionality
php artisan test --filter=UserRegistrationTest

# All authentication features
php artisan test --filter=Auth
```

#### Complete Test Suite
```bash
# Run all tests
php artisan test

# Run with coverage report (if Xdebug is enabled)
php artisan test --coverage

# Run tests in parallel (faster execution)
php artisan test --parallel
```

### Test Coverage
The comprehensive test suite covers:

#### OTP Functionality
- OTP generation and verification
- WhatsApp message delivery
- Rate limiting and expiration
- Error handling scenarios
- Phone number formatting

#### User Registration
- User registration with validation
- Duplicate prevention (phone/email)
- Data cleaning and formatting
- OTP integration
- Error handling

#### Security & Validation
- Input validation rules
- Data sanitization
- Unique constraints
- Rate limiting
- Authentication flow

### Test Data
Tests use isolated test databases and mock external services:
- WAMasbro service is mocked for testing
- OtpService is mocked for isolated testing
- Database is refreshed between tests
- No external API calls during testing

### Continuous Integration
Recommended CI/CD pipeline:
```yaml
# Example GitHub Actions workflow
- name: Run Tests
  run: |
    composer install --no-interaction --prefer-dist --optimize-autoloader
    php artisan test --parallel --coverage
```

## Monitoring & Analytics

### Logs
- OTP send attempts (success/failure)
- WhatsApp delivery status
- Rate limit violations
- Daily attempt limits
- User registration activities
- Validation failures
- Authentication attempts
- Error occurrences

### Database
- OTP codes with expiration tracking
- User accounts and profiles
- Usage statistics
- Phone number patterns
- Registration timestamps
- Failed attempts tracking
- User engagement metrics

### Key Metrics to Monitor
- OTP delivery success rate
- Registration completion rate
- Phone number format conversion success
- Rate limit violations frequency
- WhatsApp service availability
- User engagement patterns
- System response times
- Error rates by type

### Performance Indicators
- **Uptime**: System availability percentage
- **Response Time**: API endpoint response times
- **Throughput**: Requests per minute
- **Success Rate**: Successful operations percentage
- **Error Rate**: Error occurrences percentage
- **User Growth**: New registrations per day/week

## Troubleshooting

### Common Issues

1. **WhatsApp not sending messages**
   - Check WAMasbro service status
   - Verify credentials in `.env`
   - Check phone number format
   - Verify internet connectivity
   - Check WAMasbro service logs

2. **Rate limiting too strict**
   - Adjust `OTP_RATE_LIMIT_MINUTES` in config
   - Check daily attempt limits
   - Review resend rate limit settings
   - Monitor rate limit violations in logs

3. **Phone number validation errors**
   - Verify phone regex pattern
   - Check length constraints
   - Ensure proper country code format
   - Test phone number formatting manually

4. **Registration validation failures**
   - Check required field validation
   - Verify email format if provided
   - Ensure name contains only valid characters
   - Review validation error messages

5. **OTP verification failures**
   - Check OTP expiration time
   - Verify OTP code format
   - Check if OTP was already used
   - Verify database OTP records

6. **Database connection issues**
   - Check database credentials
   - Verify database server status
   - Check migration status
   - Review database logs

### Debug Mode

Enable debug mode to see detailed error messages:

```env
APP_DEBUG=true
```

### Environment Variables Check
Ensure all required environment variables are set:
```bash
# Check if OTP config is loaded
php artisan config:show otp

# Check if WAMasbro config is loaded
php artisan config:show services.wa-masbro

# Check all configs
php artisan config:show
```

### Log Analysis
Check application logs for detailed error information:
```bash
# View recent logs
tail -f storage/logs/laravel.log

# Search for OTP-related errors
grep -i "otp\|whatsapp" storage/logs/laravel.log

# Search for registration errors
grep -i "registration\|user" storage/logs/laravel.log
```

### Service Health Check
Verify service availability:
```bash
# Check WAMasbro service
curl -X GET "YOUR_WAMASBRO_URL/get-state?cred_id=YOUR_KEY_ID"

# Check database connection
php artisan tinker --execute="DB::connection()->getPdo();"

# Check queue status (if using queues)
php artisan queue:work --once
```

## Security Considerations

### OTP Security
1. **OTP Expiration**: Codes expire after 10 minutes by default
2. **Rate Limiting**: Prevents brute force attacks
3. **Daily Limits**: Prevents abuse from single phone numbers
4. **One-time Use**: Each OTP can only be used once
5. **Secure Generation**: Cryptographically secure random OTP codes

### Data Protection
6. **Input Validation**: Comprehensive validation prevents injection attacks
7. **Data Sanitization**: Automatic cleaning of user input
8. **Unique Constraints**: Prevents duplicate registrations
9. **Random Password Generation**: Secure random passwords for system accounts
10. **Audit Trail**: Complete logging of registration and OTP activities

### Privacy & Compliance
11. **Data Minimization**: Only collect necessary user information
12. **Secure Storage**: Encrypted storage of sensitive data
13. **Access Control**: Limited access to user data
14. **Data Retention**: Configurable data retention policies
15. **GDPR Compliance**: User data portability and deletion rights

## Future Enhancements

### Short Term (1-3 months)
1. **SMS Fallback**: Automatic fallback to SMS if WhatsApp fails
2. **Multi-language Support**: Support for different languages
3. **Advanced Analytics**: Detailed usage statistics and reporting
4. **Webhook Support**: Real-time delivery status updates
5. **Template Management**: Dynamic message template management

### Medium Term (3-6 months)
6. **User Profile Management**: Allow users to update their profiles
7. **Bulk OTP Sending**: Support for sending OTP to multiple users
8. **OTP History**: Track OTP usage patterns and statistics
9. **Advanced Rate Limiting**: IP-based and device-based rate limiting
10. **Integration APIs**: Webhook endpoints for external systems

### Long Term (6+ months)
11. **Admin Dashboard**: Management interface for OTP and user statistics
12. **Backup Authentication**: Alternative authentication methods
13. **Machine Learning**: Predictive analytics for OTP usage
14. **Multi-channel Support**: Email, push notifications, etc.
15. **Advanced Security**: Biometric authentication, 2FA
16. **Compliance Features**: GDPR, data retention policies
