<?php

namespace Ars\Otp\Repositories;

use Ars\Otp\Models\OtpCode;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;

class OtpRepository
{
    protected string $codeType;
    protected int $codeLength;
    protected int $maxAttempts;
    protected Carbon $expiryTime;
    protected string $defaultSalt;
    protected Carbon $now;

    public function __construct()
    {
        $this->codeType = config('otp-code.code_type');
        $this->codeLength = config('otp-code.code_length');
        $this->maxAttempts = config('otp-code.max_attempts');
        $this->expiryTime = Carbon::now()->addMinutes(config('otp-code.expiry_time'));
        $this->defaultSalt = config('otp-code.default_salt', '');
        $this->now = Carbon::now();
    }

    /**
     * Create a new OTP code for the given identifier and salt.
     *
     * @param  string  $identifier
     * @param  string|null  $salt
     * @return array
     * @throws Exception
     */
    public function create(string $identifier, string $salt = null): array
    {
        $salt = $salt ?: $this->defaultSalt;

        // Generate OTP code
        $code = $this->generateCode();

        // Delete existing OTP codes for this identifier and salt
        $this->delete($identifier, $salt);

        // Create and return the new OTP code
        return OtpCode::query()->create([
            'identifier' => $identifier,
            'salt'       => $salt,
            'code'       => $code,
            'expired_at' => $this->expiryTime,
        ])->toArray();
    }

    /**
     * Retrieve the latest valid OTP code for the given identifier and salt.
     *
     * @param  string  $identifier
     * @param  string|null  $salt
     * @return array|object|null
     */
    public function get(string $identifier, string $salt = null): array|object|null
    {
        $salt = $salt ?: $this->defaultSalt;

        $query = OtpCode::query()
            ->where('identifier', $identifier)
            ->where('salt', $salt)
            ->where('expired_at', '>=', $this->now);

        if ($this->maxAttempts > 0) {
            $query->where('attempts', '<', $this->maxAttempts);
        }

        return $query->orderBy('created_at', 'desc')->first();
    }

    /**
     * Check if a valid OTP code exists for the given identifier and salt.
     *
     * @param  string  $identifier
     * @param  string|null  $salt
     * @return bool
     */
    public function has(string $identifier, string $salt = null): bool
    {
        return (bool) $this->get($identifier, $salt);
    }

    /**
     * Verify the OTP code for the given identifier and salt.
     *
     * @param  string  $identifier
     * @param  string|null  $salt
     * @param  int  $code
     * @return bool
     */
    public function verify(string $identifier, int|string $code, string $salt = null): bool
    {
        $salt = $salt ?: $this->defaultSalt;
        $otp = $this->get($identifier, $salt);

        if (!$otp || $otp->code != $code) {
            $otp?->increment('attempts');
            return false;
        }

        $this->delete($identifier, $salt);

        return true;
    }

    /**
     * Purge all OTP codes for the given identifier and salt.
     *
     * @param  string  $identifier
     * @param  string|null  $salt
     * @return int
     */
    public function delete(string $identifier, string $salt = null): int
    {
        $salt = $salt ?: $this->defaultSalt;

        return OtpCode::query()
            ->where('identifier', $identifier)
            ->where('salt', $salt)
            ->delete();
    }

    /**
     * Purge all expired OTP codes.
     *
     * @return int
     */
    public function purgeExpiredCodes(): int
    {
        return OtpCode::query()
            ->where('expired_at', '<', $this->now)
            ->delete();
    }

    /**
     * Generate an OTP code based on the configured type and length.
     *
     * @return string|int
     * @throws Exception
     */
    protected function generateCode(): string|int
    {
        return match ($this->codeType) {
            'int', 'integer' => $this->generateRandomInteger($this->codeLength),
            'string' => strtoupper(Str::random($this->codeLength)),
        };
    }

    /**
     * Generate a random integer of the specified length.
     *
     * @param  int  $length
     * @return int
     * @throws Exception
     */
    protected function generateRandomInteger(int $length): int
    {
        $min = (int) str_pad('1', $length, '0') - 1;
        $max = (int) str_pad('9', $length, '9');

        return random_int($min, $max);
    }
}
