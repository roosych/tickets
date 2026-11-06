<?php

namespace Tests\Feature;

use App\Events\TicketEvent;
use App\Http\Controllers\Api\IntegrationTicketController;
use App\Jobs\SendIntegrationWebhook;
use App\Jobs\SendTicketNotification;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

/**
 * API тикетов для внешних систем (ServiceRadar).
 * Тест сам переключается на SQLite в памяти, чтобы не трогать рабочую MySQL разработчика.
 */
class IntegrationTicketApiTest extends TestCase
{
    use RefreshDatabase;

    private int $departmentId;

    private int $priorityId;

    public function createApplication(): Application
    {
        foreach (['DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:'] as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $_SERVER[$key] = $value;
        }

        $app = require __DIR__.'/../../bootstrap/app.php';
        // Маршруты при загрузке читают активные языки из БД; в чистой тестовой базе таблицы ещё нет.
        $app->booting(fn (Application $app) => $app['cache']->put('languages', new EloquentCollection, 3600));
        $app->make(Kernel::class)->bootstrap();

        // RefreshDatabase стирает базу: если конфиг закеширован или переопределён и мы смотрим не в SQLite в памяти — стоп.
        $connection = $app['config']->get('database.default');
        if ($connection !== 'sqlite' || $app['config']->get("database.connections.{$connection}.database") !== ':memory:') {
            throw new RuntimeException('IntegrationTicketApiTest должен работать на SQLite в памяти, а подключение — '.$connection.'. Тест остановлен, чтобы не стереть базу.');
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([TicketEvent::class]);
        Queue::fake();

        $this->departmentId = DB::table('departments')->insertGetId(['name' => 'IT Department', 'active' => true]);
        $this->priorityId = DB::table('priorities')->insertGetId(['name' => json_encode(['ru' => 'Нормальный']), 'class' => 'primary', 'minutes' => 60]);
    }

    /** Пользователь как после синхронизации с AD (фабрика проекта не заполняет обязательный mailnickname). */
    private function user(array $attributes = []): User
    {
        $login = $attributes['username'] ?? fake()->unique()->userName();
        $user = User::factory()->make(['department_id' => $this->departmentId]);
        $user->forceFill(['username' => $login, 'mailnickname' => $login, 'active' => true, ...$attributes])->save();

        return $user;
    }

    private function robot(array $abilities = [IntegrationTicketController::ABILITY], string $name = 'Chuck Norris'): User
    {
        $robot = $this->user(['name' => $name]);
        Sanctum::actingAs($robot, $abilities);

        return $robot;
    }

    private function payload(array $overrides = []): array
    {
        return [
            'external_system' => 'serviceradar',
            'external_id' => 'plan:1:2026-11',
            'text' => "Чистка принтеров\nПротереть и проверить расходники",
            'department_id' => $this->departmentId,
            'priority_id' => $this->priorityId,
            ...$overrides,
        ];
    }

    public function test_requests_without_the_integration_token_are_rejected(): void
    {
        $this->postJson(route('api.integration.tickets.store'), $this->payload())->assertUnauthorized();

        $this->robot(['other']);
        $this->postJson(route('api.integration.tickets.store'), $this->payload())->assertForbidden();
    }

    public function test_parent_and_child_tickets_are_created_once_with_executor_and_deadline(): void
    {
        $robot = $this->robot();
        $executor = $this->user(['username' => 'syezhov']);

        $parent = $this->postJson(route('api.integration.tickets.store'), $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.status', 'opened')
            ->json('data');
        Event::assertDispatched(TicketEvent::class, fn (TicketEvent $event) => $event->action === 'created' && $event->ticket->id === $parent['id']);

        $this->postJson(route('api.integration.tickets.store'), $this->payload(['text' => 'повтор']))
            ->assertOk()
            ->assertJsonPath('data.id', $parent['id']);

        $child = $this->postJson(route('api.integration.tickets.store'), $this->payload([
            'external_id' => 'task:5',
            'parent_external_id' => 'plan:1:2026-11',
            'text' => 'Чистка принтеров — HP LaserJet',
            'due_date' => '2026-11-10T18:59:00+04:00',
            'executor' => ['guid' => 'нет такого', 'username' => 'syezhov'],
            'notify' => 'executor',
        ]))->assertCreated()->json('data');

        $this->assertSame($parent['id'], $child['parent_id']);
        $this->assertSame($executor->id, $child['executor']['id']);
        $this->assertSame('2026-11-10 18:59:00', Ticket::find($child['id'])->due_date->timezone('Asia/Baku')->format('Y-m-d H:i:s'));
        $this->assertSame($robot->id, Ticket::find($child['id'])->user_id);
        Event::assertDispatchedTimes(TicketEvent::class, 1);
        Queue::assertPushed(SendTicketNotification::class, 1);
    }

    public function test_deadline_status_and_scope_changes(): void
    {
        $robot = $this->robot();
        $ticket = Ticket::query()->create([
            'text' => 'Задание', 'user_id' => $robot->id, 'priorities_id' => $this->priorityId, 'department_id' => $this->departmentId,
            'status' => 'opened', 'external_system' => 'serviceradar', 'external_id' => 'task:7',
        ]);

        $this->patchJson(route('api.integration.tickets.update', $ticket), ['due_date' => '2026-11-12T18:59:00+04:00', 'comment' => 'Перенос'])
            ->assertOk();
        $this->assertDatabaseHas('ticket_histories', ['ticket_id' => $ticket->id, 'action' => 'update_deadline', 'comment' => 'Перенос']);

        $this->postJson(route('api.integration.tickets.status', $ticket), ['status' => 'completed'])->assertUnprocessable();
        $this->postJson(route('api.integration.tickets.status', $ticket), ['status' => 'done', 'comment' => 'Выполнено в ServiceRadar'])
            ->assertOk()
            ->assertJsonPath('data.status', 'done');

        $manual = Ticket::query()->create([
            'text' => 'Обычный тикет', 'user_id' => $robot->id, 'priorities_id' => $this->priorityId, 'department_id' => $this->departmentId, 'status' => 'opened',
        ]);
        $this->getJson(route('api.integration.tickets.show', $manual))->assertNotFound();
        $this->getJson(route('api.integration.tickets.index', ['ids' => [$ticket->id, $manual->id]]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'done');
        $this->postJson(route('api.integration.tickets.status', $manual), ['status' => 'canceled'])->assertNotFound();
    }

    private function ticket(User $creator, array $attributes = []): Ticket
    {
        return Ticket::query()->create([
            'text' => 'Тикет', 'user_id' => $creator->id, 'priorities_id' => $this->priorityId, 'department_id' => $this->departmentId,
            'status' => 'opened', 'external_system' => 'serviceradar', 'external_id' => fake()->unique()->uuid(), ...$attributes,
        ]);
    }

    public function test_robot_cannot_reach_or_attach_to_tickets_it_did_not_create(): void
    {
        $person = $this->user();
        $manual = $this->ticket($person, ['external_system' => null, 'external_id' => null]);
        $otherRobot = $this->user(['name' => 'Другая система']);
        $foreign = $this->ticket($otherRobot, ['external_id' => 'plan:9']);
        $this->robot();

        foreach ([$manual, $foreign] as $ticket) {
            $this->getJson(route('api.integration.tickets.show', $ticket))->assertNotFound();
            $this->patchJson(route('api.integration.tickets.update', $ticket), ['due_date' => '2026-11-12T18:59:00+04:00'])->assertNotFound();
            $this->postJson(route('api.integration.tickets.status', $ticket), ['status' => 'canceled'])->assertNotFound();
            $this->postJson(route('api.integration.tickets.store'), $this->payload(['external_id' => 'task:'.$ticket->id, 'parent_id' => $ticket->id]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('parent_id');
        }

        $this->getJson(route('api.integration.tickets.index', ['ids' => [$manual->id, $foreign->id]]))->assertOk()->assertJsonCount(0, 'data');
        $this->assertSame('opened', $foreign->fresh()->status->value);

        $child = $this->postJson(route('api.integration.tickets.store'), $this->payload(['external_id' => 'task:1', 'parent_external_id' => 'plan:9']))
            ->assertCreated()
            ->json('data');
        $this->assertNull($child['parent_id'], 'чужой тикет по external_id родителем не становится');
    }

    public function test_status_changes_respect_closed_tickets_and_set_the_performer(): void
    {
        $robot = $this->robot();
        $performer = $this->user(['username' => 'syezhov']);
        $inactive = $this->user(['username' => 'left', 'active' => false]);
        $open = $this->ticket($robot);
        $closed = $this->ticket($robot, ['status' => 'completed']);

        $this->postJson(route('api.integration.tickets.status', $closed), ['status' => 'canceled'])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->postJson(route('api.integration.tickets.status', $open), ['status' => 'done', 'performed_by' => ['username' => 'syezhov']])
            ->assertOk()
            ->assertJsonPath('data.status', 'done')
            ->assertJsonPath('data.executor.id', $performer->id);

        $this->patchJson(route('api.integration.tickets.update', $open), ['executor' => ['username' => 'left']])->assertOk();
        $this->assertSame($performer->id, $open->fresh()->executor_id, 'неактивного не назначаем');
        $this->assertNotNull($inactive->id);
    }

    public function test_executor_change_is_recorded_and_announced(): void
    {
        $robot = $this->robot();
        $executor = $this->user(['username' => 'syezhov']);
        $ticket = $this->ticket($robot);

        $this->patchJson(route('api.integration.tickets.update', $ticket), ['executor' => ['username' => 'syezhov']])
            ->assertOk()
            ->assertJsonPath('data.executor.id', $executor->id);

        $this->assertDatabaseHas('ticket_histories', ['ticket_id' => $ticket->id, 'action' => 'assign_user', 'assign_user' => $executor->id]);
        Event::assertDispatched(TicketEvent::class, fn (TicketEvent $event) => $event->action === 'assigned');
    }

    public function test_deadline_from_another_timezone_is_stored_in_baku_time(): void
    {
        $robot = $this->robot();
        $ticket = $this->ticket($robot);

        $this->patchJson(route('api.integration.tickets.update', $ticket), ['due_date' => '2026-11-12T14:59:00+00:00'])->assertOk();

        $this->assertSame('2026-11-12 18:59:00', DB::table('tickets')->where('id', $ticket->id)->value('due_date'));
    }

    public function test_integration_token_gets_a_higher_rate_limit(): void
    {
        $this->robot();

        for ($i = 0; $i < 70; $i++) {
            $this->getJson(route('api.integration.tickets.index', ['ids' => [1]]))->assertOk();
        }

        $this->robot(['other'], 'Обычный токен');
        for ($i = 0; $i < 60; $i++) {
            $this->getJson(route('api.integration.tickets.index', ['ids' => [1]]));
        }
        $this->getJson(route('api.integration.tickets.index', ['ids' => [1]]))->assertTooManyRequests();
    }

    public function test_robot_command_creates_the_user_once_and_rotates_its_token(): void
    {
        Storage::fake('public');

        $this->artisan('integration:robot')->assertSuccessful();
        $robot = User::query()->where('email', 'chuck.norris@serviceradar.local')->firstOrFail();
        $first = $robot->tokens()->sole();

        $this->assertSame('Chuck Norris', $robot->name);
        $this->assertSame($this->departmentId, $robot->department_id);
        $this->assertSame([IntegrationTicketController::ABILITY], $first->abilities);
        Storage::disk('public')->assertExists('images/users/chuck.norris.jpg');
        $this->assertSame('Тикеты не закрываются. Они сдаются.', $robot->contact_line, 'под именем подпись вместо почты');
        $this->assertSame('chuck.norris@serviceradar.local', $robot->email, 'почта-логин не меняется');
        $person = $this->user();
        $this->assertSame($person->email, $person->contact_line, 'у обычных пользователей — почта');
        $this->assertSame(Storage::url('images/users/chuck.norris.jpg'), $robot->avatar);

        $this->artisan('integration:robot')->assertSuccessful();

        $this->assertSame(1, User::query()->where('email', 'chuck.norris@serviceradar.local')->count());
        $this->assertNull($first->fresh(), 'старый токен отозван');
        $this->assertSame(1, $robot->tokens()->count());
    }

    public function test_changes_made_in_tickets_are_announced_to_the_external_system(): void
    {
        config(['services.integration.webhook_url' => 'https://serviceradar.test/api/webhooks/tickets', 'services.integration.webhook_secret' => 'hook-secret']);
        $robot = $this->user(['name' => 'Chuck Norris']);
        $manager = $this->user();
        $external = $this->ticket($robot);
        $manual = $this->ticket($manager, ['external_system' => null, 'external_id' => null]);

        $this->actingAs($manager);
        $external->update(['executor_id' => $manager->id]);
        $external->update(['text' => 'только текст']);
        $manual->update(['executor_id' => $manager->id]);

        Queue::assertPushed(SendIntegrationWebhook::class, 1);
        Queue::assertPushed(SendIntegrationWebhook::class, fn (SendIntegrationWebhook $job) => $job->ticketId === $external->id && $job->changed === ['executor_id']);
    }

    public function test_changes_coming_from_the_external_system_are_not_echoed_back(): void
    {
        config(['services.integration.webhook_url' => 'https://serviceradar.test/api/webhooks/tickets', 'services.integration.webhook_secret' => 'hook-secret']);
        $robot = $this->robot();
        $this->user(['username' => 'syezhov']);
        $ticket = $this->ticket($robot);

        $this->patchJson(route('api.integration.tickets.update', $ticket), ['due_date' => '2026-11-12T18:59:00+04:00', 'executor' => ['username' => 'syezhov']])->assertOk();
        $this->postJson(route('api.integration.tickets.status', $ticket), ['status' => 'done'])->assertOk();

        Queue::assertNotPushed(SendIntegrationWebhook::class);
    }

    public function test_webhook_sends_the_current_ticket_state_with_a_verifiable_signature(): void
    {
        config(['services.integration.webhook_url' => 'https://serviceradar.test/api/webhooks/tickets', 'services.integration.webhook_secret' => 'hook-secret']);
        Http::fake(['serviceradar.test/*' => Http::response(['result' => 'task'])]);
        $robot = $this->user(['name' => 'Chuck Norris']);
        $executor = $this->user(['username' => 'syezhov', 'guid' => 'guid-5']);
        $ticket = $this->ticket($robot, ['executor_id' => $executor->id, 'status' => 'in_progress', 'external_id' => 'task:5']);

        (new SendIntegrationWebhook($ticket->id, ['executor_id']))->handle();

        Http::assertSent(function (HttpRequest $request) use ($ticket): bool {
            $expected = 'sha256='.hash_hmac('sha256', $request->header('X-Tickets-Timestamp')[0].'.'.$request->body(), 'hook-secret');

            return $request->url() === 'https://serviceradar.test/api/webhooks/tickets'
                && hash_equals($expected, $request->header('X-Tickets-Signature')[0])
                && $request['ticket']['id'] === $ticket->id
                && $request['ticket']['status'] === 'in_progress'
                && $request['ticket']['executor']['guid'] === 'guid-5'
                && $request['ticket']['external_system'] === 'serviceradar'
                && $request['changed'] === ['executor_id'];
        });
    }

    public function test_webhook_failure_is_retried_by_the_queue(): void
    {
        config(['services.integration.webhook_url' => 'https://serviceradar.test/api/webhooks/tickets', 'services.integration.webhook_secret' => 'hook-secret']);
        Http::fake(['serviceradar.test/*' => Http::response(['result' => 'busy'], 503)]);
        $ticket = $this->ticket($this->user(['name' => 'Chuck Norris']));

        $this->expectException(RequestException::class);

        (new SendIntegrationWebhook($ticket->id))->handle();
    }
}
