<?php

namespace App\Http\Controllers\List;

use App\Http\Controllers\Base\BaseDocumentListController;
use Illuminate\Http\Request;

class StarredController extends BaseDocumentListController
{
    public function __invoke(Request $request)
    {
        $query = $this->baseQuery($request)
            ->whereNull('deleted_at')
            ->where('is_archived', false)
            ->where('is_starred', true);

        return $this->render(
            $query,
            'starred',
            $request
        );
    }
}
