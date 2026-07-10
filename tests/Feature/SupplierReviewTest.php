<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_member_can_review_a_supplier(): void
    {
        $user = User::factory()->create(['ativo' => true]);
        $supplier = Supplier::factory()->create();

        $response = $this->actingAs($user)->post(route('suppliers.review', $supplier), [
            'nota' => 5,
            'destaques' => ['rapido', 'recomendo'],
            'comentario' => 'Ótimo fornecedor',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('supplier_reviews', [
            'supplier_id' => $supplier->id,
            'user_id' => $user->id,
            'nota' => 5,
        ]);
    }

    public function test_reviewing_twice_updates_the_existing_review(): void
    {
        $user = User::factory()->create(['ativo' => true]);
        $supplier = Supplier::factory()->create();

        $this->actingAs($user)->post(route('suppliers.review', $supplier), ['nota' => 3]);
        $this->actingAs($user)->post(route('suppliers.review', $supplier), ['nota' => 5]);

        $this->assertSame(1, $supplier->reviews()->count());
        $this->assertSame(5, $supplier->reviews()->first()->nota);
    }

    public function test_guest_cannot_review_a_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $this->post(route('suppliers.review', $supplier), ['nota' => 5])
            ->assertRedirect(route('login'));
    }
}
