<?php

namespace App\Core\Orchestrations\Contracts\Dto;

final class StepResult
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>|array<string, string>  $errors
     */
    private function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly array $data = [],
        public readonly array $errors = [],
        public readonly ?string $nextStep = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function success(string $message, array $data = [], ?string $nextStep = null): self
    {
        return new self(true, $message, $data, [], $nextStep);
    }

    /**
     * @param  array<int, string>|array<string, string>  $errors
     */
    public static function failure(string $message, array $errors = []): self
    {
        return new self(false, $message, [], $errors);
    }
}
