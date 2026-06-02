@props([
    'title' => 'عنوان صفحه',
    'back' => null,
    'clean' => true
])
@extends('layouts.index')
@section('head')
    <title>{{$title}}</title>
@endsection
@section('content')
    <div class="flex min-h-screen items-center justify-center bg-slate dark:bg-black">
        <div class="container">
            <div class="relative mx-auto max-w-[480px] rounded-squircle border border-slate/70 bg-white p-6 shadow-base dark:border-white/10 dark:bg-slate md:p-10">
                @if($back)
                    <a href="{{ route($back) }}"
                       class="absolute -top-3.5 -right-3.5 flex h-10 w-10 items-center justify-center rounded-full border border-white/70 bg-slate-800 text-white shadow-base transition-all duration-200 hover:bg-primary hover:text-white dark:border-white/10 dark:bg-slate-700">
                        <svg class="h-6 w-6">
                            <use xlink:href="#chevron-right"/>
                        </svg>
                    </a>
                @endif
                <!-- Logo -->
                <a href="{{route('index')}}" class="block border-b border-slate pb-6 dark:border-white/10">
                    <picture>
                        <source type="image/avif" srcset="{{ asset('/assets/images/logo.avif') }}">
                        <source type="image/png" srcset="{{ asset('/assets/images/logo.png') }}">
                        <source type="image/webp" srcset="{{ asset('/assets/images/logo.webp') }}">
                        <img width="210" height="50" loading="lazy" decoding="async" alt="کالندز"
                             class="mx-auto w-40" src="/assets/images/logo.svg">
                    </picture>
                </a>
                <div class="pt-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
@endsection
