<?php

namespace Janyk\Eventix\Endpoints;

use Janyk\Eventix\Client;

abstract class BaseEndpoint
{
    public function __construct(public Client $client){ }
}