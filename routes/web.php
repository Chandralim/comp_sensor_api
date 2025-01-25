<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });



Route::post('/admin/login', 'AdminAccount@login');
Route::post('/admin/refresh', 'AdminAccount@refresh');

Route::post('/send_sensor_data', 'IOT\SensorDataRawController@store');

Route::post('/tarik-data', 'GetJSONController@store');

Route::middleware('auth:internal')->group(function () {
  Route::get('/dashboards/getSensorData', 'DashboardController@getSensorData');
  Route::get('/dashboard/detail/period_data', 'DashboardController@detailPeriodData');
  Route::get('/dashboard/detail/period_data/download', 'DashboardController@detailPeriodDataDownload');

  Route::post('/admin/logout', 'AdminAccount@logout');
  Route::get('/admin/getInfo', 'AdminAccount@getInfo');
  Route::put('/admin/change_password', 'AdminAccount@change_password');
  Route::put('/admin/change_fullname', 'AdminAccount@change_fullname');

  Route::get('/users', 'AdminController@index');
  Route::get('/user', 'AdminController@show');
  Route::post('/user', 'AdminController@store');
  Route::put('/user', 'AdminController@update');
});


Route::get('/', function () {
  return '404 Not Found';
});
