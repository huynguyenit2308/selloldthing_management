<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoHTML implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Kiểm tra thẻ chứa HTML
        if ($value !== strip_tags($value)) {
            $fail(':attribute không được chứa mã HTML.');
        }

        // Kiểm tra nghi ngờ XSS không có thẻ HTML
        $dangerous = ['javascript:', 'onerror=', 'onload=', 'onclick='];
        foreach ($dangerous as $pattern) {
            if (stripos($value, $pattern) !== false) {
                $fail(':attribute chứa nội dung không an toàn.');
                return;
            }
        }
    }
}
