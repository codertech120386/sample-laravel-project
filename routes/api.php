<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('seed-workspaces', 'WorkspaceController@seed');
Route::post('search', 'WorkspaceController@search');
Route::post('signup', 'UserController@signup');
Route::post('login', 'UserController@login');
Route::post('get-workspace', 'WorkspaceController@get_workspace');
Route::post('get-workspace-details', 'WorkspaceController@get_workspace_details');

Route::post('order', 'PaymentController@store');
Route::post('update-order', 'PaymentController@update_order');
Route::post('user_subscriptions', 'PaymentController@get_user_subscriptions');

Route::post('get_user', 'UserController@get_user');
Route::post('update-subscription', 'SubscriptionController@update_subscription');

Route::get('paytm/request', 'PaytmController@getRequest');
Route::post('paytm/request', 'PaytmController@postRequest');
Route::post('paytm/response', 'PaytmController@postResponse');

Broadcast::routes(['middleware' => ['auth:sanctum']]);
