<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMemberMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class HotmartWebhookController extends Controller
{
    private const APPROVED_EVENTS = ['PURCHASE_APPROVED', 'PURCHASE_COMPLETE'];

    private const REVOKE_EVENTS = [
        'PURCHASE_CANCELED',
        'PURCHASE_REFUNDED',
        'PURCHASE_CHARGEBACK',
        'SUBSCRIPTION_CANCELLATION',
    ];

    public function handle(Request $request): Response
    {
        if (! hash_equals((string) config('services.hotmart.hottok'), (string) $request->header('X-Hotmart-Hottok'))) {
            return response()->noContent(401);
        }

        $event = $request->input('event');
        $email = $request->input('data.buyer.email');
        $nome = $request->input('data.buyer.name', 'Membro');
        $transaction = $request->input('data.purchase.transaction');

        if (! $email) {
            Log::warning('Webhook Hotmart sem email de comprador.', ['event' => $event]);

            return response()->noContent();
        }

        if (in_array($event, self::APPROVED_EVENTS, true)) {
            $this->grantAccess($email, $nome, $transaction);
        } elseif (in_array($event, self::REVOKE_EVENTS, true)) {
            $this->revokeAccess($email);
        } else {
            Log::info('Webhook Hotmart com evento não tratado.', ['event' => $event]);
        }

        return response()->noContent();
    }

    private function grantAccess(string $email, string $nome, ?string $transaction): void
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update(['ativo' => true, 'hotmart_transaction' => $transaction ?? $user->hotmart_transaction]);

            return;
        }

        $password = Str::password(12);

        $user = User::create([
            'name' => $nome,
            'email' => $email,
            'password' => Hash::make($password),
            'ativo' => true,
            'hotmart_transaction' => $transaction,
        ]);

        Mail::to($user)->send(new WelcomeMemberMail($user, $password));
    }

    private function revokeAccess(string $email): void
    {
        User::where('email', $email)->update(['ativo' => false]);
    }
}
