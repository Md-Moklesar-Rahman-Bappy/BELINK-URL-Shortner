<?php

namespace App\Actions;

use Illuminate\Http\Request;
use Common\Core\Bootstrap\BaseBootstrapData;

class AppBootstrapData extends BaseBootstrapData
{
    public function init()
    {
        parent::init();
        $this->data['linkResponse'] = app(Request::class)->route('linkResponse');
        $this->data['linkGroupResponse'] = app(Request::class)->route('linkGroupResponse');
        return $this;
    }
}
