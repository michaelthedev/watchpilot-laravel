<?php

declare(strict_types=1);

namespace App\DTO;

abstract class BaseDTO
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    final public static function make(array $data): static
    {
        return new static(...array_values($data));
    }
}
