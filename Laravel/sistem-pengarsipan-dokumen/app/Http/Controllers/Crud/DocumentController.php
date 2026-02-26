<?php

namespace App\Http\Controllers\Crud;

use App\Http\Controllers\Controller;
use App\Models\Documents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentController extends Controller
{
    private function isAdmin(): bool
    {
        return auth()->user()->role === 'admin';
    }

    private function canAccess(Documents $document): bool
    {
        return $this->isAdmin() || $document->user_id === auth()->id();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'files'   => 'required|array|min:1',
            'files.*' => [
                'required',
                'file',
                'max:51200',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png',
            ],
            'title'     => 'nullable|string|max:255',
            'status_id'=> 'nullable|integer|exists:statuses,id',
        ]);

        $userId = auth()->id();
        $uploaded = 0;
        $errors = [];

        foreach ($request->file('files') as $file) {
            try {
                $path = $file->store("documents/{$userId}", 'public');

                Documents::create([
                    'user_id'       => $userId,
                    'title'         => $validated['title']
                        ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'status_id'     => $validated['status_id'] ?? null,
                    'size'          => $file->getSize(),
                    'file_path'     => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type'     => $file->getMimeType(),
                ]);

                $uploaded++;
            } catch (\Throwable $e) {
                $errors[] = $file->getClientOriginalName() . ': ' . $e->getMessage();
            }
        }

        if ($uploaded > 0) {
            return back()->with(
                'success',
                $uploaded === 1
                    ? '1 dokumen berhasil diunggah'
                    : "{$uploaded} dokumen berhasil diunggah"
            );
        }

        return back()->withErrors([
            'files' => implode('; ', $errors) ?: 'Tidak ada file yang berhasil diunggah'
        ]);
    }

    public function show(Documents $document)
    {
        if (! $this->canAccess($document)) {
            abort(403);
        }

        return redirect(Storage::url($document->file_path));
    }

    public function update(Request $request, Documents $document)
    {
        if (! $this->canAccess($document)) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $document->update([
            'title' => $request->title,
        ]);

        return back();
    }

    public function toggleStar(Documents $document)
    {
        if ($this->isAdmin()) {
            abort(403); // admin tidak boleh star
        }

        if ($document->user_id !== auth()->id()) {
            abort(403);
        }

        $document->update([
            'is_starred' => ! $document->is_starred,
        ]);

        return back();
    }

    public function toggleArchive(Documents $document)
    {
        if ($this->isAdmin()) {
            abort(403); // admin tidak boleh archive
        }

        if ($document->user_id !== auth()->id()) {
            abort(403);
        }

        $document->update([
            'is_archived' => ! $document->is_archived,
        ]);

        return back();
    }

    public function destroy(Documents $document)
    {
        if (! $this->canAccess($document)) {
            abort(403);
        }

        $document->delete();

        return back()->with('success', 'Dokumen dipindahkan ke sampah');
    }

    public function restore(Documents $document)
    {
        if (! $this->canAccess($document)) {
            abort(403);
        }

        $document->restore();

        return back()->with('success', 'Dokumen berhasil dipulihkan');
    }

    public function forceDelete(Documents $document)
    {
        if (! $this->canAccess($document)) {
            abort(403);
        }

        $document->forceDelete();

        return back()->with('success', 'Dokumen dihapus permanen');
    }
}
