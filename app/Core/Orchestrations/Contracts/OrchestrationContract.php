<?php

namespace App\Core\Orchestrations\Contracts;

use App\Core\Orchestrations\Contracts\Dto\StepResult;

interface OrchestrationContract
{
    /**
     * Provide a dashboard-friendly snapshot of the workflow.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function preview(array $filters): array;

    /**
     * Execute a workflow step and return the resulting status payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function executeStep(string $step, array $payload, ?string $idempotencyKey = null): StepResult;

    /**
     * Attempt to roll back an operation (best effort).
     *
     * @param  array<string, mixed>  $payload
     */
    public function rollbackStep(string $step, array $payload): StepResult;
}
