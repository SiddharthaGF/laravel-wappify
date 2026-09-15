<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Jobs;

use AiluraCode\Wappify\Data\IncomingMessageData;
use AiluraCode\Wappify\Data\StatusUpdatePayload;
use AiluraCode\Wappify\Data\WhatsappAccountConfig;
use AiluraCode\Wappify\Models\Whatsapp;
use AiluraCode\Wappify\Wappify;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use Throwable;

final class ReceiveMessageJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 5;

    public int $tries = 3;

    private string $account;

    private string $payload;

    /** @var array<int, int> */
    private array $retryBackoff = [1, 5, 15];

    public function __construct(string $payload, string $account = 'default')
    {
        $this->payload = $payload;
        $this->account = $account;

        $queue = WhatsappAccountConfig::fromConfig($this->account)->queue;
        $this->tries = $queue->tries;
        $this->timeout = $queue->timeout;
        $this->retryBackoff = $queue->backoff;
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return $this->retryBackoff;
    }

    public function handle(): void
    {
        try {
            $status = Wappify::payloadToStatus($this->payload);
            if ($status !== null) {
                $this->applyStatusTransition($status);

                return;
            }

            $whatsapp = $this->storeMessage(Wappify::payloadToModel($this->payload));
            whatsapp($this->account)->markMessageAsRead($whatsapp->getWamId());
            $canDownload = (bool) Config::get('wappify.download.automatic');
            if (! $canDownload) {
                return;
            }
            if ($whatsapp->isMedia()) {
                $queue = WhatsappAccountConfig::fromConfig($this->account)->queue;
                DownloadMediaJob::dispatch($whatsapp->id)
                    ->onQueue($queue->name)
                    ->onConnection($queue->connection);
            }
        } catch (Throwable $throwable) {
            Log::error('ReceiveMessageJob failed', ['account' => $this->account, 'exception' => $throwable]);

            throw $throwable;
        }
    }

    /**
     * Resolve a lost unique-constraint race as a successful duplicate.
     *
     * @internal Used by the ingest path and its regression test.
     *
     * @throws QueryException When the failure is not a duplicate key.
     */
    public function resolveDuplicateWrite(QueryException $exception, string $wamid): Whatsapp
    {
        if (! self::isDuplicateKey($exception)) {
            throw $exception;
        }

        $existing = Whatsapp::query()->where('wamid', $wamid)->first();

        if (! $existing instanceof Whatsapp) {
            throw $exception;
        }

        return $existing;
    }

    public function storeMessage(IncomingMessageData $data): Whatsapp
    {
        try {
            return Whatsapp::query()->firstOrCreate(
                ['wamid' => $data->wamid],
                [
                    'profile' => $data->profile,
                    'from' => $data->from,
                    'type' => $data->type->value,
                    'message' => (array) $data->message,
                    'timestamp' => $data->timestamp,
                ]
            );
        } catch (QueryException $exception) {
            return $this->resolveDuplicateWrite($exception, $data->wamid);
        }
    }

    public function uniqueId(): string
    {
        try {
            return Wappify::payloadToModel($this->payload)->wamid;
        } catch (Throwable) {
            return sha1($this->payload);
        }
    }

    private static function isDuplicateKey(QueryException $exception): bool
    {
        if ((string) $exception->getCode() === '23000') {
            return true;
        }

        $errorInfo = $exception->errorInfo;

        if (! is_array($errorInfo)) {
            return false;
        }

        $code = $errorInfo[0] ?? null;

        return is_scalar($code) && (string) $code === '23000';
    }

    /**
     * Apply a status transition to an existing record without persisting a new one.
     *
     * Unknown wamids, unmapped statuses, and disallowed transitions (including
     * any transition out of the terminal Read state) are ignored.
     *
     * @throws CouldNotPerformTransition
     */
    private function applyStatusTransition(StatusUpdatePayload $status): void
    {
        $message = Whatsapp::query()->where('wamid', $status->wamid)->first();

        if (! $message instanceof Whatsapp) {
            return;
        }

        if (! $message->state->canTransitionTo($status->status->value)) {
            return;
        }

        $message->state->transitionTo($status->status->value);
    }
}
