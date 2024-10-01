<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tags\StoreRequest;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = auth()->user()->getDepartment()->tags;

        return view('cabinet.tags.index', compact('tags'));
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $data['department_id'] = auth()->user()->getDepartmentId();
        Tag::query()->create($data);

        return response()->json(['success' => true], 201);
    }

    public function show(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        $tag = Tag::query()->findOrFail($id);
        abort_unless($tag->department_id !== auth()->user()->getDepartmentId(), 403);
        $tag->delete();
        return response()->json(['success' => true]);
    }
}
