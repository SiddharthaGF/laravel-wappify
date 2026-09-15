<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Http\Controllers;

use AiluraCode\Wappify\Actions\DeleteMessage;
use AiluraCode\Wappify\Models\Whatsapp;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;
use Throwable;

final class MessagesController extends Controller
{
    /**
     * @throws Throwable
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        $whatsapp = Whatsapp::find($id);
        if ($whatsapp === null) {
            return Response::json(['message' => 'Whatsapp not found'], 404);
        }

        Gate::authorize('delete-whatsapp', $whatsapp);

        $withMedia = (bool) ($request->get('withMedia'));

        try {
            $result = app(DeleteMessage::class, [
                'id' => $id,
                'withMedia' => $withMedia,
            ])();
        } catch (ModelNotFoundException) {
            return Response::json(['message' => 'Whatsapp not found'], 404);
        }

        return Response::json($result);
    }

    /**
     * Get all Whatsapp messages.
     *
     * @return LengthAwarePaginator<int, Whatsapp>
     */
    public function index(): LengthAwarePaginator
    {
        return Whatsapp::paginate(25);
    }

    public function show(string $id): JsonResponse|Whatsapp
    {
        $whatsapp = Whatsapp::find($id);
        if ($whatsapp === null) {
            return Response::json(['message' => 'Whatsapp not found'], 404);
        }

        return $whatsapp;
    }
}
