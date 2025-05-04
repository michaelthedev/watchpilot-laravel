<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class MediaRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            $fail('Media must be an array.');
            return;
        }

        // Validate 'title'
        if (!isset($value['title']) || !is_string($value['title'])) {
            $fail("{$attribute}.title must be a string and is required.");
        }

        // Validate '_id'
        if (!isset($value['_id']) || !is_numeric($value['_id'])) {
            $fail("{$attribute}._id must be a numeric value and is required.");
        }

        // Validate 'type'
        if (!isset($value['type']) || !in_array($value['type'], ['movie', 'tv-show'])) {
            $fail("{$attribute}.type must be either 'movie' or 'tv-show' and is required.");
        }

        // Validate 'poster' (nullable string)
        if (!isset($value['poster']) || !is_string($value['poster'])) {
            $fail("{$attribute}.poster must be a string.");
        }

        // Validate 'release_date' (nullable date)
        //@todo: Implement date validation for release_date
    }
}
