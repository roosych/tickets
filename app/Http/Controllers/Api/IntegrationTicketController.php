<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangeIntegrationTicketStatusRequest;
use App\Http\Requests\Api\StoreIntegrationTicketRequest;
use App\Http\Requests\Api\UpdateIntegrationTicketRequest;
use App\Http\Resources\IntegrationTicketResource;
use App\Models\Ticket;
use App\Services\Integration\IntegrationTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Тикеты для внешних систем. Технический пользователь видит и меняет только тикеты, которые сам создал через API.
 */
class IntegrationTicketController extends Controller
{
    /** Способность токена технического пользователя. */
    public const ABILITY = 'tickets:integration';

    public function __construct(private IntegrationTicketService $service) {}

    /**
     * Текущее состояние нескольких тикетов по номерам: внешняя система так сверяет статусы.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'max:200'],
            'ids.*' => ['integer'],
        ]);

        return IntegrationTicketResource::collection(
            Ticket::query()
                ->with('performer')
                ->whereNotNull('external_system')
                ->where('user_id', $request->user()->id)
                ->whereIn('id', $data['ids'])
                ->get()
        );
    }

    public function store(StoreIntegrationTicketRequest $request): JsonResponse
    {
        [$ticket, $created] = $this->service->create($request->validated(), $request->user());

        return (new IntegrationTicketResource($ticket->load('performer')))
            ->response()
            ->setStatusCode($created ? 201 : 200);
    }

    public function show(Request $request, Ticket $ticket): IntegrationTicketResource
    {
        $this->ensureOwn($request, $ticket);

        return new IntegrationTicketResource($ticket->load('performer'));
    }

    public function update(UpdateIntegrationTicketRequest $request, Ticket $ticket): IntegrationTicketResource
    {
        $this->ensureOwn($request, $ticket);

        return new IntegrationTicketResource($this->service->update($ticket, $request->validated(), $request->user())->load('performer'));
    }

    public function status(ChangeIntegrationTicketStatusRequest $request, Ticket $ticket): IntegrationTicketResource
    {
        $this->ensureOwn($request, $ticket);

        return new IntegrationTicketResource($this->service->changeStatus($ticket, $request->validated())->load('performer'));
    }

    /**
     * Чужие и созданные вручную тикеты для внешней системы как будто не существуют.
     */
    private function ensureOwn(Request $request, Ticket $ticket): void
    {
        abort_if($ticket->external_system === null || (int) $ticket->user_id !== (int) $request->user()->id, 404);
    }
}
