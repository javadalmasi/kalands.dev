<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentVote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentVoteController extends Controller
{
    public function store(Request $request, Comment $comment): RedirectResponse
    {
        $data = $request->validate([
            'vote' => ['required', 'in:1,-1'],
        ]);

        $voteValue = (int) $data['vote'];
        $existingVote = CommentVote::query()
            ->where('comment_id', $comment->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existingVote && (int) $existingVote->vote === $voteValue) {
            $existingVote->delete();
            return back()->with('message', 'رای شما حذف شد.');
        }

        CommentVote::query()->updateOrCreate(
            [
                'comment_id' => $comment->id,
                'user_id' => auth()->id(),
            ],
            [
                'vote' => $voteValue,
            ]
        );

        return back()->with('message', 'رای شما ثبت شد.');
    }
}
