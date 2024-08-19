<?php

namespace Ars\Otp\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class OtpCodeCast implements CastsAttributes
{
    public string $codeType;
    public bool $encryptCode;

    public function __construct()
    {
        $this->codeType = config('otp-code.code_type');
        $this->encryptCode = config('otp-code.encrypt_code');
    }

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($this->encryptCode) {
            $value = Crypt::decrypt($value, false);
        }

        return match ($this->codeType) {
            'int', 'integer' => (int)$value,
            'string' => (string)$value,
        };
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($this->encryptCode) {
            return Crypt::encrypt($value, false);
        }

        return $value;
    }
}
