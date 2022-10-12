<?php

namespace Janyk\Eventix\Exceptions;

class RefreshTokenHasExpired extends \Exception
{
    public function __construct()
    {
        parent::__construct('The refresh token has expired.');
    }
}
