<?php

namespace App\Http\Controllers\List;

use App\Models\Documents;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB as FacadesDB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->role === 'admin';

        /**
         * =========================
         * DOCUMENT STATUS
         * =========================
         */

        $todayStart = Carbon::today();
        $todayEnd   = Carbon::tomorrow();

        // Base query: admin = semua data, user = data miliknya
        $base = Documents::withTrashed();

        if (! $isAdmin) {
            $base->where('user_id', $user->id);
        }

        // TOTAL
        $active = (clone $base)
            ->whereNull('deleted_at')
            ->where('is_archived', false)
            ->count();

        $archived = (clone $base)
            ->where('is_archived', true)
            ->whereNull('deleted_at')
            ->count();

        $deleted = (clone $base)
            ->whereNotNull('deleted_at')
            ->count();

        // TODAY INCREMENT
        $activeToday = (clone $base)
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->whereNull('deleted_at')
            ->where('is_archived', false)
            ->count();

        $archivedToday = (clone $base)
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->where('is_archived', true)
            ->whereNull('deleted_at')
            ->count();

        $deletedToday = (clone $base)
            ->whereBetween('deleted_at', [$todayStart, $todayEnd])
            ->count();

        $documentStats = [
            'active' => [
                'value'  => $active,
                'change' => $activeToday,
            ],
            'archived' => [
                'value'  => $archived,
                'change' => $archivedToday,
            ],
            'deleted' => [
                'value'  => $deleted,
                'change' => $deletedToday,
            ],
        ];

        /**
         * =========================
         * EXTENSION STATS
         * =========================
         */
        $mimeMap = [
            'application/pdf' => 'PDF',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'DOCX',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'XLSX',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'PPTX',
            'image/jpeg' => 'JPG',
            'image/png' => 'PNG',
        ];

        $extensions = ['PDF', 'DOCX', 'XLSX', 'PPTX', 'JPG', 'PNG'];

        $rowsQuery = Documents::whereNull('deleted_at');

        if (! $isAdmin) {
            $rowsQuery->where('user_id', $user->id);
        }

        $rows = $rowsQuery
            ->select(
                'mime_type',
                FacadesDB::raw('COUNT(*) as files'),
                FacadesDB::raw('SUM(size) as total_size')
            )
            ->groupBy('mime_type')
            ->get()
            ->keyBy('mime_type');

        $totalBytes = $rows->sum('total_size');

        $extensionStats = collect($extensions)->map(function ($ext) use (
            $rows,
            $mimeMap,
            $totalBytes
        ) {
            $mime = array_search($ext, $mimeMap, true);
            $row  = $mime && isset($rows[$mime]) ? $rows[$mime] : null;

            $bytes = $row?->total_size ?? 0;
            $size  = $this->formatSize((int) $bytes);

            return [
                'key'       => strtolower($ext),
                'extension' => $ext,
                'files'     => (int) ($row->files ?? 0),
                'size'      => $size['value'],
                'unit'      => $size['unit'],
                'percent'   => $totalBytes
                    ? round(($bytes / $totalBytes) * 100)
                    : 0,
            ];
        });

        /**
         * =========================
         * STORAGE TOTAL
         * =========================
         */
        $storage = $this->storage($user, $isAdmin);

        return Inertia::render('user/dashboard', [
            'documentStats'  => $documentStats,
            'extensionStats' => $extensionStats,
            'storage'        => $storage,
        ]);
    }

    private function formatSize(int $bytes): array
    {
        if ($bytes < 1024) {
            return ['value' => $bytes, 'unit' => 'B'];
        }

        if ($bytes < 1024 ** 2) {
            return ['value' => round($bytes / 1024, 2), 'unit' => 'KB'];
        }

        if ($bytes < 1024 ** 3) {
            return ['value' => round($bytes / 1024 ** 2, 2), 'unit' => 'MB'];
        }

        return ['value' => round($bytes / 1024 ** 3, 2), 'unit' => 'GB'];
    }

    private function storage($user, bool $isAdmin): array
    {
        $query = Documents::whereNull('deleted_at');

        if (! $isAdmin) {
            $query->where('user_id', $user->id);
        }

        $bytes = $query->sum('size');

        return $this->formatSize((int) $bytes);
    }
}
