@extends('layouts.index')

@section('head')
    <title>دسترسی غیرمجاز | کالندز</title>
@endsection

@section('content')
    <main class="flex-grow bg-slate pb-14 dark:bg-black">
        @include('errors.partials.error-layout', [
            'code' => '403',
            'headline' => 'دسترسی به این صفحه محدود شده است',
            'message' => 'متاسفانه شما اجازه ورود به این بخش را ندارید. این محدودیت ممکن است به دلیل سطح دسترسی حساب کاربری شما باشد.'
        ])
    </main>
@endsection
