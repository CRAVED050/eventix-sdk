<?php

namespace Janyk\Eventix\Models;

abstract class AbstractModel
{
    /**
     * Returns the definition of the model.
     *
     * @return array [
     *           'model' => Janyk\Eventix\BaseModel,
     *           'array' => bool
     * ]
     */
    abstract public function getModelDefinition(): array;

    /**
     * Fills the fields in this model from an array. Any related models are also created.
     *
     * @param array $data Associative array with mapped field values.
     */
    public function fromArray(array $data): void
    {
        foreach ($this->getModelDefinition() as $field => $definition) {
            if(! isset($data->$field)) {
                continue;
            }
            if($definition['model'] == null) {
                $this->$field = $data->$field;
            }
            elseif($definition['array']) {
                $this->$field = array_map(function ($data) use ($definition) {
                    if ($data instanceof self) {
                        return $data;
                    }
                    else {
                        return $definition['model']::constructFromArray($data);
                    }
                }, $data->$field);
            }
            else {
                if ($data->$field instanceof AbstractModel) {
                    $this->$field = $data->$field;
                }
                else {
                    $this->$field = $definition['model']::constructFromArray($data->$field);
                }
            }
        }
    }

    /**
     * Returns an associative array with values of this model and related models.
     *
     * @param bool $omitNullValues  Whether to omit fields that have the value null.
     * @return array                Associative array with values of this model and related models.
     */
    public function toArray(bool $omitNullValues = true): array
    {
        $data = [];

        foreach ($this->getModelDefinition() as $field => $definition) {
            if ($omitNullValues && $this->$field === null) {
                continue;
            }
            if ($definition['model'] == null) {
                $data[$field] = $this->$field;
            }
            elseif ($definition['array']) {
                $data[$field] = array_map(function ($model) use ($omitNullValues) {
                    /** @var $model self */
                    return $model->toArray($omitNullValues);
                }, array_values($this->$field));
            }
            else {
                $data[$field] = $this->$field->toArray($omitNullValues);
            }
        }

        return $data;
    }
}
