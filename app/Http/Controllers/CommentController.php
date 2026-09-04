<?php

namespace App\Http\Controllers;

use App\Actions\StoreCommentAction;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Repositories\SettingsRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;

class CommentController extends Controller
{
    public function index()
    {
        $comments = auth()->user()->comments;

        return view('profile.comments', compact('comments'));
    }

    public function store(StoreCommentRequest $request, SettingsRepository $settingsRepository, StoreCommentAction $action): RedirectResponse
    {
        $settings = $settingsRepository->get('comments.settings', ['enabled' => true, 'disabled_message' => 'ارسال نظر در این زمان ممکن نیست.']);
        if (! ($settings['enabled'] ?? true)) {
            return back()->withErrors($settings['disabled_message'] ?? 'ارسال نظر در این زمان ممکن نیست.');
        }

        $rateKey = 'comments:'.($request->user()?->id ?? $request->ip());
        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            return back()->withErrors('تعداد ارسال نظر در این بازه زمانی بیش از حد مجاز است.');
        }

        $recentDuplicateExists = Comment::query()
            ->where('ip_address', $request->ip())
            ->where('content', strip_tags((string) $request->input('content')))
            ->where('created_at', '>=', now()->subMinutes(2))
            ->exists();

        if ($recentDuplicateExists) {
            return back()->withErrors('این نظر اخیرا ثبت شده است. لطفا کمی بعد دوباره تلاش کنید.');
        }

        RateLimiter::hit($rateKey, 120);

        return $this->executeAction($action);
    }

    public function show(string $authkey, Comment $comment)
    {
        return view('profile.comments-edit', compact('comment'));
    }

    public function update(UpdateCommentRequest $request)
    {
        $comment = Comment::query()->find($request->get('comment_id'));
        if (! $comment) {
            return back();
        }
        if ($comment->user_id != auth()->id()) {
            abort(403);
        }

        $comment->update([
            'content' => strip_tags((string) $request->get('content')),
            'status' => Comment::STATUS_PENDING,
        ]);

        return back()->with('message', 'نظر باموفقیت ویرایش شد و پس از تایید در سایت نمایش داده میشود.');
    }
}
