<?php

declare(strict_types=1);

namespace Rockbuzz\LaraOrders\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasSchemalessAttributes
{
    public static function create(array $attributes = []): Model
    {
        return tap(new static, function ($model) use ($attributes) {
            $model->resolveAttributes($attributes)
                    ->save();
        });
    }

    public function update(array $attributes = [], array $options = []): bool
    {
        return $this->resolveAttributes($attributes)
                    ->save($options);
    }

    protected function resolveAttributes(array $attributes): Model
    {
        foreach ($attributes as $attribute => $value) {
            if ($lenght = strpos($attribute, '.')) {
                $schemalessAttribute = substr($attribute, 0, $lenght);
                $attribute = str_replace("{$schemalessAttribute}.", '', $attribute);
                $this->{$schemalessAttribute}->set($attribute, $value);
                continue;
            }
            $this->{$attribute} = $value;
        }
        return $this;
    }
}
