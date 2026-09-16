<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Http\Controllers;

use AiluraCode\Wappify\Actions\EnqueueInboundPayload;

use AiluraCode\Wappify\Actions\VerifyWebhookChallenge;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use UnexpectedValueException;

final class WebhookController extends Controller
{
    /**
     * Receive a WhatsApp message.
     *
     * @throws BindingResolutionException
     */
    public function receive(Request $request, string $account = 'default'): JsonResponse
    {
        try {
            $enqueue = App::make(EnqueueInboundPayload::class, [
                'payload' => $request->getContent(),
                'account' => $account,
            ]);
            if (! $enqueue instanceof EnqueueInboundPayload) {
                throw new UnexpectedValueException('Cannot resolve EnqueueInboundPayload command.');
            }
            $acknowledgement = $enqueue();
        } catch (InvalidArgumentException) {
            return Response::json(['message' => "Account \"$account\" not found"], 404);
        }

        return Response::json($acknowledgement);
    }

    /**
     * Verify the webhook.
     */
    public function webhook(Request $request, string $account = 'default'): SymfonyResponse
    {
        try {
            $verify = App::make(VerifyWebhookChallenge::class, [
                'query' => $request->query->all(),
                'account' => $account,
            ]);
            if (! $verify instanceof VerifyWebhookChallenge) {
                throw new UnexpectedValueException('Cannot resolve VerifyWebhookChallenge command.');
            }
            $challenge = $verify();
        } catch (InvalidArgumentException) {
            return Response::json(['message' => "Account \"$account\" not found"], 404);
        }

        return Response::make($challenge, 200, ['Content-Type' => 'text/plain']);
    }
}
