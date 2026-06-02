@extends('layouts.index')

@section('head')
    <title>پیام سیستم | کالندز</title>
@endsection

@section('content')
    <main class="flex-grow bg-slate pb-14 dark:bg-black">
        @include('errors.partials.error-layout', [
            'code' => '200',
            'headline' => 'اعلان سیستمی',
            'message' => $exception->getMessage() ?: 'عملیات با موفقیت انجام شد یا پیام جدیدی از سمت سیستم دریافت نشده است.'
        ])
    </main>
@endsection
