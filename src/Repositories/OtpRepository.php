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

    protected int $expiryMinutes;

    protected string $defaultSalt;

    protected bool $toArray;

    public function __construct()
    {
        $this->codeType = config('otp-code.code_type', 'integer');
        $this->codeLength = (int) config('otp-code.code_length', 4);
        $this->maxAttempts = (int) config('otp-code.max_attempts', 3);
        $this->expiryMinutes = (int) config('otp-code.expiry_time', 2);
        $this->defaultSalt = (string) config('otp-code.default_salt', '');
        $this->toArray = false;
    }

    /**
     * Create a new OTP code for the given identifier and salt.
     *
     * @return array|object|null
     *
     * @throws Exception
     */
    public function create(string $identifier, ?string $salt = null): array|object|null
    {
        $salt = $salt ?: $this->defaultSalt;

        $code = $this->generateCode();

        $this->delete($identifier, $salt);

        $otpCode = OtpCode::query()->create([
            'identifier' => $identifier,
            'salt' => $salt,
            'code' => $code,
            'expired_at' => $this->expiresAt(),
        ]);

        return $this->return($otpCode);
    }

    /**
     * Retrieve the latest valid OTP code for the given identifier and salt.
     *
     * @return array|object|null
     */
    public function get(string $identifier, ?string $salt = null): array|object|null
    {
        $salt = $salt ?: $this->defaultSalt;

        $query = OtpCode::query()
            ->where('identifier', $identifier)
            ->where('salt', $salt)
            ->where('expired_at', '>=', Carbon::now());

        if ($this->maxAttempts > 0) {
            $query->where('attempts', '<', $this->maxAttempts);
        }

        $otpCode = $query->orderByDesc('created_at')->first();

        return $this->return($otpCode);
    }

    /**
     * Check if a valid OTP code exists for the given identifier and salt.
     */
    public function has(string $identifier, ?string $salt = null): bool
    {
        return (bool) $this->get($identifier, $salt);
    }

    /**
     * Verify the OTP code for the given identifier and salt.
     */
    public function verify(string $identifier, int|string $code, ?string $salt = null): bool
    {
        $salt = $salt ?: $this->defaultSalt;
        $otp = $this->get($identifier, $salt);

        if (! $otp) {
            return false;
        }

        $otpCode = is_array($otp) ? $otp['code'] : $otp->code;
        $normalized = $this->normalizeCode($code);

        $matches = in_array($this->codeType, ['int', 'integer'], true)
            ? (int) $otpCode === (int) $normalized
            : (string) $otpCode === (string) $normalized;

        if (! $matches) {
            is_array($otp)
                ? OtpCode::query()->where('id', $otp['id'])->increment('attempts')
                : $otp->increment('attempts');

            return false;
        }

        $this->delete($identifier, $salt);

        return true;
    }

    /**
     * Purge all OTP codes for the given identifier and salt.
     */
    public function delete(string $identifier, ?string $salt = null): int
    {
        $salt = $salt ?: $this->defaultSalt;

        return OtpCode::query()
            ->where('identifier', $identifier)
            ->where('salt', $salt)
            ->delete();
    }

    /**
     * Purge all expired OTP codes.
     */
    public function purgeExpiredCodes(): int
    {
        return OtpCode::query()
            ->where('expired_at', '<', Carbon::now())
            ->delete();
    }

    /**
     * Generate an OTP code based on the configured type and length.
     *
     * @throws Exception
     */
    protected function generateCode(): string|int
    {
        return match ($this->codeType) {
            'int', 'integer' => $this->generateRandomInteger($this->codeLength),
            'string' => strtoupper(Str::random($this->codeLength)),
            default => throw new Exception("Unsupported otp-code.code_type [{$this->codeType}]."),
        };
    }

    /**
     * Generate a random integer with exactly `$length` digits.
     *
     * @throws Exception
     */
    protected function generateRandomInteger(int $length): int
    {
        $length = max(1, $length);
        $min = 10 ** ($length - 1);
        $max = (10 ** $length) - 1;

        return random_int($min, $max);
    }

    /**
     * Convert Persian / Arabic-Indic digits to ASCII before compare.
     */
    protected function normalizeCode(int|string $code): string
    {
        return strtr(trim((string) $code), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }

    protected function expiresAt(): Carbon
    {
        return Carbon::now()->addMinutes($this->expiryMinutes);
    }

    /**
     * @return array|object|null
     */
    protected function return(?object $otpCode): array|object|null
    {
        if ($this->toArray && $otpCode) {
            return $otpCode->toArray();
        }

        return $otpCode;
    }

    public function setToArray(bool $toArray): static
    {
        $this->toArray = $toArray;

        return $this;
    }
}
