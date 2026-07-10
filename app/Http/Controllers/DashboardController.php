<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $suppliers = Supplier::withAvg('reviews', 'nota')
            ->with('reviews')
            ->orderBy('nome')
            ->get();

        return view('dashboard', compact('user', 'suppliers'));
    }
}
