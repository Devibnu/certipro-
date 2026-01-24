<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StateTransitionException extends Exception
{
    protected array $context = [];
    protected ?string $fromStatus = null;
    protected ?string $toStatus = null;

    /**
     * Create a new state transition exception
     */
    public function __construct(
        string $message,
        array $context = [],
        ?string $fromStatus = null,
        ?string $toStatus = null
    ) {
        parent::__construct($message);
        $this->context = $context;
        $this->fromStatus = $fromStatus;
        $this->toStatus = $toStatus;
    }

    /**
     * Static factory for transition errors
     */
    public static function invalidTransition(
        string $from,
        string $to,
        string $entityType = 'entity'
    ): self {
        return new self(
            "Tidak dapat mengubah status {$entityType} dari '{$from}' ke '{$to}'. Transisi tidak diizinkan.",
            ['entity_type' => $entityType],
            $from,
            $to
        );
    }

    /**
     * Static factory for missing requirement
     */
    public static function missingRequirement(string $requirement, string $action): self
    {
        return new self(
            "Tidak dapat {$action}: {$requirement}",
            ['requirement' => $requirement, 'action' => $action]
        );
    }

    /**
     * Static factory for locked entity
     */
    public static function entityLocked(string $entityType, string $reason = 'Data telah terkunci'): self
    {
        return new self(
            "Tidak dapat memodifikasi {$entityType}: {$reason}",
            ['entity_type' => $entityType, 'locked_reason' => $reason]
        );
    }

    /**
     * Static factory for duplicate entity
     */
    public static function duplicateEntity(string $entityType, string $identifier): self
    {
        return new self(
            "{$entityType} dengan identifier '{$identifier}' sudah ada",
            ['entity_type' => $entityType, 'identifier' => $identifier]
        );
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): Response|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error_type' => 'state_transition_error',
                'context' => $this->context,
                'from_status' => $this->fromStatus,
                'to_status' => $this->toStatus,
            ], 422);
        }

        return response()->view('errors.state-transition', [
            'message' => $this->getMessage(),
            'context' => $this->context,
            'fromStatus' => $this->fromStatus,
            'toStatus' => $this->toStatus,
        ], 422);
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        // Log for monitoring and debugging
        Log::warning('State transition blocked', [
            'message' => $this->getMessage(),
            'context' => $this->context,
            'from_status' => $this->fromStatus,
            'to_status' => $this->toStatus,
            'trace' => $this->getTraceAsString(),
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'url' => request()->fullUrl(),
        ]);
    }

    /**
     * Get the exception context
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get from status
     */
    public function getFromStatus(): ?string
    {
        return $this->fromStatus;
    }

    /**
     * Get to status
     */
    public function getToStatus(): ?string
    {
        return $this->toStatus;
    }

    /**
     * Check if this is a specific type of error
     */
    public function isLockError(): bool
    {
        return isset($this->context['locked_reason']);
    }

    /**
     * Check if this is a missing requirement error
     */
    public function isMissingRequirementError(): bool
    {
        return isset($this->context['requirement']);
    }

    /**
     * Check if this is a duplicate error
     */
    public function isDuplicateError(): bool
    {
        return isset($this->context['identifier']);
    }
}
