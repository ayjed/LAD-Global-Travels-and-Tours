<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Reusable server-side input validator and sanitizer.
 * Supports rule chains: 'required|email|max:100', 'required|in:visa,tours,flights'.
 */
class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];
    private array $validated = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    /**
     * Statically validate and return a new validator instance.
     */
    public static function make(array $data, array $rules): self
    {
        $validator = new self($data, $rules);
        $validator->validate();
        return $validator;
    }

    /**
     * Execute validation across all registered rules.
     */
    public function validate(): bool
    {
        $this->errors = [];
        $this->validated = [];

        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            // Trim strings
            if (is_string($value)) {
                $value = trim($value);
            }

            foreach ($rules as $ruleDef) {
                $ruleParts = explode(':', $ruleDef, 2);
                $ruleName = $ruleParts[0];
                $ruleParam = $ruleParts[1] ?? null;

                // Required check
                if ($ruleName === 'required') {
                    if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                        $this->addError($field, "The {$this->formatFieldName($field)} field is required.");
                        break; // Stop further checks on empty required field
                    }
                }

                // If field is optional and empty, skip further format checks
                if (($value === null || $value === '') && $ruleName !== 'required') {
                    continue;
                }

                // Email validation
                if ($ruleName === 'email') {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->addError($field, "The {$this->formatFieldName($field)} must be a valid email address.");
                    }
                }

                // Numeric validation
                if ($ruleName === 'numeric') {
                    if (!is_numeric($value)) {
                        $this->addError($field, "The {$this->formatFieldName($field)} must be a valid number.");
                    }
                }

                // Check if field is declared numeric or raw type is numeric
                $isNumericField = in_array('numeric', $rules, true) || is_int($value) || is_float($value);

                // Minimum length or value
                if ($ruleName === 'min' && $ruleParam !== null) {
                    $min = (int)$ruleParam;
                    if ($isNumericField && is_numeric($value)) {
                        if ((float)$value < $min) {
                            $this->addError($field, "The {$this->formatFieldName($field)} must be at least {$min}.");
                        }
                    } elseif (mb_strlen((string)$value) < $min) {
                        $this->addError($field, "The {$this->formatFieldName($field)} must be at least {$min} characters.");
                    }
                }

                // Maximum length or value
                if ($ruleName === 'max' && $ruleParam !== null) {
                    $max = (int)$ruleParam;
                    if ($isNumericField && is_numeric($value)) {
                        if ((float)$value > $max) {
                            $this->addError($field, "The {$this->formatFieldName($field)} may not be greater than {$max}.");
                        }
                    } elseif (mb_strlen((string)$value) > $max) {
                        $this->addError($field, "The {$this->formatFieldName($field)} may not exceed {$max} characters.");
                    }
                }

                // In whitelist check
                if ($ruleName === 'in' && $ruleParam !== null) {
                    $allowed = explode(',', $ruleParam);
                    if (!in_array((string)$value, $allowed, true)) {
                        $this->addError($field, "The selected {$this->formatFieldName($field)} is invalid.");
                    }
                }

                // Phone format basic check
                if ($ruleName === 'phone') {
                    // Accepts standard digits, plus, hyphens, spaces, parentheses (7 to 20 chars)
                    if (!preg_match('/^[0-9+\-\s()]{7,20}$/', (string)$value)) {
                        $this->addError($field, "The {$this->formatFieldName($field)} must be a valid phone number.");
                    }
                }
            }

            // If no errors for this field, save sanitized value
            if (!isset($this->errors[$field])) {
                $this->validated[$field] = is_string($value) ? strip_tags($value) : $value;
            }
        }

        return empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Alias for getErrors()
     */
    public function errors(): array
    {
        return $this->errors;
    }

    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    public function getValidated(): array
    {
        return $this->validated;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    private function formatFieldName(string $field): string
    {
        return str_replace(['_', '-'], ' ', $field);
    }
}
