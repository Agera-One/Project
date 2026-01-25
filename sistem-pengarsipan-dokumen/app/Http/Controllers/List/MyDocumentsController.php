<?php

namespace App\Http\Controllers\List;

use App\Http\Controllers\Base\BaseDocumentListController;
use Illuminate\Http\Request;

class MyDocumentsController extends BaseDocumentListController
{
    public function __invoke(Request $request)
    {
        $query = $this->baseQuery($request)
            ->whereNull('deleted_at')
            ->where('is_archived', false);

        return $this->render(
            $query,
            'my-documents',
            $request
        );
    }
}
