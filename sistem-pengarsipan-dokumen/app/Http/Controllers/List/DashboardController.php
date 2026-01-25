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


// <?php

// namespace App\Http\Controllers;

// use App\Models\Documents;
// use App\Models\Category;
// use Inertia\Inertia;

// class DashboardController extends Controller
// {
//     public function show()
//     {
//         $documents = Documents::with(['category', 'status'])
//             ->where('user_id', auth()->id())
//             ->latest('updated_at')
//             ->take(10)
//             ->get()
//             ->map(fn ($doc) => $this->transformDocument($doc));

//         $statusCounts = [
//             'active' => Documents::where('user_id', auth()->id())->whereNull('deleted_at')->where('is_archived', false)->count(),
//             'archived' => Documents::where('user_id', auth()->id())->whereNull('deleted_at')->where('is_archived', true)->count(),
//             'deleted' => Documents::where('user_id', auth()->id())->onlyTrashed()->count(),
//         ];

//         $categories = Category::withCount('documents')
//             ->get()
//             ->map(fn ($cat) => [
//                 'id' => $cat->id,
//                 'title' => strtoupper($cat->category_name),
//                 'files' => $cat->documents_count,
//                 'size' => Documents::where('category_id', $cat->id)->sum('size'),
//                 'iconBg' => 'bg-blue-500/20', // Adjust dynamically if needed
//                 'iconColor' => 'text-blue-500',
//                 'percentUsed' => '24% Used', // Calculate if needed
//             ]);

//         $totalStorage = Documents::where('user_id', auth()->id())->sum('size');

//         return Inertia::render('Dashboard', [
//             'documents' => $documents,
//             'statusCounts' => $statusCounts,
//             'categories' => $categories,
//             'totalStorage' => $totalStorage,
//         ]);
//     }

//     private function transformDocument(Documents $doc): array
//     {
//         // Sama dengan di DocumentController
//         // Copy from above
//     }
// }
