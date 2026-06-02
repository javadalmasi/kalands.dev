@extends('layouts.index')

@section('head')
    <title>انقضای نشست کاربر | کالندز</title>
@endsection

@section('content')
    <main class="flex-grow bg-slate pb-14 dark:bg-black">
        @include('errors.partials.error-layout', [
            'code' => '419',
            'headline' => 'امنیت نشست شما به پایان رسیده است',
            'message' => 'به دلیل وقفه طولانی در فعالیت شما، اعتبار امنیتی این صفحه منقضی شده است. برای ادامه فعالیت لطفا صفحه را مجددا بارگذاری نمایید.'
        ])
    </main>
@endsection
