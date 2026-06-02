@props([
    'challenge',
    'name' => 'js_challenge_answer',
])

@php
    $q = (string) ($challenge['question'] ?? '');
    $normalized = strtr($q, ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','×'=>'*','x'=>'*','X'=>'*','÷'=>'/']);
    preg_match_all('/-?\d+/', $normalized, $m);
    $nums = array_map('intval', $m[0] ?? []);
    $correct = (int) old($name, 0);
    if (count($nums) >= 2) {
        if (str_contains($normalized, '*')) {
            $correct = $nums[0] * $nums[1];
        } elseif (str_contains($normalized, '/')) {
            $correct = $nums[1] !== 0 ? (int) floor($nums[0] / $nums[1]) : $nums[0];
        } elseif (str_contains($normalized, '-')) {
            $correct = $nums[0] - $nums[1];
        } else {
            $correct = $nums[0] + $nums[1];
        }
    }
    $choices = [$correct, $correct + 1, $correct - 1];
    $choices = array_values(array_unique($choices));
    while (count($choices) < 3) {
        $choices[] = $correct + count($choices) + 1;
    }
    shuffle($choices);
@endphp

<div class="rounded-squircle border border-primary/20 bg-primary/5 p-4">
    <input type="hidden" name="js_challenge_key" value="{{ $challenge['key'] }}">
    <p class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-200">چالش امنیتی: {{ $challenge['question'] }}</p>
    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
        @foreach($choices as $choice)
            <label class="cursor-pointer">
                <input type="radio" name="{{ $name }}" value="{{ $choice }}" class="peer sr-only" @checked((string) old($name) === (string) $choice)>
                <span class="flex h-11 items-center justify-center rounded-squircle border border-slate-200 bg-white text-sm font-bold text-slate-700 transition peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white dark:border-white/10 dark:bg-slate-800 dark:text-slate-100 dark:peer-checked:!bg-primary dark:peer-checked:!text-white dark:peer-checked:border-primary">
                    {{ $choice }}
                </span>
            </label>
        @endforeach
    </div>
</div>
