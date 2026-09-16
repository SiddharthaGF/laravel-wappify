<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Actions;

use AiluraCode\Wappify\Models\Whatsapp;
use AiluraCode\Wappify\Support\PayloadMapper;
use AiluraCode\Wappify\WhatsAppCloudApi;
use Exception;
use Netflie\WhatsAppCloudApi\Response\ResponseException;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi as BaseTransport;

/**
 * Send one text message and persist exactly one row.
 *
 * The command owns persistence: the transport MUST NOT self-save.
 */
final class SendTextMessage
{
    public function __construct(
        private readonly string $to,
        private readonly string $text,
        private readonly string $account = 'default',
    ) {}

    /**
     * @throws ResponseException|Exception
     */
    public function __invoke(?BaseTransport $transport = null): Whatsapp
    {
        $transport ??= WhatsAppCloudApi::forAccount($this->account);

        $response = $transport->sendTextMessage($this->to, $this->text);

        $model = PayloadMapper::toModel(PayloadMapper::fromResponse($response, $this->account));
        $model->save();

        return $model;
    }
}
