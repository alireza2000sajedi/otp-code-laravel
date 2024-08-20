
# OTP Code Laravel - One-Time Password In Laravel

## Introduction
This Laravel package offers a comprehensive solution for generating, storing, and verifying One-Time Passwords (OTPs). It supports customizable OTP configurations, including type, length, expiration, encryption, and validation rules, ensuring secure and flexible OTP management in your Laravel applications.

## Features
- Generate secure OTP codes with customizable types and lengths.
- Store and manage OTP codes with configurable expiration times.
- Optional OTP encryption for enhanced security.
- Validation rules to ensure OTP integrity.
- Seamless integration with Laravel applications.

## Requirements:

- Laravel Version: 10 or higher
- PHP Version: 8.0 or higher

## Installation
To install the package, follow these steps:

1. **Require the package via Composer**:
   ```bash
   composer require ars/laravel-otp-code
   ```

2. **Publish the configuration file and migration**:
   ```bash
   php artisan vendor:publish --provider="Ars\Otp\Providers\OtpCodeServiceProvider"
   ```

3. **Run the migration**:
   ```bash
   php artisan migrate
   ```

## Configuration
The configuration file `otp-code.php` includes the following options:

- **`table_name`**: The name of the database table to store OTP codes.
- **`code_type`**: The data type of the OTP code (`int` for integers or `string` for alphanumeric).
- **`code_length`**: The length of the OTP code.
- **`max_attempts`**: The maximum number of attempts allowed to validate the OTP (set `0` to disable attempts).
- **`expiry_time`**: The time (in minutes) before the OTP expires.
- **`encrypt_code`**: Whether to encrypt the OTP code before storing it.
- **`default_salt`**: The default salt value to be used if none is provided.

## Usage

### Repository

#### Creating an OTP
To create an OTP, use the `create` method:
```php
$otpRepository = new \Ars\Otp\Repositories\OtpRepository();
$otp = $otpRepository->create('alireza2000sajedi@gmail.com');
$code = $otp['code'];
```

#### Verifying an OTP
To verify an OTP, use the `verify` method:
```php
$isValid = $otpRepository->verify('alireza2000sajedi@gmail.com', 1234);
```

#### Checking OTP Existence
To check if a valid OTP exists, use the `has` method:
```php
$exists = $otpRepository->has('alireza2000sajedi@gmail.com');
```

### Facade
The package provides a facade for easy access to the OTP repository:
```php
use Ars\Otp\Facades\OtpCode;

// Create OTP
$otp = OtpCode::create('alireza2000sajedi@gmail.com');
$code = $otp['code'];
// Verify OTP
$isValid = OtpCode::verify('alireza2000sajedi@gmail.com', 1234);
```

### Custom Validation Rule
The package includes a validation rule to validate OTP codes:
```php
use Ars\Otp\Rules\OtpCode;

$request->validate([
    'email' => 'alireza2000sajedi@gmail.com',
    'otp_code' => ['required', new OtpCode($request->get('email'))],
]);
```

## Example
```php
use Ars\Otp\Facades\OtpCode;

    //If has salt send parameter after identifier
    public function sendOtp($email, $salt = null)
    {
        if (OtpCode::has($email, $salt)) {
            return Responder::setErrorCode(201)->setMessage(trans('otp_code::otp-code.already_send'))->respond();
        }

        $otp = OtpCode::create($email, $salt);

        Notification::send($email, new OtpNotification($otp['code']));
    }

```

## Customization

### Error Messages
You can customize the error messages by modifying the language files. Publish the language files using:
```bash
php artisan vendor:publish --tag=lang --provider="Ars\Otp\Providers\OtpCodeServiceProvider"
```

### Commands
The package includes an Artisan command to purge expired OTP codes:
```bash
php artisan otp:clear-expired
```

## Conclusion
This package provides a robust solution for managing OTP codes in a Laravel application. By leveraging the repository, facade, and validation rules, you can easily integrate OTP functionality into your project.

## Contributing
Contributions are welcome! Please adhere to the following guidelines:

1. Fork the repository.
2. Create a feature branch (git checkout -b feature/my-new-feature).
3. Commit your changes (git commit -am 'Add new feature').
4. Push to the branch (git push origin feature/my-new-feature).
5. Open a Pull Request.

## License
This library is licensed under the MIT License.

## Contact
For support or questions, please open an issue on GitHub.
