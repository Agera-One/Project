<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Controller;
use App\Models\Documents;
use Illuminate\Http\Request;

abstract class BaseDocumentListController extends Controller
{
    protected function baseQuery(Request $request)
    {
        return Documents::query()
            ->with('status')
            ->when(
                $request->filled('search'),
                fn ($q) =>
                    $q->where('title', 'like', '%' . $request->search . '%')
                      ->orWhere('original_name', 'like', '%' . $request->search . '%')
            );
    }

    protected function render($query, string $page, Request $request)
    {
        $documents = $query
            ->latest('updated_at')
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
            'id'         => $doc->id,
            'title'      => $doc->original_name ?? $doc->title,
            'size'       => (int) $doc->size,
            'status'     => strtolower($doc->status?->status_name ?? 'active'),
            'updated_at' => ($doc->updated_at ?? $doc->created_at)?->toIso8601String(),
            'extension'  => $doc->extension,
            'is_starred' => (bool) $doc->is_starred,
            'is_deleted' => ! is_null($doc->deleted_at),
        ];
    }
}
