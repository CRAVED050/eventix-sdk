<?php

namespace Janyk\Eventix\Endpoints;

use Janyk\Eventix\Models\Collection;
use Janyk\Eventix\Models\Event;
use Janyk\Eventix\Types\RequestMethod;

class Events extends BaseEndpoint
{
    public function all(): Collection
    {
        return $this->client->request(RequestMethod::GET, 'events', new Collection(Event::class));
    }
}