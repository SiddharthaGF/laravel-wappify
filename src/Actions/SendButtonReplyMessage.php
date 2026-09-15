<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Actions;

use AiluraCode\Wappify\Data\MessageButtons;
use AiluraCode\Wappify\Models\Whatsapp;
use AiluraCode\Wappify\Support\PayloadMapper;
use Illuminate\Support\Facades\Log;
use Netflie\WhatsAppCloudApi\Message\ButtonReply\ButtonAction;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi as BaseTransport;
use Throwable;

/**
 * Send one button-reply message and persist exactly one row.
 *
 * Failures are logged once, then rethrown so tries/backoff can retry.
 */
final class SendButtonReplyMessage
{
    public function __construct(
        private string $to,
        private string $message,
        private MessageButtons $buttons,
        private string $account = 'default',
    ) {}

    public function __invoke(?BaseTransport $transport = null): Whatsapp
    {
        try {
            $transport ??= whatsapp($this->account);

            $response = $transport->sendButton(
                $this->to,
                $this->message,
                new ButtonAction($this->buttons->all())
            );

            $model = PayloadMapper::toModel(PayloadMapper::fromResponse($response, $this->account));
            $model->save();

            return $model;
        } catch (Throwable $throwable) {
            Log::error('SendButtonReplyMessage failed', ['account' => $this->account, 'exception' => $throwable]);

            throw $throwable;
        }
    }
}
