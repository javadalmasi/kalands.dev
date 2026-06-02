<x-layouts.auth title="ثبت‌نام - مرحله اول">
    <h1 class="mb-6 text-center text-xl font-medium text-slate dark:text-white">ثبت‌نام - مرحله اول</h1>

    <form action="{{ route('auth.register.step1.store') }}" method="POST" class="space-y-4" id="register-step1-form">
        @csrf

        <x-form.input name="first_name" label="نام" value="{{ old('first_name', $stepOne['first_name'] ?? '') }}"/>
        <x-form.input name="last_name" label="نام خانوادگی" value="{{ old('last_name', $stepOne['last_name'] ?? '') }}"/>
        <x-form.input name="identifier" label="ایمیل یا موبایل" value="{{ old('identifier', $stepOne['identifier'] ?? '') }}"/>
        <x-form.input name="password" label="رمز عبور" type="password"/>
        <x-form.input name="password_confirmation" label="تکرار رمز عبور" type="password"/>

        <div class="rounded-squircle border border-slate p-3 dark:border-white/10">
            <div class="mb-2 flex items-center justify-between text-xs text-slate dark:text-white/80">
                <span>قدرت رمز عبور</span>
                <span id="password-strength-label">ضعیف</span>
            </div>
            <div class="h-2 w-full overflow-hidden rounded bg-slate dark:bg-slate">
                <div id="password-strength-bar" class="h-full w-0 bg-warning transition-all"></div>
            </div>
            <ul class="mt-2 space-y-1 text-xs text-slate dark:text-white/80">
                <li id="rule-length">حداقل ۸ کاراکتر</li>
                <li id="rule-case">حروف بزرگ و کوچک</li>
                <li id="rule-number">حداقل یک عدد</li>
                <li id="rule-symbol">حداقل یک نماد</li>
            </ul>
        </div>

        <x-security.challenge-options :challenge="$challenge" />

        <x-session-errors/>

        <button type="submit" class="btn-primary w-full py-3">ادامه</button>
    </form>

    <script>
        const passwordInput = document.querySelector('input[name="password"]');
        const bar = document.getElementById('password-strength-bar');
        const label = document.getElementById('password-strength-label');
        const rules = {
            length: document.getElementById('rule-length'),
            caseMix: document.getElementById('rule-case'),
            number: document.getElementById('rule-number'),
            symbol: document.getElementById('rule-symbol'),
        };

        function setRule(element, passed) {
            element.classList.toggle('text-primary', passed);
            element.classList.toggle('text-warning', !passed);
        }

        function checkPasswordStrength() {
            const value = passwordInput.value || '';
            const checks = {
                length: value.length >= 8,
                caseMix: /[a-z]/.test(value) && /[A-Z]/.test(value),
                number: /[0-9]/.test(value),
                symbol: /[^a-zA-Z0-9]/.test(value),
            };

            setRule(rules.length, checks.length);
            setRule(rules.caseMix, checks.caseMix);
            setRule(rules.number, checks.number);
            setRule(rules.symbol, checks.symbol);

            const score = Object.values(checks).filter(Boolean).length;
            const percent = (score / 4) * 100;
            bar.style.width = `${percent}%`;

            if (score <= 1) {
                bar.className = 'h-full bg-warning transition-all';
                label.textContent = 'ضعیف';
            } else if (score <= 3) {
                bar.className = 'h-full bg-alert transition-all';
                label.textContent = 'متوسط';
            } else {
                bar.className = 'h-full bg-primary transition-all';
                label.textContent = 'قوی';
            }
        }

        passwordInput?.addEventListener('input', checkPasswordStrength);
        checkPasswordStrength();
    </script>
</x-layouts.auth>
