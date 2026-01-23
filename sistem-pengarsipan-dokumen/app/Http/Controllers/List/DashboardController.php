<?php

namespace App\Http\Controllers\List;

use Illuminate\Http\Request;
use App\Models\Documents;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function show()
    {
        $documents = Documents::with('status')
            ->latest()
            ->get()
            ->map(fn ($doc) => [
                'id'         => $doc->id,
                'title'      => $doc->original_name ?? $doc->title,
                'size'       => (int) $doc->size,
                'status'     => strtolower($doc->status?->status_name ?? 'active'),
                'created_at' => $doc->created_at->toIso8601String(),
                'extension'  => $doc->extension,
            ]);

        return Inertia::render('dashboard', [
            'documents' => $documents,
        ]);
    }
}
