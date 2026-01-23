<?php

namespace App\Http\Controllers\Crud;

use App\Models\Documents;
use App\Models\Category;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class DocumentController extends Controller
{
    public function create()
    {
        return Inertia::render('Documents/Create', [
            'statuses'   => Status::select('id', 'status_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'files'       => 'required|array|min:1',
            'files.*' => [
                'required',
                'file',
                'max:1048576',
                'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png',
            ],
            'title'       => 'nullable|string|max:255',
            'status_id'   => 'nullable|integer|exists:statuses,id',
        ]);

        $userId = auth()->id();
        $uploaded = 0;
        $errors = [];

        foreach ($request->file('files') as $file) {
            try {
                $path = $file->store('documents/' . $userId, 'public');

                Documents::create([
                    'user_id'        => $userId,
                    'title'          => $validated['title'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'status_id'      => $validated['status_id'] ?? null,
                    'size'           => $file->getSize(),
                    'file_path'      => $path,
                    'original_name'  => $file->getClientOriginalName(),
                    'mime_type'      => $file->getMimeType(),
                ]);

                $uploaded++;
            } catch (\Exception $e) {
                $errors[] = $file->getClientOriginalName() . ': ' . $e->getMessage();
            }
        }

        if ($uploaded > 0) {
            $msg = $uploaded === 1
                ? '1 dokumen berhasil diunggah'
                : "$uploaded dokumen berhasil diunggah";

            return back()->with('success', $msg);
        }

        return back()->withErrors(['files' => implode('; ', $errors) ?: 'Tidak ada file yang berhasil diunggah']);
    }

    public function edit(Documents $document)
    {
        $this->authorize('update', $document);

        return Inertia::render('Documents/Edit', [
            'document'   => $document,
            'statuses'   => Status::select('id', 'status_name')->get(),
        ]);
    }

    public function update(Request $request, Documents $document)
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'status_id'   => 'nullable|exists:statuses,id',
            'file'        => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png',
        ]);

        if ($request->hasFile('file')) {
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            $file = $request->file('file');
            $path = $file->store('documents/' . auth()->id(), 'public');

            $validated['file_path']     = $path;
            $validated['original_name'] = $file->getClientOriginalName();
            $validated['mime_type']     = $file->getMimeType();
            $validated['size']          = $file->getSize();
        }

        $document->update($validated);

        return redirect()->route('myDocuments')->with('success', 'Dokumen berhasil diperbarui');
    }

    public function destroy(Documents $document)
    {
        $this->authorize('delete', $document);

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Dokumen dipindahkan ke sampah');
    }

    public function restore(Documents $document)
    {
        $this->authorize('restore', $document);
        $document->restore();

        return back()->with('success', 'Dokumen berhasil dipulihkan');
    }
}
