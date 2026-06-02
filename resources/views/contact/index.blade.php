<x-layouts.app title="تماس با ما">
    <main class="bg-slate pb-16 pt-28 dark:bg-black md:pb-20 md:pt-36">
        <div class="container">
            <div class="grid gap-6 {{ ($contactInfo['enabled'] ?? true) ? 'lg:grid-cols-2' : 'lg:grid-cols-1' }}">
                @if($contactInfo['enabled'] ?? true)
                <div class="order-2 rounded-squircle border border-slate/70 bg-white p-6 shadow-base dark:border-white/10 dark:bg-slate">
                    @if(($contactInfo['show_title'] ?? true) && !empty($contactInfo['title']))
                        <h2 class="mb-3 text-xl font-bold text-slate dark:text-white">{{ $contactInfo['title'] }}</h2>
                    @endif
                    @if(($contactInfo['show_description'] ?? true) && !empty($contactInfo['description']))
                        <p class="mb-6 text-sm leading-7 text-slate dark:text-slate-200">{{ $contactInfo['description'] }}</p>
                    @endif

                    <div class="space-y-4 text-sm">
                        @if(($contactInfo['show_phone'] ?? true) && !empty($contactInfo['phone']))
                            <div class="rounded-squircle border border-slate/70 p-4 dark:border-white/10">
                                <p class="mb-1 text-slate dark:text-slate-200">تلفن</p>
                                <p class="font-medium text-slate dark:text-white">{{ $contactInfo['phone'] }}</p>
                            </div>
                        @endif
                        @if(($contactInfo['show_email'] ?? true) && !empty($contactInfo['email']))
                            <div class="rounded-squircle border border-slate/70 p-4 dark:border-white/10">
                                <p class="mb-1 text-slate dark:text-slate-200">ایمیل</p>
                                <p class="font-medium text-slate dark:text-white">{{ $contactInfo['email'] }}</p>
                            </div>
                        @endif
                        @if(($contactInfo['show_address'] ?? true) && !empty($contactInfo['address']))
                            <div class="rounded-squircle border border-slate/70 p-4 dark:border-white/10">
                                <p class="mb-1 text-slate dark:text-slate-200">آدرس</p>
                                <p class="font-medium text-slate dark:text-white">{{ $contactInfo['address'] }}</p>
                            </div>
                        @endif
                        @if(($contactInfo['show_work_hours'] ?? true) && !empty($contactInfo['work_hours']))
                            <div class="rounded-squircle border border-slate/70 p-4 dark:border-white/10">
                                <p class="mb-1 text-slate dark:text-slate-200">ساعت پاسخگویی</p>
                                <p class="font-medium text-slate dark:text-white">{{ $contactInfo['work_hours'] }}</p>
                            </div>
                        @endif
                        @if(($contactInfo['show_map'] ?? false) && !empty($contactInfo['map_iframe']))
                            <div class="overflow-hidden rounded-squircle border border-slate dark:border-white/10">
                                {!! $contactInfo['map_iframe'] !!}
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                <div class="order-1 rounded-squircle border border-slate/70 bg-white p-6 shadow-base dark:border-white/10 dark:bg-slate {{ ($contactInfo['enabled'] ?? true) ? '' : 'mx-auto w-full max-w-3xl' }}">
                <h1 class="mb-5 text-xl font-bold text-slate dark:text-white">تماس با ما</h1>
                <div class="mb-4 rounded-squircle border border-primary/20 bg-primary/5 p-3 text-sm text-slate dark:text-slate-200">
                    قبل از ارسال پیام، لطفا بخش
                    <a href="{{ route('faq.index') }}" class="font-bold text-primary hover:underline">سوالات متداول</a>
                    را مطالعه کنید.
                </div>

                <form action="{{ route('contact.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <x-form.input name="name" label="نام" value="{{ old('name') }}"/>
                    <x-form.input name="email" label="ایمیل" type="email" value="{{ old('email') }}"/>
                    <x-form.input name="subject" label="موضوع" value="{{ old('subject') }}"/>
                    <label class="block text-sm text-slate dark:text-slate-200">
                        پیام
                        <textarea name="message" rows="5" class="mt-1 w-full rounded-squircle border border-slate bg-transparent p-3 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">{{ old('message') }}</textarea>
                    </label>

                    <div class="pt-2">
                        <x-security.challenge-options :challenge="$challenge" />
                    </div>

                    <x-session-errors/>
                    <x-session-messages/>

                    <button class="btn-primary w-full py-3">ارسال پیام</button>
                </form>
                </div>
            </div>
        </div>
    </main>
</x-layouts.app>
