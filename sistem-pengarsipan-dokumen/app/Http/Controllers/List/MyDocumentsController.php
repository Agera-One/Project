<?php

namespace App\Http\Controllers\List;

use App\Http\Controllers\Base\BaseDocumentListController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MyDocumentsController extends BaseDocumentListController
{
    public function __invoke(Request $request)
    {
        $query = $this->baseQuery($request)
            ->whereNull('deleted_at');

        return $this->render(
            $query,
            'my-documents',
            $request
        );
    }
}
