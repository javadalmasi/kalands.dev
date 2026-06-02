<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLikeRequest;
use App\Models\CommentVote;
use App\Models\Like;

class LikeController extends Controller
{
    public function index()
    {
        $likes = Like::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $commentVotes = CommentVote::query()
            ->where('user_id', auth()->id())
            ->with(['comment.product'])
            ->latest()
            ->get();

        $commentVotesSummary = [
            'likes' => $commentVotes->where('vote', 1)->count(),
            'dislikes' => $commentVotes->where('vote', -1)->count(),
            'total' => $commentVotes->count(),
        ];

        return view('profile.likes', compact('likes', 'commentVotes', 'commentVotesSummary'));
    }

    public function store(StoreLikeRequest $request)
    {
        if (Like::query()
            ->where('product_id', request('product_id'))
            ->where('user_id', auth()->id())
            ->count()
        ) {
            Like::query()
                ->where('product_id', request('product_id'))
                ->where('user_id', auth()->id())
                ->delete();
            return back()->with('message', 'از پسند شده ها حذف شد.');
        }

        request()->merge([
            'user_id' => auth()->id()
        ]);

        Like::query()->create(request()->all());

        return back()->with('message', 'باموفقیت پسند شد.');
    }

    public function delete(string $authkey, Like $like)
    {
        if ($like->user_id != auth()->id()) abort(403);

        $like->delete();

        return back()->with('message', 'محصول از پسند شده ها حذف شد.');
    }
}
