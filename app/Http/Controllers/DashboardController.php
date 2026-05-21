<?php

namespace App\Http\Controllers;

use App\Services\NotionService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private NotionService $notion) {}

    public function index(Request $request)
    {
        $user      = $request->session()->get('user');
        $suppliers = $this->notion->getSuppliers();

        return view('dashboard', compact('user', 'suppliers'));
    }
}
