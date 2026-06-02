@extends('layouts.index')

@section('head')
    <title>سایت در حال بروزرسانی | کالندز</title>
@endsection

@section('content')
    <main class="flex-grow bg-slate pb-14 dark:bg-black">
        @include('errors.partials.error-layout', [
            'code' => '503',
            'headline' => 'در حال بهینه‌سازی کالندز هستیم',
            'message' => 'ما در حال انجام برخی تغییرات فنی و بروزرسانی‌های دوره‌ای برای بهبود تجربه شما هستیم. سایت تا دقایقی دیگر به حالت عادی بازمی‌گردد.'
        ])
    </main>
@endsection
