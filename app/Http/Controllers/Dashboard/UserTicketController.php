<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketMessageRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Mail\GenericTemplateMail;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Repositories\SettingsRepository;
use App\Services\EmailTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class UserTicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::query()
            ->where('user_id', auth()->id())
            ->with('category')
            ->latest()
            ->get();

        return view('dash.user.tickets.index', compact('tickets'));
    }

    public function create(SettingsRepository $settingsRepository)
    {
        $settings = $settingsRepository->get('tickets.settings', ['enabled' => true]);
        $blockedUsers = $settingsRepository->get('tickets.blocked_users', []);
        $isBlocked = in_array(auth()->id(), $blockedUsers);

        $categories = TicketCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('dash.user.tickets.create', compact('categories', 'settings', 'isBlocked'));
    }

    public function store(StoreTicketRequest $request, SettingsRepository $settingsRepository): RedirectResponse
    {
        $settings = $settingsRepository->get('tickets.settings', ['enabled' => true]);
        if (! ($settings['enabled'] ?? true)) {
            return back()->withErrors($settings['disabled_message'] ?? 'ارسال تیکت در این زمان ممکن نیست.');
        }

        $blockedUsers = $settingsRepository->get('tickets.blocked_users', []);
        if (in_array(auth()->id(), $blockedUsers)) {
            return back()->withErrors('شما به دلیل عدم رعایت قوانین از ارسال درخواست منع شده اید');
        }

        $ticket = Ticket::query()->create([
            'user_id' => auth()->id(),
            'category_id' => $request->integer('category_id'),
            'subject' => $request->string('subject')->toString(),
            'priority' => $request->input('priority', 'medium'),
            'status' => 'open',
        ]);

        $ticket->messages()->create([
            'sender_type' => 'user',
            'sender_id' => auth()->id(),
            'message' => nl2br(e(strip_tags((string) $request->input('message')))),
        ]);

        // Send notification to admin if set
        if ($adminEmail = ($settings['admin_email'] ?? null)) {
            try {
                $emailTemplateService = app(EmailTemplateService::class);
                $rendered = $emailTemplateService->render('ticket_created', [
                    'ticket_id' => $ticket->id,
                    'ticket_subject' => $ticket->subject,
                    'user_name' => auth()->user()->full_name,
                    'app_name' => config('app.name'),
                ]);

                Mail::to($adminEmail)->send(new GenericTemplateMail($rendered['subject'], $rendered['html']));
            } catch (\Exception $e) {
                // Log or ignore
            }
        }

        return redirect()->route('dash.user.tickets.show', [
            'authkey' => request()->route('authkey'),
            'ticket' => $ticket->id,
        ])->with('message', 'تیکت با موفقیت ثبت شد.');
    }

    public function show(string $authkey, Ticket $ticket)
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        $ticket->load(['category', 'messages.user', 'messages.admin']);

        return view('dash.user.tickets.show', compact('ticket'));
    }

    public function reply(string $authkey, StoreTicketMessageRequest $request, Ticket $ticket, SettingsRepository $settingsRepository): RedirectResponse
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        $settings = $settingsRepository->get('tickets.settings', ['enabled' => true]);
        if (! ($settings['enabled'] ?? true)) {
            return back()->withErrors($settings['disabled_message'] ?? 'ارسال تیکت در این زمان ممکن نیست.');
        }

        $blockedUsers = $settingsRepository->get('tickets.blocked_users', []);
        if (in_array(auth()->id(), $blockedUsers)) {
            return back()->withErrors('شما به دلیل عدم رعایت قوانین از ارسال درخواست منع شده اید');
        }

        if ($ticket->status === 'closed') {
            return back()->withErrors('این تیکت بسته شده و امکان پاسخ ندارد.');
        }

        $ticket->messages()->create([
            'sender_type' => 'user',
            'sender_id' => auth()->id(),
            'message' => nl2br(e(strip_tags((string) $request->input('message')))),
        ]);

        $ticket->update([
            'status' => 'open',
        ]);

        return back()->with('message', 'پاسخ شما ثبت شد.');
    }

    public function close(string $authkey, Ticket $ticket): RedirectResponse
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        $ticket->update([
            'status' => 'closed',
        ]);

        return back()->with('message', 'تیکت با موفقیت بسته شد.');
    }
}
