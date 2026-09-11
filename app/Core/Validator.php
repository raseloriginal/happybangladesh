<?php
class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data) { $this->data = $data; }

    public function required(string $field, string $label = ''): static
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (empty(trim((string)($this->data[$field] ?? '')))) {
            $this->errors[$field] = "{$label} is required.";
        }
        return $this;
    }

    public function email(string $field): static
    {
        $val = $this->data[$field] ?? '';
        if ($val && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Please enter a valid email address.';
        }
        return $this;
    }

    public function phone(string $field): static
    {
        $val = $this->data[$field] ?? '';
        if ($val && !preg_match('/^[0-9+\-\s()]{7,20}$/', $val)) {
            $this->errors[$field] = 'Please enter a valid phone number.';
        }
        return $this;
    }

    public function numeric(string $field, float $min = 0, ?float $max = null): static
    {
        $val = $this->data[$field] ?? '';
        if ($val !== '' && !is_numeric($val)) {
            $this->errors[$field] = 'Must be a number.';
        } elseif (is_numeric($val) && (float)$val < $min) {
            $this->errors[$field] = "Must be at least {$min}.";
        } elseif ($max !== null && is_numeric($val) && (float)$val > $max) {
            $this->errors[$field] = "Must not exceed {$max}.";
        }
        return $this;
    }

    public function minLength(string $field, int $min): static
    {
        $val = $this->data[$field] ?? '';
        if (mb_strlen((string)$val) < $min) {
            $this->errors[$field] = "Must be at least {$min} characters.";
        }
        return $this;
    }

    public function date(string $field): static
    {
        $val = $this->data[$field] ?? '';
        if ($val && !\DateTime::createFromFormat('Y-m-d', $val)) {
            $this->errors[$field] = 'Invalid date format (YYYY-MM-DD expected).';
        }
        return $this;
    }

    public function passes(): bool { return empty($this->errors); }
    public function fails(): bool { return !empty($this->errors); }
    public function errors(): array { return $this->errors; }
    public function firstError(): string { return reset($this->errors) ?: ''; }
}
