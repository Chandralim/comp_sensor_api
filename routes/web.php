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


// Route::post('/air_limbah_flowmeter_adc', 'IOT\AirLimbahFlowMeterController@storeADC');
// Route::post('/air_limbah_flowmeter', 'IOT\AirLimbahFlowMeterController@store');
// Route::post('/air_limbah_direct_inject', 'IOT\AirLimbahFlowMeterController@directInjectData');

Route::post('/send_sensor_data', 'IOT\SensorDataRawController@store');

// Route::prefix("/")->group(function () {
Route::middleware('auth:internal')->group(function () {
  // Route::get('/dashboards', 'DashboardController@index');
  // Route::get('/dashboards/download', 'DashboardController@download');
  // Route::get('/dashboard', 'DashboardController@show');
  // Route::put('/dashboard', 'DashboardController@update');
  // Route::get('/dashboard/detail', 'DashboardController@getDetail');

  Route::get('/dashboards/getSensorData', 'DashboardController@getSensorData');
  Route::get('/dashboard/detail/period_data', 'DashboardController@detailPeriodData');
  Route::get('/dashboard/detail/period_data/download', 'DashboardController@detailPeriodDataDownload');

  // Route::get('/dashboards/getAirLimbahSensorData', 'DashboardController@getAirLimbahSensorData');
  // Route::get('/dashboards/getAirLimbahCenterSensorData', 'DashboardController@getAirLimbahCenterSensorData');


  Route::get('/invoices', 'InvoiceController@index');
  Route::get('/invoice', 'InvoiceController@download');

  Route::get('/dashboard/detail/graph/air_limbah', 'DashboardController@detailGraphAirLimbah');
  Route::get('/dashboard/detail/history/air_limbah', 'DashboardController@detailHistoryAirLimbah');
  Route::get('/dashboard/detail/history/air_limbah/download', 'DashboardController@detailHistoryAirLimbahDownload');

  Route::post('/admin/logout', 'AdminAccount@logout');
  Route::get('/admin/getInfo', 'AdminAccount@getInfo');
  Route::put('/admin/change_password', 'AdminAccount@change_password');
  Route::put('/admin/change_fullname', 'AdminAccount@change_fullname');


  Route::get('/users', 'AdminController@index');
  Route::get('/user', 'AdminController@show');
  Route::post('/user', 'AdminController@store');
  Route::put('/user', 'AdminController@update');



  Route::get('/locations', 'LocationController@index');
  Route::get('/locations/download', 'LocationController@download');
  Route::get('/location', 'LocationController@show');
  Route::post('/location', 'LocationController@store');
  Route::put('/location', 'LocationController@update');

  Route::get('/air_limbah_sensors', 'AirLimbahSensorController@index');
  Route::get('/air_limbah_sensors/download', 'AirLimbahSensorController@download');
  Route::get('/air_limbah_sensor', 'AirLimbahSensorController@show');
  Route::post('/air_limbah_sensor', 'AirLimbahSensorController@store');
  Route::put('/air_limbah_sensor', 'AirLimbahSensorController@update');
});


Route::get('/', function () {
  return '404 Not Found';
});
