<?php

namespace App\Http\Controllers;

use App\Actions\Link\GetMonthlyClicks;
use App\ActiveWorkspace;
use Common\Core\BaseController;
use Common\Core\Exceptions\AccessResponseWithAction;
use Illuminate\Contracts\Auth\Access\Gate;
use const App\Providers\WORKSPACED_RESOURCES;

class LinkUsageController extends BaseController
{
    /**
     * @var ActiveWorkspace
     */
    private $activeWorkspace;

    public function __construct(ActiveWorkspace $activeWorkspace)
    {
        $this->activeWorkspace = $activeWorkspace;
    }

    public function getUsage()
    {
        $usage = collect(WORKSPACED_RESOURCES)->mapWithKeys(function($resource) {
            $table = app($resource)->getTable();
            $maxCount = $this->activeWorkspace->getRestrictionValue("$table.create", 'count');
            $used =  $this->activeWorkspace->getResourceCount($resource);
            $response = app(Gate::class)->inspect('store', $resource);
            return [app($resource)->getTable() => [
                'used' => $used,
                'total' => $maxCount,
                'canCreate' => $response->allowed(),
                'canDelete' => app(Gate::class)->allows('destroy', $resource),
                'canEdit' => app(Gate::class)->allows('update', new $resource),
                'createFailType' => $this->getFailType($response),
            ]];
        })->filter();

        $clickMaxCount = $this->activeWorkspace->getRestrictionValue('links.create', 'click_count');
        $usage['link_clicks'] = [
            'used' => app(GetMonthlyClicks::class)->execute($this->activeWorkspace->modelForLinkClickQuery()),
            'total' => $clickMaxCount,
        ];

        $response = [
            'forWorkspace' => !$this->activeWorkspace->personal(),
            'userOwnsWorkspace' => $this->activeWorkspace->currentUserIsOwner(),
            'usage' => $usage,
        ];

        return $this->success($response);
    }

    private function getFailType($response)
    {
        if ($response->allowed()) {
            return null;
        } else if ($response instanceof AccessResponseWithAction) {
            return 'overQuota';
        } else {
            return 'noPermission';
        }
    }
}
