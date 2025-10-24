<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoFullWidthSpace implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Kiểm tra nếu có ký tự \x{3000} (khoảng trắng full-width)
        if (preg_match('/\x{3000}/u', $value)) {
            $fail(':attribute không được chứa ký tự khoảng trắng đặc biệt.');
        }
    }
}