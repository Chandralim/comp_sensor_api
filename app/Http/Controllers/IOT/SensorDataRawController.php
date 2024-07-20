<?php

namespace App\Http\Controllers\IOT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Helpers\MyLib;
use App\Exceptions\MyException;

use App\Model\AirLimbahFlowMeter;
use App\Http\Resources\AirLimbahFlowMeterResource;
use App\Http\Requests\AirLimbahFlowMeterRequest;
use App\Http\Requests\AirLimbahFlowMeterADCRequest;

// use App\Events\DashboardDataReceived;
use App\Events\SensorDataRawReceived;
use App\Exports\MyReport;
use App\Helpers\MyLog;
use App\Model\SensorList;
use App\Model\SensorDataRaw;
use Excel;
use DB;

class SensorDataRawController extends Controller
{
  public $sensor = "";

  public function __construct(Request $request)
  {
    $this->sensor = MyLib::sensorToken();

    // if (!$this->sensor->location) {
    //   throw new MyException(["message" => "Please set up location first"], 400);
    // }
  }

  // public function directInjectData(AirLimbahFlowMeterRequest $request)
  // {
  //   return $this->modbus($request, $request->total_val);
  // }

  // public function store(AirLimbahFlowMeterRequest $request)
  // {
  //   return $this->modbus($request, $request->total_val * 1000);
  // }

  // public function store(Request $request)
  // {
  //   $rules = [
  //     // 'created_at'              => 'required|date_format:Y-m-d H:i:s',
  //     'created_at'              => 'required|datetime',

  //     'sensor_list_id'          => 'required|exists:\App\Model\SensorList,id',
  //     'value'                   => 'required|numeric',
  //   ];

  //   $messages = [
  //     'created_at.required'     => 'Tanggal dan waktu diperlukan',
  //     'created_at.date_format'  => 'Format Tanggal dan waktu salah',

  //     'sensor_list_id.required' => 'Sensor List ID diperlukan',
  //     'sensor_list_id.exists'   => 'Sensor List ID tidak terdaftar',

  //     'value.required'          => 'Nilai Diperlukan',
  //     'value.numeric'           => 'Nilai harus berupa angka',
  //   ];

  //   $validator = \Validator::make($request->all(), $rules, $messages);

  //   if ($validator->fails())
  //     throw new ValidationException($validator);

  //   if (!SensorList::where("id", $request->sensor_list_id)->where('sensor_token_id', $this->sensor->id)->first())
  //     throw new MyException(["message" => "Sensor Not Match"], 401);

  //   $data = [
  //     "created_at"      => strtotime($request->created_at),
  //     "sensor_list_id"  => $request->sensor_list_id,
  //     "value"           => $request->value,
  //   ];

  //   broadcast(new SensorDataRawReceived($data));

  //   $model_query = new SensorDataRaw();
  //   if ($model_query->insert($data)) {
  //     return response()->json([
  //       "message" => "Proses tambah data berhasil",
  //     ], 200);
  //   }

  //   return response()->json([
  //     "message" => "Proses tambah data gagal"
  //   ], 400);
  // }

  public function store(Request $request)
  {
    $created_at = $request->created_at;
    if (!$created_at) {
      throw new MyException(["message" => "Created At Need To Fill"], 422);
    }

    $zone = "";
    $pattern_utc = "/^.{10}T.{8}Z$/i";
    $pattern_asia = "/^.{10}T.{8} 07:00$/i";
    if (preg_match($pattern_utc, $created_at)) {
      $zone = "UTC";
    } elseif (preg_match($pattern_asia, $created_at)) {
      $zone = "Jakarta";
      $created_at = str_replace(" ", "+", $created_at);
    } else {
      throw new MyException(["message" => "Created At Format Not Match"], 422);
    }

    // echo date("Y-m-d H:i:s", strtotime($created_at));
    $datetime = new \DateTime($created_at);
    $datetime->setTimezone(new \DateTimeZone('UTC'));
    // echo $datetime->format('Y-m-d H:i:s');

    $utc_millis = $datetime->getTimestamp() * 1000;
    $return = [];
    foreach ($request->all() as $k => $v) {
      $pattern = "/^snsr_/i";
      if (preg_match($pattern, $k)) {
        $uname = preg_replace($pattern, "", $k);
        $value = $v;

        $sl = SensorList::where('uname', $uname)->where("sensor_token_id", $this->sensor->id)->first();
        if ($sl) {
          $dt = [
            "created_at"      => $utc_millis,
            "sensor_list_id"  => $sl->id,
            "value"           => $value,
          ];
          SensorDataRaw::insert($dt);

          $dt['sensor_token_id'] = $this->sensor->id;
          array_push($return, $dt);
        }
      }
    }

    broadcast(new SensorDataRawReceived($return));

    return response()->json([
      "message" => "Proses tambah data berhasil",
    ], 200);
  }

  //     $utcMillis = 1678986600000;

  // $utcSeconds = $utcMillis / 1000;
  // $utcDateTime = date('Y-m-d H:i:s.', $utcSeconds);
  // $utcMicroseconds = $utcMillis % 1000;
  // $utcDateTime .= sprintf('%03d', $utcMicroseconds);

  // echo $utcDateTime; // Output: 2023-03-16 07:30:00.000


  // public function modbus(AirLimbahFlowMeterRequest $request, $total_val)
  // {
  //   $dt_total_val = 0;

  //   $flowmeter = \App\Model\AirLimbahFlowMeter::where("location_id", $this->sensor->location->id)->where("air_limbah_sensor_id", $this->sensor->id)->orderBy("created_at", "desc")->first();
  //   if ($flowmeter) {
  //     $dt_total_val = $flowmeter->total_val;
  //   }

  //   if ($total_val == 0) $total_val = $dt_total_val;
  //   $deviation = $total_val - $dt_total_val;
  //   $insert = [
  //     "air_limbah_sensor_id" => $this->sensor->id,
  //     "location_id" => $this->sensor->location->id,
  //     "real_time_val" => $request->real_time_val,
  //     "deviation" => $deviation,
  //     "total_val" => $total_val,
  //     "created_at" => MyLib::getMillis(),
  //   ];

  //   if (isset($request->electricity))
  //     $insert["electricity_is_off"] = $request->electricity == "PLN" ? 0 : 1;

  //   $toBroadCast = $insert;
  //   $toBroadCast["total_val"] /= 1000;

  //   broadcast(new DashboardDataReceived($toBroadCast));

  //   $model_query = new AirLimbahFlowMeter();
  //   if ($model_query->insert($insert)) {
  //     return response()->json([
  //       "message" => "Proses tambah data berhasil",
  //     ], 200);
  //   }
  //   return response()->json([
  //     "message" => "Proses tambah data gagal"
  //   ], 400);
  // }

  // public function storeADCBackup(AirLimbahFlowMeterADCRequest $request)
  // {
  //   $this->sensor = MyLib::airLimbahSensor();

  //   $real_time_val = 0;
  //   $total_val = 0;

  //   if(!$this->sensor->location){
  //     return response()->json([
  //       "message"=>"Please set up location first",
  //     ],400);
  //   }

  //   $flowmeter_u_id = 0;
  //   $flowmeter_deviation = 0;
  //   $flowmeter=\App\Model\AirLimbahFlowMeter::where("location_id",$this->sensor->location->id)->where("air_limbah_sensor_id",$this->sensor->id)->orderBy("created_at","desc")->first();
  //   if($flowmeter){
  //     $flowmeter_u_id = $flowmeter->u_id;
  //     $flowmeter_deviation = $flowmeter->deviation;
  //     $total_val = $flowmeter->total_val;
  //   }

  //   $real_time_val = $request->adc ;
  //   $u_id = (int) $request->u_id;

  //   $deviation = $request->point;
  //   if($flowmeter_u_id==$u_id){
  //     $total_val -= $flowmeter_deviation;
  //   }

  //   $total_val += $deviation;
  //   $insert=[
  //     "u_id"=>$u_id,
  //     "air_limbah_sensor_id"=>$this->sensor->id,
  //     "location_id"=>$this->sensor->location->id,
  //     "real_time_val"=>$real_time_val,
  //     "total_val"=>$total_val,
  //     "deviation"=>$deviation,
  //     "created_at"=>MyLib::getMillis()
  //   ];

  //   broadcast(new DashboardDataReceived($insert));

  //   $model_query=new AirLimbahFlowMeter();
  //   if ($model_query->insert($insert)) {      
  //     return response()->json([
  //         "message"=>"Proses tambah data berhasil",
  //     ],200);
  //   }

  //   return response()->json([
  //       "message"=>"Proses tambah data gagal"
  //   ],400);
  // }
}
