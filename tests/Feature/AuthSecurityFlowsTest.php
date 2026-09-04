<?php

namespace Tests\Feature;

use App\Jobs\SendPasswordResetCodeJob;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AuthSecurityFlowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_step_registration_flow_creates_user_and_logs_in(): void
    {
        $stepOne = $this->challengeSession('auth_register_step1', 9);

        $this->withSession($stepOne['session'])->post(route('auth.register.step1.store'), [
            'first_name' => 'Ali',
            'last_name' => 'Karimi',
            'identifier' => 'ali@example.com',
            'password' => 'Secure#123',
            'password_confirmation' => 'Secure#123',
            'js_challenge_key' => $stepOne['key'],
            'js_challenge_answer' => '9',
        ])->assertRedirect(route('auth.register.step2'));

        $stepTwo = $this->challengeSession('auth_register_step2', 5);
        $stepTwoSession = array_merge($stepOne['session'], $stepTwo['session']);

        $this->withSession($stepTwoSession)->post(route('auth.register.step2.store'), [
            'theme_preference' => 'dark',
            'profile_bio' => 'My short bio',
            'marketing_opt_in' => '1',
            'js_challenge_key' => $stepTwo['key'],
            'js_challenge_answer' => '5',
        ])->assertRedirectContains('/dash/user/');

        $this->assertAuthenticated('web');

        $user = User::query()->first();
        $this->assertNotNull($user);
        $this->assertSame('ali@example.com', $user->email);
        $this->assertSame('dark', $user->theme_preference);
        $this->assertNotEmpty($user->dashboard_authkey);
    }

    public function test_dashboard_authkey_middleware_rejects_invalid_authkey(): void
    {
        $user = User::query()->create([
            'first_name' => 'Sara',
            'last_name' => 'Ahmadi',
            'email' => 'sara@example.com',
            'password_hash' => 'hashed',
            'password_salt' => 'salt',
            'is_active' => true,
            'dashboard_authkey' => 'VALIDKEY1234',
            'dashboard_authkey_expires_at' => now()->addHour(),
            'theme_preference' => 'light',
        ]);

        $this->actingAs($user, 'web')
            ->withSession(['dashboard.user.authkey' => 'VALIDKEY1234'])
            ->get(route('dash.user.index', ['authkey' => 'WRONGKEY']))
            ->assertRedirect(route('auth.login'));

        $this->assertGuest('web');
    }

    public function test_forget_password_dispatches_reset_code_job(): void
    {
        Queue::fake();

        User::query()->create([
            'first_name' => 'Reza',
            'last_name' => 'Moradi',
            'email' => 'reza@example.com',
            'password_hash' => 'hashed',
            'password_salt' => 'salt',
            'is_active' => true,
            'theme_preference' => 'light',
        ]);

        $challenge = $this->challengeSession('auth_forget', 3);

        $this->withSession($challenge['session'])->post(route('auth.forget.send'), [
            'identifier' => 'reza@example.com',
            'js_challenge_key' => $challenge['key'],
            'js_challenge_answer' => '3',
        ])->assertSessionHas('message');

        Queue::assertPushed(SendPasswordResetCodeJob::class, function (SendPasswordResetCodeJob $job) {
            return true;
        });

        $this->assertDatabaseHas((new PasswordResetCode)->getTable(), [
            'identifier' => 'reza@example.com',
        ]);
    }

    private function challengeSession(string $context, int $answer): array
    {
        $key = $context.':test-key';

        return [
            'key' => $key,
            'session' => [
                'js_challenge.'.$key => [
                    'answer' => $answer,
                    'context' => $context,
                    'expires_at' => now()->addMinutes(10)->toISOString(),
                ],
            ],
        ];
    }
}
