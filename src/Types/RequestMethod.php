<?php

namespace Janyk\Eventix\Types;

enum RequestMethod
{
    case GET;
    case POST;
    case PUT;
    case DELETE;
}