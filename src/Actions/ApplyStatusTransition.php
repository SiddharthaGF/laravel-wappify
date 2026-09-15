<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Actions;

use AiluraCode\Wappify\Data\StatusUpdatePayload;
use AiluraCode\Wappify\Models\Whatsapp;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

/**
 * Advance the lifecycle state of an existing row without inserting anything.
 *
 * Unknown wamids, unmapped statuses, and disallowed transitions (including
 * any transition out of the terminal Read state) are silently ignored.
 */
final class ApplyStatusTransition
{
    public function __construct(
        private StatusUpdatePayload $status,
    ) {}

    /**
     * @throws CouldNotPerformTransition
     */
    public function __invoke(): void
    {
        $message = Whatsapp::query()->where('wamid', $this->status->wamid)->first();

        if (! $message instanceof Whatsapp) {
            return;
        }

        if (! $message->state->canTransitionTo($this->status->status->value)) {
            return;
        }

        $message->state->transitionTo($this->status->status->value);
    }
}
