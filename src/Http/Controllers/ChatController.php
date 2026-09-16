<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Http\Controllers;

use AiluraCode\Wappify\Models\Whatsapp;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;

final class ChatController extends Controller
{
    /**
     * @return LengthAwarePaginator<int, Whatsapp>
     */
    public function chat(string $from): LengthAwarePaginator
    {
        return Whatsapp::chat($from)->paginate(25);
    }

    /**
     * @return LengthAwarePaginator<int, Whatsapp>
     */
    public function me(): LengthAwarePaginator
    {
        return Whatsapp::me()->paginate(25);
    }

    /**
     * @return LengthAwarePaginator<int, Whatsapp>
     */
    public function you(): LengthAwarePaginator
    {
        return Whatsapp::you()->paginate(25);
    }
}
