@extends('layouts.index')

@section('head')
    <title>عدم احراز هویت | کالندز</title>
@endsection

@section('content')
    <main class="flex-grow bg-slate pb-14 dark:bg-black">
        @include('errors.partials.error-layout', [
            'code' => '401',
            'headline' => 'ابتدا باید وارد حساب خود شوید',
            'message' => 'برای دسترسی به این بخش، نیاز است تا هویت شما تایید شود. لطفا وارد حساب کاربری خود شوید و یا در صورت نداشتن حساب، ثبت نام کنید.'
        ])
    </main>
@endsection
