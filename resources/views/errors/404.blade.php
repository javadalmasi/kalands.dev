@extends('layouts.index')

@section('head')
    <title>صفحه پیدا نشد | کالندز</title>
@endsection

@section('content')
    <main class="flex-grow bg-slate pb-14 dark:bg-black">
        @include('errors.partials.error-layout', [
            'code' => '404',
            'headline' => 'صفحه‌ای که دنبالش بودید پیدا نشد',
            'message' => 'نشانی وارد شده اشتباه است یا این صفحه از کالندز حذف شده است. می‌توانید از جستجوی زیر برای یافتن محصول مورد نظر استفاده کنید.'
        ])
    </main>
@endsection
