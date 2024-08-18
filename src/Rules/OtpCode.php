<?php

namespace Ars\Otp\Rules;

use Ars\Otp\Repositories\OtpRepository;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class OtpCode implements ValidationRule
{
    protected mixed $identifier;
    protected mixed $salt;

    /**
     * Create a new rule instance.
     *
     * @param mixed $identifier
     * @param mixed|null $salt
     */
    public function __construct(mixed $identifier, mixed $salt = null)
    {
        $this->identifier = $identifier;
        $this->salt = $salt;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $result = app(OtpRepository::class)->verify($this->identifier, $this->salt, $value);

        if (!$result) {
            $fail(__('otp_code::otp-code.invalid'))->translate();
        }
    }
}
