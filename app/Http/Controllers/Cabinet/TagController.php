<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Enums\TicketStatusEnum;
use App\Http\Requests\Tags\StoreRequest;
use App\Http\Requests\Tags\UpdateRequest;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = auth()->user()->getDepartment()->tags()
            ->withCount([
                'tickets',
                'tickets as open_tickets_count' => fn ($q) => $q->whereIn('tickets.status', [
                    TicketStatusEnum::OPENED->value,
                    TicketStatusEnum::IN_PROGRESS->value,
                ]),
            ])
            ->reorder('text')
            ->get();

        return view('cabinet.tags.index', compact('tags'));
    }

    public function store(StoreRequest $request)
    {
        $this->authorize('create', Tag::class);

        $data = $request->validated();
        $data['department_id'] = auth()->user()->getDepartmentId();
        Tag::query()->create($data);

        return response()->json(['success' => true], 201);
    }

    public function show(string $id)
    {
        //
    }

    public function update(UpdateRequest $request, string $id)
    {
        $tag = Tag::query()->findOrFail($id);
        abort_if($tag->department_id !== auth()->user()->getDepartmentId(), 403);
        $this->authorize('update', $tag);

        $tag->update($request->validated());

        return response()->json(['success' => true]);
    }

    public function destroy(string $id)
    {
        $tag = Tag::query()->findOrFail($id);
        $this->authorize('delete', $tag);
        abort_if($tag->department_id !== auth()->user()->getDepartmentId(), 403);
        $tag->delete();
        return response()->json(['success' => true]);
    }
}
