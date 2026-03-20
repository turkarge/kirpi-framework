<?php

declare(strict_types=1);

namespace Core\Validation;

use Core\Exception\ValidationException;

class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): array
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleString) {
            $fieldRules = is_array($ruleString)
                ? $ruleString
                : explode('|', $ruleString);

            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                $this->applyRule($field, $value, $rule, $data);
            }
        }

        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }

        return $this->only($data, array_keys($rules));
    }

    public function check(array $data, array $rules): bool
    {
        try {
            $this->validate($data, $rules);
            return true;
        } catch (ValidationException) {
            return false;
        }
    }

    public function errors(): array
    {
        return $this->errors;
    }

    // ─── Kural Uygula ────────────────────────────────────────

    private function applyRule(string $field, mixed $value, string $rule, array $data): void
    {
        // "min:8" → ['min', '8']
        [$ruleName, $param] = array_pad(explode(':', $rule, 2), 2, null);

        match($ruleName) {
            'required'       => $this->validateRequired($field, $value),
            'nullable'       => null, // Her zaman geçer
            'string'         => $this->validateString($field, $value),
            'numeric'        => $this->validateNumeric($field, $value),
            'integer'        => $this->validateInteger($field, $value),
            'boolean'        => $this->validateBoolean($field, $value),
            'array'          => $this->validateArray($field, $value),
            'email'          => $this->validateEmail($field, $value),
            'url'            => $this->validateUrl($field, $value),
            'min'            => $this->validateMin($field, $value, (int) $param),
            'max'            => $this->validateMax($field, $value, (int) $param),
            'between'        => $this->validateBetween($field, $value, $param),
            'size'           => $this->validateSize($field, $value, (int) $param),
            'in'             => $this->validateIn($field, $value, $param),
            'not_in'         => $this->validateNotIn($field, $value, $param),
            'confirmed'      => $this->validateConfirmed($field, $value, $data),
            'same'           => $this->validateSame($field, $value, $param, $data),
            'different'      => $this->validateDifferent($field, $value, $param, $data),
            'regex'          => $this->validateRegex($field, $value, $param),
            'alpha'          => $this->validateAlpha($field, $value),
            'alpha_num'      => $this->validateAlphaNum($field, $value),
            'alpha_dash'     => $this->validateAlphaDash($field, $value),
            'starts_with'    => $this->validateStartsWith($field, $value, $param),
            'ends_with'      => $this->validateEndsWith($field, $value, $param),
            'date'           => $this->validateDate($field, $value),
            'before'         => $this->validateBefore($field, $value, $param),
            'after'          => $this->validateAfter($field, $value, $param),
            'unique'         => $this->validateUnique($field, $value, $param),
            'exists'         => $this->validateExists($field, $value, $param),
            'required_if'    => $this->validateRequiredIf($field, $value, $param, $data),
            'required_with'  => $this->validateRequiredWith($field, $value, $param, $data),
            default          => null,
        };
    }

    // ─── Kurallar ────────────────────────────────────────────

    private function validateRequired(string $field, mixed $value): void
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, "The {$field} field is required.");
        }
    }

    private function validateString(string $field, mixed $value): void
    {
        if ($value !== null && !is_string($value)) {
            $this->addError($field, "The {$field} must be a string.");
        }
    }

    private function validateNumeric(string $field, mixed $value): void
    {
        if ($value !== null && !is_numeric($value)) {
            $this->addError($field, "The {$field} must be numeric.");
        }
    }

    private function validateInteger(string $field, mixed $value): void
    {
        if ($value !== null && filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, "The {$field} must be an integer.");
        }
    }

    private function validateBoolean(string $field, mixed $value): void
    {
        if ($value !== null && !in_array($value, [true, false, 0, 1, '0', '1'], true)) {
            $this->addError($field, "The {$field} must be true or false.");
        }
    }

    private function validateArray(string $field, mixed $value): void
    {
        if ($value !== null && !is_array($value)) {
            $this->addError($field, "The {$field} must be an array.");
        }
    }

    private function validateEmail(string $field, mixed $value): void
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "The {$field} must be a valid email address.");
        }
    }

    private function validateUrl(string $field, mixed $value): void
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, "The {$field} must be a valid URL.");
        }
    }

    private function validateMin(string $field, mixed $value, int $min): void
    {
        if ($value === null) return;

        if (is_string($value) && mb_strlen($value) < $min) {
            $this->addError($field, "The {$field} must be at least {$min} characters.");
            return;
        }

        if (is_numeric($value) && $value < $min) {
            $this->addError($field, "The {$field} must be at least {$min}.");
        }

        if (is_array($value) && count($value) < $min) {
            $this->addError($field, "The {$field} must have at least {$min} items.");
        }
    }

    private function validateMax(string $field, mixed $value, int $max): void
    {
        if ($value === null) return;

        if (is_string($value) && mb_strlen($value) > $max) {
            $this->addError($field, "The {$field} may not be greater than {$max} characters.");
            return;
        }

        if (is_numeric($value) && $value > $max) {
            $this->addError($field, "The {$field} may not be greater than {$max}.");
        }

        if (is_array($value) && count($value) > $max) {
            $this->addError($field, "The {$field} may not have more than {$max} items.");
        }
    }

    private function validateBetween(string $field, mixed $value, ?string $param): void
    {
        if ($value === null || $param === null) return;

        [$min, $max] = array_map('intval', explode(',', $param, 2));

        if (is_string($value)) {
            $length = mb_strlen($value);
            if ($length < $min || $length > $max) {
                $this->addError($field, "The {$field} must be between {$min} and {$max} characters.");
            }
        } elseif (is_numeric($value)) {
            if ($value < $min || $value > $max) {
                $this->addError($field, "The {$field} must be between {$min} and {$max}.");
            }
        }
    }

    private function validateSize(string $field, mixed $value, int $size): void
    {
        if ($value === null) return;

        if (is_string($value) && mb_strlen($value) !== $size) {
            $this->addError($field, "The {$field} must be {$size} characters.");
        }

        if (is_array($value) && count($value) !== $size) {
            $this->addError($field, "The {$field} must contain {$size} items.");
        }
    }

    private function validateIn(string $field, mixed $value, ?string $param): void
    {
        if ($value === null || $param === null) return;

        $allowed = explode(',', $param);

        if (!in_array($value, $allowed)) {
            $this->addError($field, "The selected {$field} is invalid.");
        }
    }

    private function validateNotIn(string $field, mixed $value, ?string $param): void
    {
        if ($value === null || $param === null) return;

        $forbidden = explode(',', $param);

        if (in_array($value, $forbidden)) {
            $this->addError($field, "The selected {$field} is invalid.");
        }
    }

    private function validateConfirmed(string $field, mixed $value, array $data): void
    {
        $confirmField = $field . '_confirmation';

        if (!isset($data[$confirmField]) || $value !== $data[$confirmField]) {
            $this->addError($field, "The {$field} confirmation does not match.");
        }
    }

    private function validateSame(string $field, mixed $value, ?string $other, array $data): void
    {
        if ($other === null) return;

        if ($value !== ($data[$other] ?? null)) {
            $this->addError($field, "The {$field} and {$other} must match.");
        }
    }

    private function validateDifferent(string $field, mixed $value, ?string $other, array $data): void
    {
        if ($other === null) return;

        if ($value === ($data[$other] ?? null)) {
            $this->addError($field, "The {$field} and {$other} must be different.");
        }
    }

    private function validateRegex(string $field, mixed $value, ?string $pattern): void
    {
        if ($value === null || $pattern === null) return;

        if (!preg_match($pattern, $value)) {
            $this->addError($field, "The {$field} format is invalid.");
        }
    }

    private function validateAlpha(string $field, mixed $value): void
    {
        if ($value !== null && !ctype_alpha($value)) {
            $this->addError($field, "The {$field} may only contain letters.");
        }
    }

    private function validateAlphaNum(string $field, mixed $value): void
    {
        if ($value !== null && !ctype_alnum($value)) {
            $this->addError($field, "The {$field} may only contain letters and numbers.");
        }
    }

    private function validateAlphaDash(string $field, mixed $value): void
    {
        if ($value !== null && !preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
            $this->addError($field, "The {$field} may only contain letters, numbers, dashes and underscores.");
        }
    }

    private function validateStartsWith(string $field, mixed $value, ?string $param): void
    {
        if ($value === null || $param === null) return;

        $prefixes = explode(',', $param);

        foreach ($prefixes as $prefix) {
            if (str_starts_with($value, $prefix)) return;
        }

        $this->addError($field, "The {$field} must start with one of: {$param}.");
    }

    private function validateEndsWith(string $field, mixed $value, ?string $param): void
    {
        if ($value === null || $param === null) return;

        $suffixes = explode(',', $param);

        foreach ($suffixes as $suffix) {
            if (str_ends_with($value, $suffix)) return;
        }

        $this->addError($field, "The {$field} must end with one of: {$param}.");
    }

    private function validateDate(string $field, mixed $value): void
    {
        if ($value !== null && strtotime($value) === false) {
            $this->addError($field, "The {$field} is not a valid date.");
        }
    }

    private function validateBefore(string $field, mixed $value, ?string $date): void
    {
        if ($value === null || $date === null) return;

        if (strtotime($value) >= strtotime($date)) {
            $this->addError($field, "The {$field} must be a date before {$date}.");
        }
    }

    private function validateAfter(string $field, mixed $value, ?string $date): void
    {
        if ($value === null || $date === null) return;

        if (strtotime($value) <= strtotime($date)) {
            $this->addError($field, "The {$field} must be a date after {$date}.");
        }
    }

    private function validateUnique(string $field, mixed $value, ?string $param): void
    {
        if ($value === null || $param === null) return;

        // Format: "table,column,exceptId"
        $parts  = explode(',', $param);
        $table  = $parts[0];
        $column = $parts[1] ?? $field;
        $except = $parts[2] ?? null;

        $query = app(\Core\Database\DatabaseManager::class)
            ->table($table)
            ->where($column, $value);

        if ($except !== null) {
            $query->where('id', '!=', $except);
        }

        if ($query->exists()) {
            $this->addError($field, "The {$field} has already been taken.");
        }
    }

    private function validateExists(string $field, mixed $value, ?string $param): void
    {
        if ($value === null || $param === null) return;

        $parts  = explode(',', $param);
        $table  = $parts[0];
        $column = $parts[1] ?? $field;

        $exists = app(\Core\Database\DatabaseManager::class)
            ->table($table)
            ->where($column, $value)
            ->exists();

        if (!$exists) {
            $this->addError($field, "The selected {$field} is invalid.");
        }
    }

    private function validateRequiredIf(string $field, mixed $value, ?string $param, array $data): void
    {
        if ($param === null) return;

        [$otherField, $otherValue] = array_pad(explode(',', $param, 2), 2, null);

        if (($data[$otherField] ?? null) == $otherValue) {
            $this->validateRequired($field, $value);
        }
    }

    private function validateRequiredWith(string $field, mixed $value, ?string $param, array $data): void
    {
        if ($param === null) return;

        $fields = explode(',', $param);

        foreach ($fields as $otherField) {
            if (!empty($data[$otherField])) {
                $this->validateRequired($field, $value);
                return;
            }
        }
    }

    // ─── Helpers ─────────────────────────────────────────────

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    private function only(array $data, array $keys): array
    {
        return array_intersect_key($data, array_flip($keys));
    }
}