<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MentionController extends Controller
{
    public function getUnreadMentions(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Only AJAX requests are allowed'], 403);
        }

        $mentions = auth()->user()->unreadMentions()
            ->with(['comment.ticket', 'comment.creator'])
            ->latest()
            ->get();

        $html = view('components.mention-list', ['mentions' => $mentions])->render();

        return response()->json([
            'success' => true,
            'mentions_count' => count($mentions),
            'html' => $html,
        ]);
    }
}
