<?php

namespace App\Observers;

use App\Services\AuditTrailService;
use Illuminate\Database\Eloquent\Model;

class SensitiveActivityObserver
{
    public function __construct(
        private readonly AuditTrailService $auditTrailService,
    ) {
    }

    public function created(Model $model): void
    {
        $this->auditTrailService->logModelEvent(
            'created',
            $model,
            null,
            $model->getAttributes(),
        );
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();

        if ($changes === []) {
            return;
        }

        $oldValues = [];

        foreach (array_keys($changes) as $attribute) {
            $oldValues[$attribute] = $model->getOriginal($attribute);
        }

        $this->auditTrailService->logModelEvent(
            'updated',
            $model,
            $oldValues,
            $changes,
        );
    }

    public function deleted(Model $model): void
    {
        $this->auditTrailService->logModelEvent(
            'deleted',
            $model,
            $model->getOriginal(),
            null,
        );
    }
}
