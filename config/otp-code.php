<?php

return [
    'table_name'   => 'otp_codes', // The name of the database table to store OTP codes
    'code_type'    => 'integer', // The data type of the OTP code (e.g., 'integer' for integer, 'string' for alphanumeric)
    'code_length'  => 4, // The length of the OTP code (e.g., 4 digits)
    'max_attempts' => 3, // The maximum number of attempts allowed to validate the OTP (set to 0 to disable attempt limits)
    'expiry_time'  => 2, // The time (in minutes) before the OTP expires
    'default_salt' => '', // The default salt value used for hashing or generating the OTP (optional)
    'encrypt_code' => false, // Whether to encrypt the OTP code before storing it in the database (true or false)
];