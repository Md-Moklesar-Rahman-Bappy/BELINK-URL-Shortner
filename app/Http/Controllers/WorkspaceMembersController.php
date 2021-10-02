<?php

namespace App\Http\Controllers;

use App\Actions\Workspace\JoinWorkspace;
use App\User;
use App\Workspace;
use App\WorkspaceInvite;
use App\WorkspaceMember;
use Auth;
use Common\Core\BaseController;
use Illuminate\Http\Request;
use Session;
use const App\Providers\WORKSPACED_RESOURCES;

class WorkspaceMembersController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var User
     */
    private $user;

    public function __construct(
        Request $request,
        User $user
    ) {
        $this->request = $request;
        $this->user = $user;
    }

    public function join(WorkspaceInvite $workspaceInvite)
    {
        if ($user = Auth::user()) {
            app(JoinWorkspace::class)->execute($workspaceInvite, $user);
            if ($this->request->expectsJson()) {
                return $this->success(['workspace' => $workspaceInvite->workspace->loadCount('members')]);
            } else {
                return redirect('/dashboard');
            }
        } else {
            Session::put('workspaceInvite', $workspaceInvite->id);
            if (User::where('email', $workspaceInvite->email)->exists()) {
                return redirect("workspace/join/login?email={$workspaceInvite->email}");
            } else {
                return redirect("workspace/join/register?email={$workspaceInvite->email}");
            }
        }
    }

    public function destroy(Workspace $workspace, int $userId) {

        $this->authorize('destroy', [WorkspaceMember::class, $workspace, $userId]);

        // transfer workspace resources to owner
        if ($workspace->owner_id !== $userId) {
            foreach (WORKSPACED_RESOURCES as $model) {
                app($model)
                    ->where('workspace_id', $workspace->id)
                    ->where('user_id', $userId)
                    ->update(['user_id' => $workspace->owner_id]);
            }
        }

        app(WorkspaceMember::class)
            ->where('workspace_id', $workspace->id)
            ->where('user_id', $userId)
            ->delete();

        return $this->success();
    }

    public function changeRole(Workspace $workspace, int $memberId)
    {
        $this->authorize('update', [WorkspaceMember::class, $workspace]);

        $validatedData = $this->request->validate([
            'roleId' => 'required|integer'
        ]);

        app(WorkspaceMember::class)
            ->where('id', $memberId)
            ->update(['role_id' => $validatedData['roleId']]);

        return $this->success();
    }
}
