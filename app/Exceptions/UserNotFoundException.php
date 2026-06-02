<?php

namespace App\Exceptions;

use Exception;

class UserNotFoundException extends Exception
{
    protected $message = 'کاربری با این مشخصات یافت نشد.';
}
