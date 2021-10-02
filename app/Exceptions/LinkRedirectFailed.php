<?php

namespace App\Exceptions;

use App\Link;
use Exception;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Support\Responsable;

class LinkRedirectFailed extends Exception implements Responsable
{
    /**
     * @var Link
     */
    protected $link;

    /**
     * @inheritDoc
     */
    public function toResponse($request)
    {
        if (app(Gate::class)->authorize('show', $this->link)) {
            return response()->view(
                'redirects/redirect-error',
                ['message' => $this->getMessage()],
                403
            );
        } else {
            abort(404);
        }
    }

    public function setLink(Link $link)
    {
        $this->link = $link;
        return $this;
    }
}
