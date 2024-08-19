<?php

namespace Ars\Otp\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class OtpCodeCast implements CastsAttributes
{
    protected string $codeType;
    protected bool $encryptCode;

    public function __construct()
    {
        $this->codeType = config('otp-code.code_type', 'int');
        $this->encryptCode = config('otp-code.encrypt_code', false);
    }

    /**
     * Cast the given value from storage.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array<string, mixed>  $attributes
     * @return mixed
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        // Decrypt the value if encryption is enabled
        if ($this->encryptCode && $value !== null) {
            $value = Crypt::decrypt($value, false);
        }

        // Cast the value based on the configured type
        return match ($this->codeType) {
            'int', 'integer' => (int) $value,
            'string' => (string) $value,
        };
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array<string, mixed>  $attributes
     * @return mixed
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        // Encrypt the value if encryption is enabled
        if ($this->encryptCode && $value !== null) {
            return Crypt::encrypt($value, false);
        }

        return $value;
    }
}
