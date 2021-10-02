<?php

namespace App\Providers;

use App\Actions\Admin\GetAnalyticsHeaderData;
use App\Actions\Admin\GetAppAnalyticsData;
use App\Actions\AppBootstrapData;
use App\Actions\AppValueLists;
use App\Link;
use App\LinkDomain;
use App\LinkGroup;
use App\LinkOverlay;
use App\LinkPage;
use App\TrackingPixel;
use Common\Admin\Analytics\Actions\GetAnalyticsData;
use Common\Admin\Analytics\Actions\GetAnalyticsHeaderDataAction;
use Common\Core\Bootstrap\BootstrapData;
use Common\Core\Values\ValueLists;
use Common\Domains\CustomDomain;
use Common\Pages\CustomPage;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

const WORKSPACED_RESOURCES = [
    LinkPage::class, Link::class, LinkGroup::class,
    LinkOverlay::class, TrackingPixel::class, LinkDomain::class
];

class AppServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        $this->app->bind(
            BootstrapData::class,
            AppBootstrapData::class
        );

        Relation::morphMap([
            CustomDomain::class => LinkDomain::class,
            CustomPage::class => LinkPage::class,
        ]);
    }

    /**
     * @return void
     */
    public function register()
    {
        // bind analytics
        $this->app->bind(
            GetAnalyticsHeaderDataAction::class,
            GetAnalyticsHeaderData::class
        );

        $this->app->bind(
            GetAnalyticsData::class,
            GetAppAnalyticsData::class
        );

        $this->app->bind(CustomDomain::class, LinkDomain::class);

//        $this->app->bind(
//            AppUrlGenerator::class,
//            UrlGenerator::class
//        );

        $this->app->bind(ValueLists::class, AppValueLists::class);
    }
}
