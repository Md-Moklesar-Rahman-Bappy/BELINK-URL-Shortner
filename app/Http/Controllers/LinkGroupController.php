<?php

namespace App\Http\Controllers;

use App\Actions\Link\GenerateLinkReport;
use App\LinkGroup;
use App\Rules\UniqueWorkspacedResource;
use Auth;
use Common\Core\BaseController;
use Common\Database\Paginator;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class LinkGroupController extends BaseController
{
    /**
     * @var LinkGroup
     */
    private $linkGroup;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param LinkGroup $linkGroup
     * @param Request $request
     */
    public function __construct(LinkGroup $linkGroup, Request $request)
    {
        $this->linkGroup = $linkGroup;
        $this->request = $request;
    }

    /**
     * @return Response
     */
    public function index()
    {
        $userId = $this->request->get('userId');
        $params = $this->request->all();
        $this->authorize('index', [LinkGroup::class, $userId]);

        $paginator = new Paginator($this->linkGroup, $params);
        $paginator->withCount('links');

        if ($userId) {
            $paginator->where('user_id', $userId);
        } else {
            $paginator->with('user');
        }

        return $this->success(
            ['pagination' => $paginator->paginate()]
        );
    }

    /**
     * @param LinkGroup $linkGroup
     * @return Response
     */
    public function show(LinkGroup $linkGroup)
    {
        $this->authorize('show', $linkGroup);

        return $this->success(['linkGroup' => $linkGroup]);
    }

    /**
     * @return Response
     */
    public function store()
    {
        $this->authorize('store', LinkGroup::class);

        $this->validate($this->request, [
            'name' => [
                'required', 'min:3', 'max:250',
                new UniqueWorkspacedResource('link_groups'),
            ],
            'hash' => 'required|min:3|max:10|unique:link_groups|unique:links',
        ]);

        $group = $this->linkGroup->create([
            'name' => $this->request->get('name'),
            'description' => $this->request->get('description'),
            'public' => $this->request->get('public'),
            'hash' => $this->request->get('hash'),
            'user_id' => Auth::id(),
            'rotator' => $this->request->get('rotator', false),
        ]);

        return $this->success(['group' => $group]);
    }

    /**
     * @param LinkGroup $linkGroup
     * @return Response
     */
    public function update(LinkGroup $linkGroup)
    {
       $this->authorize('update', $linkGroup);

        $this->validate($this->request, [
            'name' => [
                'required', 'min:3', 'max:250',
                (new UniqueWorkspacedResource('link_groups'))
                    ->ignore($linkGroup->id)
            ],
            'hash' => ['required', 'min:3', 'max:10', 'unique:links', Rule::unique('link_groups')->ignore($linkGroup->id)],
        ]);

        $linkGroup->fill([
            'name' => $this->request->get('name'),
            'description' => $this->request->get('description'),
            'public' => $this->request->get('public'),
            'hash' => $this->request->get('hash'),
            'rotator' => $this->request->get('rotator', false),
        ])->save();

        return $this->success(['group' => $linkGroup]);
    }

    /**
     * @param string $ids
     * @return Response
     */
    public function destroy($ids)
    {
        $groupIds = explode(',', $ids);
        $this->authorize('destroy', [LinkGroup::class, $groupIds]);

        $this->linkGroup->whereIn('id', $groupIds)->delete();
        DB::table('link_group_link')->whereIn('link_group_id', $groupIds)->delete();

        return $this->success();
    }

    public function links(LinkGroup $linkGroup)
    {
        $this->authorize('show', $linkGroup);

        $params = $this->request->all();

        $paginator = new Paginator($linkGroup->links(), $params);
        $paginator->with(['rules', 'tags', 'pixels', 'domain', 'user', 'groups']);
        return $this->success([
            'linkGroup' => $linkGroup,
            'pagination' => $paginator->paginate()
        ]);
    }

    public function analytics(LinkGroup $linkGroup)
    {
        $this->authorize('show', $linkGroup);

        return $this->success([
            'linkGroup' => $linkGroup,
            'analytics' => app(GenerateLinkReport::class)
                ->execute($this->request->all(), $linkGroup),
        ]);
    }
}
