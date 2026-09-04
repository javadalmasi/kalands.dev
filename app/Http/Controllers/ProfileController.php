<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\Auth\PasswordHashService;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index');
    }

    public function edit()
    {
        $user = auth()->user();

        return view('profile.edit', compact('user'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = auth()->user();

        $user->update($request->only('first_name', 'last_name', 'theme_preference', 'profile_bio'));

        if ($request->email != $user->email) {
            $user->email = request('email');
            $user->email_verified_at = null;
            $user->save();
        }

        if ($request->phone != $user->phone) {
            $user->phone = request('phone');
            $user->phone_verified_at = null;
            $user->save();
        }

        return redirect()->route('dash.user.index', ['authkey' => $user->dashboard_authkey])
            ->with('message', 'اطلاعات حساب کاربری باموفقیت ویرایش شد.');
    }

    public function updatePassword(UpdatePasswordRequest $request, PasswordHashService $passwordHashService)
    {
        $password = $passwordHashService->make($request->get('password'));

        auth()->user()->update([
            'password_hash' => $password['hash'],
            'password_salt' => $password['salt'],
        ]);

        return back()->with('message', 'رمز عبور شما باموفقیت تغییر یافت.');
    }
}
