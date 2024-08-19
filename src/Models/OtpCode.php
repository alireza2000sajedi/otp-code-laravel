<?php

namespace Ars\Otp\Models;

use Ars\Otp\Casts\OtpCodeCast;
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
    }

    protected $fillable = [
        'identifier',
        'salt',
        'code',
        'attempts',
        'expired_at',
    ];

    protected $casts = [
        'code'       => OtpCodeCast::class,
        'attempts'   => 'int',
        'expired_at' => 'datetime',
    ];

}
