<?php

namespace Tests\Feature;

use App\Mail\WelcomeMemberMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class HotmartWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.hotmart.hottok' => 'test-hottok']);
    }

    private function postWebhook(array $payload, ?string $hottok = 'test-hottok')
    {
        return $this->postJson('/api/webhooks/hotmart', $payload, array_filter([
            'X-Hotmart-Hottok' => $hottok,
        ]));
    }

    public function test_rejects_requests_with_invalid_hottok(): void
    {
        $response = $this->postWebhook(['event' => 'PURCHASE_APPROVED'], 'token-errado');

        $response->assertStatus(401);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_purchase_approved_creates_active_user_and_sends_email(): void
    {
        Mail::fake();

        $response = $this->postWebhook([
            'event' => 'PURCHASE_APPROVED',
            'data' => [
                'buyer' => ['name' => 'Novo Membro', 'email' => 'novo@example.com'],
                'purchase' => ['transaction' => 'HP123'],
            ],
        ]);

        $response->assertNoContent();

        $user = User::where('email', 'novo@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->ativo);
        $this->assertSame('HP123', $user->hotmart_transaction);

        Mail::assertSent(WelcomeMemberMail::class, fn ($mail) => $mail->hasTo('novo@example.com'));
    }

    public function test_refund_deactivates_existing_user(): void
    {
        $user = User::factory()->create(['email' => 'membro@example.com', 'ativo' => true]);

        $response = $this->postWebhook([
            'event' => 'PURCHASE_REFUNDED',
            'data' => [
                'buyer' => ['name' => $user->name, 'email' => $user->email],
                'purchase' => ['transaction' => 'HP123'],
            ],
        ]);

        $response->assertNoContent();
        $this->assertFalse($user->fresh()->ativo);
    }
}
