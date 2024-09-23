<?php

namespace App\Http\Controllers\Cabinet;

use App\Enums\TicketStatusEnum;
use App\Exceptions\TicketAccessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\AttachTagsRequest;
use App\Http\Requests\Tickets\AttachUserRequest;
use App\Http\Requests\Tickets\CompleteRequest;
use App\Http\Requests\Tickets\StoreCommentRequest;
use App\Http\Requests\Tickets\StoreRequest;
use App\Models\Department;
use App\Models\Media;
use App\Models\Priorities;
use App\Models\TemporaryFile;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCreatedNotification;
use App\Services\TicketService;
use Illuminate\Support\Facades\Storage;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

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

        $tickets = Ticket::query()
            ->with('priority', 'creator')
            ->whereNot('status', TicketStatusEnum::CLOSED)
            ->where('department_id', $user->getDepartmentId())
            ->latest()
            ->get();

        $priorities = Priorities::getCachedPriorities();
        //$departments = Department::all();
        $departments = Department::where('id', '=', 17)->get();

        return view('cabinet.tickets.index', compact('tickets', 'priorities', 'statusLabels', 'departments'));
    }

    public function sent()
    {
        $statusLabels = [];
        foreach (TicketStatusEnum::cases() as $status) {
            $statusLabels[$status->value] = $status->label();
        }
        $priorities = Priorities::getCachedPriorities();
        //$departments = Department::all();
        $departments = Department::where('id', '=', 17)->get();
        $tickets = Ticket::query()->with('performers', 'department', 'comments')
            ->where('user_id', auth()->id())
            ->get();
        $openTickets = $tickets->where('status', TicketStatusEnum::OPENED);
        $inProgressTickets = $tickets->where('status', TicketStatusEnum::IN_PROGRESS);
        $completedTickets = $tickets->where('status', TicketStatusEnum::COMPLETED);

        return view('cabinet.tickets.sent', compact(
            'priorities',
            'statusLabels',
            'departments',
            'openTickets',
            'inProgressTickets',
            'completedTickets',
            )
        );
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('show', $ticket);

        $ticket = $ticket->load(['comments.creator', 'histories', 'tags']);

        $comments = $ticket->comments;
        $histories = $ticket->histories;
        $departmentTags = auth()->user()->getDepartment()->tags;
        $activities = $comments->concat($histories)->sortBy('created_at');

        return view('cabinet.tickets.show', compact('ticket', 'activities', 'departmentTags'));
    }


    /**
     * @throws \Exception
     */
    public function store(StoreRequest $request)
    {
        $this->authorize('create', Ticket::class);

        $data = $request->validated();
        $ticket = $this->ticketService->createTicket($data);

        return response()->json(['status' => 'success', 'data' => $ticket], 201);
    }

    /**
     * @throws TicketAccessException
     */
    public function complete(CompleteRequest $request)
    {
        $data = $request->validated();
        $ticket = Ticket::findOrFail($data['ticket_id']);
        $this->ticketService->updateTicketStatus($ticket, TicketStatusEnum::COMPLETED, $data['completed_comment']);

        $html = view('components.tickets.ticket-status-badge', [
            'status' => $ticket->status->label(),
            'color' => $ticket->status->color()
        ])->render();

        return response()->json(['success' => true, 'data' => $ticket, 'html' => $html]);
    }

    /**
     * @throws TicketAccessException
     */
    public function inprogress(int $id)
    {
        $ticket = Ticket::findOrFail($id);
        $this->authorize('show', $ticket);

        $this->ticketService->updateTicketStatus($ticket, TicketStatusEnum::IN_PROGRESS);

        $html = view('components.tickets.ticket-status-badge', [
            'status' => $ticket->status->label(),
            'color' => $ticket->status->color()
        ])->render();

        $html2 =  view('components.tickets.ticket-performer', [
            'ticket' => $ticket,
            'users' => $ticket->performers,
        ])->render();

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'html' => $html,
            'html2' => $html2,
        ]);
    }

    /**
     * @throws TicketAccessException
     */
    public function close(Ticket $ticket)
    {
        $this->ticketService->closeTicket($ticket);
        return response()->json(['success' => true,]);
    }

    /**
     * @throws TicketAccessException
     */
    public function storeComment(StoreCommentRequest $request, Ticket $ticket)
    {
        $this->authorize('show', $ticket);

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

        $this->authorize('show', $ticket);

        $this->ticketService->attachUsers($ticket, $data['users'] ?? []);
        $ticket->load('performers');

        return response()->json([
            'status' => 'success',
            'html' => view('components.tickets.ticket-performer', [
                'ticket' => $ticket,
                'users' => $ticket->performers,
            ])->render()
        ]);
    }

    public function getTicketPerformers(Ticket $ticket)
    {
        $this->authorize('show', $ticket);

        $performerIds = $ticket->performers->pluck('id');
        return response()->json(['performerIds' => $performerIds]);
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
