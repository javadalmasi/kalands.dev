@extends('layouts.index')

@section('head')
    <title>خطای داخلی سرور | کالندز</title>
@endsection

@section('content')
    <main class="flex-grow bg-slate pb-14 dark:bg-black">
        @include('errors.partials.error-layout', [
            'code' => '500',
            'headline' => 'مشکلی در سرور پیش آمده است',
            'message' => 'در حین پردازش درخواست شما خطای غیرمنتظره‌ای در سیستم رخ داد. تیم فنی مطلع شده است. لطفا دقایقی دیگر مجددا تلاش نمایید.'
        ])
    </main>
@endsection
