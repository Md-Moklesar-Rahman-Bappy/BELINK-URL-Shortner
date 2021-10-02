<?php

namespace App\Http\Controllers;

use App\Actions\Link\GenerateLinkReport;
use App\ActiveWorkspace;
use Common\Core\BaseController;
use Illuminate\Http\Request;

class LinkReportsController extends BaseController
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ActiveWorkspace
     */
    private $activeWorkspace;

    public function __construct(Request $request, ActiveWorkspace $activeWorkspace)
    {
        $this->request = $request;
        $this->activeWorkspace = $activeWorkspace;
    }

    public function show()
    {
        $reports = app(GenerateLinkReport::class)
            ->execute($this->request->all(), $this->activeWorkspace->modelForLinkClickQuery());

        return $this->success(['reports' => $reports]);
    }
}
