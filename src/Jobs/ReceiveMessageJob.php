<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Jobs;

use AiluraCode\Wappify\Actions\ApplyStatusTransition;

use AiluraCode\Wappify\Actions\IngestInboundMessage;

use AiluraCode\Wappify\Data\IncomingMessageData;
use AiluraCode\Wappify\Data\WhatsappAccountConfig;
use AiluraCode\Wappify\Exceptions\UnknownMessageTypeException;
use AiluraCode\Wappify\Models\Whatsapp;
use AiluraCode\Wappify\Support\PayloadMapper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Netflie\WhatsAppCloudApi\Response\ResponseException;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use Throwable;
use UnexpectedValueException;

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
    private array $retryBackoff;

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

    /**
     * @throws CouldNotPerformTransition
     * @throws Throwable
     * @throws ResponseException
     * @throws UnknownMessageTypeException
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        try {
            $status = PayloadMapper::statusFromJson($this->payload);
            if ($status !== null) {
                $transition = App::make(ApplyStatusTransition::class, ['status' => $status]);
                if (! $transition instanceof ApplyStatusTransition) {
                    throw new UnexpectedValueException('Cannot resolve ApplyStatusTransition command.');
                }
                $transition();

                return;
            }

            $ingest = App::make(IngestInboundMessage::class, ['payload' => $this->payload, 'account' => $this->account]);
            if (! $ingest instanceof IngestInboundMessage) {
                throw new UnexpectedValueException('Cannot resolve IngestInboundMessage command.');
            }
            $ingest();
        } catch (Throwable $throwable) {
            Log::error('ReceiveMessageJob failed', ['account' => $this->account, 'exception' => $throwable]);

            throw $throwable;
        }
    }

    /**
     * Resolve a lost unique-constraint race as a successful duplicate.
     *
     * @internal Kept for the ingest path and its regression test; delegates to the command.
     *
     * @throws QueryException|BindingResolutionException When the failure is not a duplicate key.
     */
    public function resolveDuplicateWrite(QueryException $exception, string $wamid): Whatsapp
    {
        $ingest = App::make(IngestInboundMessage::class, ['payload' => $this->payload, 'account' => $this->account]);
        if (! $ingest instanceof IngestInboundMessage) {
            throw new UnexpectedValueException('Cannot resolve IngestInboundMessage command.');
        }

        return $ingest->resolveDuplicateWrite($exception, $wamid);
    }

    /**
     * @throws BindingResolutionException
     * @internal Kept for the ingest path and its regression test; delegates to the command.
     */
    public function storeMessage(IncomingMessageData $data): Whatsapp
    {
        $ingest = App::make(IngestInboundMessage::class, ['payload' => $this->payload, 'account' => $this->account]);
        if (! $ingest instanceof IngestInboundMessage) {
            throw new UnexpectedValueException('Cannot resolve IngestInboundMessage command.');
        }

        return $ingest->store($data);
    }

    public function uniqueId(): string
    {
        try {
            return PayloadMapper::fromJson($this->payload)->wamid;
        } catch (Throwable) {
            return sha1($this->payload);
        }
    }
}
