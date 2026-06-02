<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function comments()
    {
        $comments = Comment::query()->latest()->get();
        return view('admin.comments', compact('comments'));
    }

    public function rejectComment(Comment $comment)
    {
        $comment->update([
            'status' => Comment::STATUS_REJECTED
        ]);

        return back();
    }

    public function acceptComment(Comment $comment)
    {
        $comment->update([
            'status' => Comment::STATUS_APPROVED
        ]);

        return back();
    }

    public function spamComment(Comment $comment)
    {
        $comment->update([
            'status' => Comment::STATUS_SPAM,
        ]);

        return back();
    }

    public function deleteComment(Comment $comment)
    {
        $comment->delete();

        return back();
    }
}
