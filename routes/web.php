<?php

Route::group(['prefix' => 'secure'], function () {
    // BOOTSTRAP
    Route::get('bootstrap-data', '\Common\Core\Controllers\BootstrapController@getBootstrapData')->middleware('redirectLink');

    // HOMEPAGE STATS
    Route::get('homepage/stats', 'HomepageStatsController@getStats');

    // LINK
    Route::get('link/reports', 'LinkReportsController@show')->middleware('auth');
    Route::get('link/usage', 'LinkUsageController@getUsage')->middleware('auth');
    Route::apiResource('link', 'LinkController');

    // LINK GROUP
    Route::apiResource('link-group', 'LinkGroupController');
    Route::get('link-group/{linkGroup}/links', 'LinkGroupController@links');
    Route::get('link-group/{linkGroup}/analytics', 'LinkGroupController@analytics');
    Route::post('link-group/{linkGroup}/detach', 'LinkGroupAttachmentsController@detach');
    Route::post('link-group/{linkGroup}/attach', 'LinkGroupAttachmentsController@attach');

    // LINK OVERLAY
    Route::apiResource('link-overlay', 'LinkOverlayController');

    // TRACKING PIXEL
    Route::apiResource('tracking-pixel', 'TrackingPixelController');

    // LINK PAGES
    Route::apiResource('link-page', 'LinkPagesController');

    // WORKSPACE
    Route::apiResource('workspace', 'WorkspaceController');
    Route::get('workspace/join/{workspaceInvite}', 'WorkspaceMembersController@join');
    Route::delete('workspace/{workspace}/member/{userId}', 'WorkspaceMembersController@destroy');
    Route::post('workspace/{workspace}/invite', 'WorkspaceInvitesController@store');
    Route::post('workspace/{workspace}/{workspaceInvite}/resend', 'WorkspaceInvitesController@resend');
    Route::post('workspace/{workspace}/member/{memberId}/change-role', 'WorkspaceMembersController@changeRole');
    Route::post('workspace/{workspace}/invite/{inviteId}/change-role', 'WorkspaceInvitesController@changeRole');
    Route::delete('workspace/invite/{workspaceInvite}', 'WorkspaceInvitesController@destroy');
});

Route::get('{linkHash}/qr', 'QrCodeController@show');
Route::get('{linkHash}/img', 'LinkImageController@show');

//FRONT-END ROUTES THAT NEED TO BE PRE-RENDERED
Route::get('/', '\Common\Core\Controllers\HomeController@show')
    ->middleware('prerenderIfCrawler:homepage');

// CATCH ALL ROUTES AND REDIRECT TO HOME
Route::get('{all}', '\Common\Core\Controllers\HomeController@show')
    ->where('all', '.*')
    ->middleware('prerenderIfCrawler:homepage')
    ->middleware('redirectLink');
