<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use Illuminate\View\View;

class FeeStructureController extends Controller
{
    /**
     * Display the fee structure manager.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Fee::class);

        return view('pages.fee.fee-structure.index');
    }
}
