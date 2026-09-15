<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Http\Controllers;

use AiluraCode\Wappify\Actions\EnqueueInboundPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class WebhookController extends Controller
{
    /**
     * Receive a WhatsApp message.
     */
    public function receive(Request $request, string $account = 'default'): JsonResponse
    {
        try {
            $acknowledgement = app(EnqueueInboundPayload::class, [
                'payload' => $request->getContent(),
                'account' => $account,
            ])();
        } catch (InvalidArgumentException) {
            return response()->json(['message' => "Account \"$account\" not found"], 404);
        }

        return response()->json($acknowledgement);
    }

    /**
     * Verify the webhook.
     */
    public function webhook(Request $request, string $account = 'default'): Response
    {
        try {
            $challenge = webhook($request, $account);
        } catch (InvalidArgumentException) {
            return response()->json(['message' => "Account \"$account\" not found"], 404);
        }

        return response($challenge, 200, ['Content-Type' => 'text/plain']);
    }
}
