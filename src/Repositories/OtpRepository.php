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
    protected bool $toArray;

    public function __construct()
    {
        $this->codeType = config('otp-code.code_type', 'integer');
        $this->codeLength = config('otp-code.code_length', 4);
        $this->maxAttempts = config('otp-code.max_attempts', 3);
        $this->expiryTime = Carbon::now()->addMinutes(config('otp-code.expiry_time', 2));
        $this->defaultSalt = config('otp-code.default_salt', '');
        $this->now = Carbon::now();
        $this->toArray = false;
    }

    /**
     * Create a new OTP code for the given identifier and salt.
     *
     * @param  string  $identifier
     * @param  string|null  $salt
     * @return array|object|null
     * @throws Exception
     */
    public function create(string $identifier, string $salt = null): array|object|null
    {
        $salt = $salt ?: $this->defaultSalt;

        // Generate OTP code
        $code = $this->generateCode();

        // Delete existing OTP codes for this identifier and salt
        $this->delete($identifier, $salt);

        // Create the new OTP code
        $otpCode = OtpCode::query()->create([
            'identifier' => $identifier,
            'salt'       => $salt,
            'code'       => $code,
            'expired_at' => $this->expiryTime,
        ]);

        return $this->return($otpCode);
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

        $otpCode = $query->orderBy('created_at', 'desc')->first();

        return $this->return($otpCode);
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
     * @param  int|string  $code
     * @param  string|null  $salt
     * @return bool
     */
    public function verify(string $identifier, int|string $code, string $salt = null): bool
    {
        $salt = $salt ?: $this->defaultSalt;
        $otp = $this->get($identifier, $salt);

        if (!$otp) {
            return false;
        }

        // Handle OTP as array or object
        $otpCode = is_array($otp) ? $otp['code'] : $otp->code;

        if ($otpCode != $code) {
            is_array($otp) ? OtpCode::query()->where('id', $otp['id'])->increment('attempts') : $otp->increment('attempts');
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

    /**
     * Return the OTP code as an array if required.
     *
     * @param object|null $otpCode
     * @return array|object|null
     */
    protected function return(object|null $otpCode): array|object|null
    {
        if ($this->toArray && $otpCode) {
            return $otpCode->toArray();
        }

        return $otpCode;
    }

    /**
     * Enable returning OTP code as an array.
     *
     * @param bool $toArray
     * @return $this
     */
    public function setToArray(bool $toArray): static
    {
        $this->toArray = $toArray;
        return $this;
    }
}