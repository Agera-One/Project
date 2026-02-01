<?php

namespace App\Http\Controllers\List;

use App\Http\Controllers\Base\BaseDocumentListController;
use Illuminate\Http\Request;

class RecentlyController extends BaseDocumentListController
{
    public function __invoke(Request $request)
    {
        $query = $this->baseQuery($request)
            ->whereNull('deleted_at')
            ->where('is_archived', false)
            ->where('updated_at', '>=', now()->subDays(30));

        return $this->render(
            $query,
            'user/recently',
            $request
        );
    }
}
