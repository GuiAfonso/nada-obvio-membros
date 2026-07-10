<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupplierReviewController extends Controller
{
    public function store(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'nota' => 'required|integer|min:1|max:5',
            'destaques' => 'array',
            'destaques.*' => 'string|in:'.implode(',', array_keys(config('suppliers.destaques'))),
            'comentario' => 'nullable|string|max:1000',
        ]);

        $supplier->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'nota' => $validated['nota'],
                'destaques' => $validated['destaques'] ?? [],
                'comentario' => $validated['comentario'] ?? null,
            ],
        );

        return back()->with('status', 'Avaliação enviada com sucesso.');
    }
}
