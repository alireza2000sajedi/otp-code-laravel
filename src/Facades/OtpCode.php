<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool verify(string $identifier, int $code, string $salt = null)
 * @method static array create(string $identifier, string $salt = null)
 * @method static int delete(string $identifier, string $salt = null)
 * @method static array|object|null get(string $identifier, string $salt = null)
 * @method static bool has(string $identifier, string $salt = null)
 * @method static int purgeExpiredCodes()
 */
class OtpCode extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'otp-code'; // This should match the key used in the service provider
    }
}
