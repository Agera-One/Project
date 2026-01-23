<?php

namespace App\Http\Controllers\List;

use App\Http\Controllers\Base\BaseDocumentListController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TrashController extends BaseDocumentListController
{
    public function __invoke(Request $request)
    {
        $query = $this->baseQuery($request)
            ->onlyTrashed();

        return $this->render(
            $query,
            'trash',
            $request,
            'deleted_at'
        );
    }
}
