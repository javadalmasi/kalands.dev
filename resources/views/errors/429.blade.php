@extends('layouts.index')

@section('head')
    <title>درخواست‌های بیش از حد | کالندز</title>
@endsection

@section('content')
    <main class="flex-grow bg-slate pb-14 dark:bg-black">
        @include('errors.partials.error-layout', [
            'code' => '429',
            'headline' => 'آرام‌تر! درخواست‌های شما زیاد است',
            'message' => 'تعداد درخواست‌های ارسالی از سمت شما بیش از حد مجاز در بازه زمانی کوتاه است. لطفا کمی استراحت کنید و چند دقیقه دیگر دوباره تلاش کنید.'
        ])
    </main>
@endsection
