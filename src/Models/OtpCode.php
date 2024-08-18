<?php

namespace Ars\Otp\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasUuids;

    protected $castsCode;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('otp-code.table_name'));
        $this->setCastsCode();
    }

    protected $fillable = [
        'identifier',
        'salt',
        'code',
        'attempts',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    protected function setCastsCode()
    {
        $codeType = config('otp-code.code_type');

        if (config('otp-code.encrypt_code')) {
            $this->casts['code'] = 'encrypted:'.$codeType;
        } else {
            $this->casts['code'] = $codeType;
        }
    }
}
