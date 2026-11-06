<?php

namespace App\Jobs;

use App\Http\Resources\IntegrationTicketResource;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;

/**
 * Сообщает внешней системе, что её тикет изменился. Отправляет текущее состояние тикета (а не разницу),
 * поэтому порядок и повторы не важны. Подпись: X-Tickets-Signature = sha256=HMAC(«время.тело», секрет).
 */
class SendIntegrationWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 6;

    public int $timeout = 30;

    /**
     * @param  list<string>  $changed  какие поля изменились
     */
    public function __construct(public int $ticketId, public array $changed = []) {}

    /**
     * Повторы: через 10 с, 1 мин, 5 мин, 15 мин, 1 ч. Если внешняя система так и не ответит, её сверка заберёт состояние сама.
     *
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 60, 300, 900, 3600];
    }

    public function handle(): void
    {
        $url = config('services.integration.webhook_url');
        $secret = (string) config('services.integration.webhook_secret');
        $ticket = Ticket::query()->with('performer')->find($this->ticketId);

        if (! $url || $secret === '' || $ticket === null || $ticket->external_system === null) {
            return;
        }

        $body = json_encode([
            'event' => 'ticket.updated',
            'changed' => $this->changed,
            'ticket' => (new IntegrationTicketResource($ticket))->resolve(),
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $timestamp = (string) now()->timestamp;

        Http::withBody($body, 'application/json')
            ->acceptJson()
            ->withHeaders([
                'X-Tickets-Timestamp' => $timestamp,
                'X-Tickets-Signature' => 'sha256='.hash_hmac('sha256', $timestamp.'.'.$body, $secret),
            ])
            ->timeout(15)
            ->post($url)
            ->throw();
    }
}
