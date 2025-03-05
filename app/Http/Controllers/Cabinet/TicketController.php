<?php

namespace App\Http\Controllers\Cabinet;

use App\Enums\TicketStatusEnum;
use App\Exceptions\TicketAccessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\AttachTagsRequest;
use App\Http\Requests\Tickets\AttachUserRequest;
use App\Http\Requests\Tickets\CancelRequest;
use App\Http\Requests\Tickets\CompleteRequest;
use App\Http\Requests\Tickets\StoreCommentRequest;
use App\Http\Requests\Tickets\StoreRequest;
use App\Models\Department;
use App\Models\Mention;
use App\Models\Priorities;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function index()
    {
        $statusLabels = [];
        foreach (TicketStatusEnum::cases() as $status) {
            $statusLabels[$status->value] = $status->label();
        }

        $user = auth()->user();
        $departmentId = $user->getDepartmentId();

        $tickets = Ticket::query()
            ->with(['priority', 'creator', 'tags', 'performer', 'department'])
            ->where('department_id', $departmentId)
            ->where(function ($query) {
                $query->whereColumn('user_id', '!=', 'executor_id')
                    ->orWhereNull('executor_id'); // Добавляем условие для NULL
            })
            ->visibleToUser()
            ->latest()
            ->get();

        $priorities = Priorities::getCachedPriorities();
        $departments = Department::where('active', '=', true)->get();

        return view('cabinet.tickets.index', compact('tickets', 'priorities', 'statusLabels', 'departments'));
    }

    public function inbox(Request $request)
    {
        $statusLabels = [];
        foreach (TicketStatusEnum::cases() as $status) {
            $statusLabels[$status->value] = $status->label();
        }
        $priorities = Priorities::getCachedPriorities();
        $tickets = Ticket::query()
            ->with(['priority', 'creator', 'tags', 'performer'])
            ->where('executor_id', auth()->id())
            ->when($request->input('filter.status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->input('filter.creator'), function ($query, $creatorFilter) {
                if ($creatorFilter === 'own') {
                    $query->where('user_id', auth()->id())
                        ->where('executor_id', auth()->id());
                }
            })
            ->latest()
            ->get();

        return view('cabinet.tickets.inbox', compact('tickets', 'statusLabels', 'priorities'));
    }

    public function sent()
    {
        $statusLabels = [];
        foreach (TicketStatusEnum::cases() as $status) {
            $statusLabels[$status->value] = $status->label();
        }
        $priorities = Priorities::getCachedPriorities();
        $departments = Department::where('active', '=', true)->get();
        $tickets = Ticket::query()->with(['priority', 'creator', 'department', 'performer', 'comments'])
            ->where(function ($query) {
                $query->whereColumn('user_id', '!=', 'executor_id')
                    ->orWhereNull('executor_id'); // Добавляем условие для NULL
            })
            ->where('user_id', auth()->id())
            ->get();
        $openTickets = $tickets->where('status', TicketStatusEnum::OPENED);
        $inProgressTickets = $tickets->where('status', TicketStatusEnum::IN_PROGRESS);
        $doneTickets = $tickets->where('status', TicketStatusEnum::DONE);

        return view('cabinet.tickets.sent', compact(
            'priorities',
            'statusLabels',
            'departments',
            'openTickets',
            'inProgressTickets',
            'doneTickets',
            )
        );
    }

    public function show(Request $request, Ticket $ticket)
    {
        //auth()->loginUsingId(151);
//        abort_unless(
//            auth()->user()->getDepartmentId() === $ticket->department_id
//                    || auth()->id() === $ticket->creator->id,
//            403,
//            'Вы не можете просматривать тикеты другого отдела'
//        );

        abort_unless(
            auth()->user()->username === 'akarimov' //todo ВРЕМЕННО!!!
            || auth()->user()->getDepartmentId() === $ticket->department_id
            || auth()->id() === $ticket->creator->id,
            403,
            'Вы не можете просматривать тикеты другого отдела'
        );

        $backUrl = $request->query('back') ?? url()->previous();

        // Отмечаем упоминания как прочитанные
        Mention::query()
            ->whereHas('comment', function ($query) use ($ticket) {
                $query->where('ticket_id', $ticket->id);
            })
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $ticket = $ticket->load(['comments.creator', 'histories', 'tags']);

        $priorities = Priorities::getCachedPriorities();
        $departments = [];
        $comments = $ticket->comments;
        $histories = $ticket->histories;
        $departmentTags = auth()->user()->getDepartment()->tags;
        $activities = $comments->concat($histories)->sortBy('created_at');

        //$deptUsers = auth()->user()->deptAllUsers();
        $deptUsers = $ticket->getUsersByDepartment($ticket->department_id)
            ->filter(fn($user) => !str_contains($user->name, 'Aydin Karimov'));

        $mentions = $deptUsers->push($ticket->creator)->unique('id')->values();
        // Если $ticket->performer не равен null, добавляем его тоже
        if ($ticket->performer) {
            $mentions->push($ticket->performer);
        }
        $mentions = $mentions->map(function ($user) {
            return [
                'id' => $user->id,
                'key' => $user->name,
                'value' => $user->username,
            ];
        })->toJson();
        //dd($mentions);

        return view('cabinet.tickets.show', compact('ticket', 'departments', 'priorities', 'activities', 'departmentTags', 'mentions', 'backUrl'));
    }


    /**
     * @throws \Exception
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $ticket = $this->ticketService->createTicket($data);

        return response()->json(['status' => 'success', 'data' => $ticket], 201);
    }

    public function complete(CompleteRequest $request)
    {
        $data = $request->validated();
        $ticket = Ticket::findOrFail($data['ticket_id']);
        $this->ticketService->completeTicket($ticket, $data['completed_comment']);

        $html = view('components.tickets.ticket-status-badge', [
            'status' => $ticket->status->label(),
            'color' => $ticket->status->color()
        ])->render();

        return response()->json(['success' => true, 'data' => $ticket, 'html' => $html]);
    }

    public function cancel(CancelRequest $request)
    {
        $data = $request->validated();
        $ticket = Ticket::findOrFail($data['ticket_id']);

        try {
            $this->authorize('cancel', $ticket);
        } catch (AuthorizationException $e) {
            throw new AuthorizationException('У вас нет прав на отмену этого тикета!');
        }

        $this->ticketService->cancelTicket($ticket, $data['cancelled_comment']);

        return response()->json(['success' => true]);
    }

    public function inprogress(int $id)
    {
        $ticket = Ticket::findOrFail($id);
        $this->ticketService->updateTicketStatus($ticket, TicketStatusEnum::IN_PROGRESS);

        $html = view('components.tickets.ticket-status-badge', [
            'status' => $ticket->status->label(),
            'color' => $ticket->status->color()
        ])->render();

        $html2 =  view('components.tickets.ticket-performer-full', [
            'ticket' => $ticket,
            'user' => $ticket->performer,
        ])->render();

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'html' => $html,
            'html2' => $html2,
        ]);
    }

    public function close(Request $request, Ticket $ticket = null)
    {
        // Если тикет не передан через route model binding, пытаемся получить его из запроса
        if (!$ticket && $request->has('ticket_id')) {
            $ticket = Ticket::findOrFail($request->ticket_id);
        }

        // Проверяем, что тикет определен
        if (!$ticket) {
            abort(404, 'Тикет не найден');
        }

        try {
            $this->authorize('close', $ticket);
        } catch (AuthorizationException $e) {
            throw new AuthorizationException('У вас нет прав на закрытие этого тикета!');
        }

        // Проверяем, требуется ли рейтинг для закрытия тикета
        if ($ticket->requiresRating()) {
            $ratingData = $request->validate([
                'rating' => ['required', 'integer', 'between:1,5'],
                'comment' => ['required', 'string', 'min:3', 'max:1000'],
            ], [
                'rating.required' => trans('tickets.validations.comment.raiting'),
                'rating.integer' => 'Оценка должна быть целым числом.',
                'rating.between' => trans('tickets.validations.comment.raiting'),
                'comment.required' => trans('tickets.validations.comment.text_required'),
                'comment.string' => trans('tickets.validations.comment.text_string'),
                'comment.min' => trans('tickets.validations.comment.text_min'),
                'comment.max' => trans('tickets.validations.comment.text_max'),
            ]);

            $this->ticketService->closeTicket($ticket, $ratingData);
        } else {
            $this->ticketService->closeTicket($ticket);
        }

        return response()->json(['success' => true]);
    }

    public function storeComment(StoreCommentRequest $request, Ticket $ticket)
    {
        try {
            $this->authorize('comment', $ticket);
        } catch (AuthorizationException $e) {
            throw new AuthorizationException('У вас нет прав комментировать этот тикет!');
        }

        $data = $request->validated();
        $comment = $this->ticketService->addComment($ticket, $data);
        $comment->load('creator');
        return response()->json([
            'status' => 'success',
            'html' => view('components.tickets.ticket-comment', ['comment' => $comment])
                ->render()
        ]);
    }

    /**
     * @throws TicketAccessException
     */
    public function attachUsers(AttachUserRequest $request)
    {
        $data = $request->validated();
        $ticket = Ticket::find($data['ticket_id']);
        $performer = User::find($data['performer_id']);

        try {
            $this->authorize('assign', $ticket);
        } catch (AuthorizationException $e) {
            throw new AuthorizationException('У вас нет прав назначения сотрудника на тикет!');
        }

        $this->ticketService->attachUsers($ticket, $performer ?? null);

        return response()->json([
            'status' => 'success',
            'ticket_status' => view('components.tickets.ticket-status-badge', [
                'color' => $ticket->status->color(),
                'status' => $ticket->status->label(),
            ])->render(),
            'html' => view('components.tickets.ticket-performer-full', [
                'ticket' => $ticket,
                'user' => $ticket->performer,
            ])->render()
        ]);
    }

    public function getTicketPerformers(Ticket $ticket)
    {
        //$this->authorize('show', $ticket);

        return response()->json(['performer' => $ticket->performer]);
    }

    public function attachTags(AttachTagsRequest $request, Ticket $ticket)
    {
        $data = $request->validated();
        $this->ticketService->attachTags($ticket, $data);

        return response()->json([
            'status' => 'success',
        ]);
    }

}
