<?php

namespace Janyk\Eventix\Models;

class Collection extends AbstractModel
{
    public function __construct(public string $model) {

    }

    public function getModelDefinition(): array
    {
        return [
            'items' => ['model' => $this->model, 'array' => true],
        ];
    }
}