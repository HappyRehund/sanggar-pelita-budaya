<?php

declare(strict_types=1);

class ValidationService
{
    private array $errors = [];

    public function reset(): void
    {
        $this->errors = [];
    }

    public function required(string $field, mixed $value): self
    {
        if ($value === null || $value === '' || $value === []) {
            $this->errors[$field] = ucfirst($field) . ' is required.';
        }
        return $this;
    }

    public function maxLength(string $field, string $value, int $max): self
    {
        if ($value !== '' && mb_strlen($value) > $max) {
            $this->errors[$field] = ucfirst($field) . " must not exceed {$max} characters.";
        }
        return $this;
    }

    public function inEnum(string $field, mixed $value, array $allowed): self
    {
        if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
            $this->errors[$field] = ucfirst($field) . ' has an invalid value.';
        }
        return $this;
    }

    public function url(string $field, ?string $value): self
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field] = ucfirst($field) . ' must be a valid URL.';
        }
        return $this;
    }

    public function optionalUrl(string $field, ?string $value): self
    {
        if ($value === null || $value === '') {
            return $this;
        }
        return $this->url($field, $value);
    }

    public function date(string $field, ?string $value): self
    {
        if ($value === null || $value === '') {
            return $this;
        }
        $d = DateTime::createFromFormat('Y-m-d', $value);
        if (!$d || $d->format('Y-m-d') !== $value) {
            $this->errors[$field] = ucfirst($field) . ' must be a valid date (YYYY-MM-DD).';
        }
        return $this;
    }

    public function slug(string $field, string $value): self
    {
        if ($value !== '' && !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value)) {
            $this->errors[$field] = ucfirst($field) . ' may only contain lowercase letters, numbers, and hyphens.';
        }
        return $this;
    }

    public function addError(string $field, string $message): self
    {
        $this->errors[$field] = $message;
        return $this;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function failOrContinue(): void
    {
        if ($this->hasErrors()) {
            validation_error_response($this->errors);
        }
    }
}