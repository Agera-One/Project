<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Controller;
use App\Models\Documents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

abstract class BaseDocumentListController extends Controller
{
    protected function baseQuery(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->role === 'admin';

        $query = Documents::withTrashed()
            ->with('user')
            ->when(
                $request->filled('search'),
                fn ($q) => $q
                    ->where(function ($qq) use ($request) {
                        $qq->where('title', 'like', '%' . $request->search . '%')
                           ->orWhere('original_name', 'like', '%' . $request->search . '%');
                    })
            );

        // Kalau bukan admin, filter hanya dokumen miliknya
        if (! $isAdmin) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    protected function render(
        $query,
        string $page,
        Request $request,
        string $orderBy = 'updated_at'
    ) {
        $documents = $query
            ->latest($orderBy)
            ->get()
            ->map(fn ($doc) => $this->transformDocument($doc));

        return inertia($page, [
            'documents' => $documents,
            'filters'   => $request->only('search'),
        ]);
    }

    protected function transformDocument($doc): array
    {
        return [
            'id'          => $doc->id,
            'title'       => $doc->title,
            'size'        => (int) $doc->size,
            'status'      => $doc->deleted_at ? 'deleted' : ($doc->is_archived ? 'archived' : 'active'),
            'updated_at'  => ($doc->updated_at ?? $doc->created_at)?->toIso8601String(),
            'deleted_at'  => $doc->deleted_at?->toIso8601String(),
            'extension'   => strtolower(
                pathinfo($doc->original_name ?? '', PATHINFO_EXTENSION) ?: 'unknown'
            ),
            'is_starred'  => (bool) $doc->is_starred,
            'is_archived' => (bool) $doc->is_archived,
            'file_url'    => $doc->file_path
                ? Storage::url($doc->file_path)
                : null,
            'owner' => $doc->user ? [
                'id'    => $doc->user->id,
                'name'  => $doc->user->name,
                'email' => $doc->user->email,
            ] : null,
        ];
    }
}
