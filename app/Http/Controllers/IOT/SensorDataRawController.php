<?php

namespace App\Http\Controllers\IOT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Helpers\MyLib;
use App\Exceptions\MyException;

use App\Model\SensorList;
use App\Model\SensorDataRaw;
use Excel;
use Illuminate\Support\Facades\DB;


class SensorDataRawController extends Controller
{
  public $sensor = "";

  public function __construct(Request $request)
  {
    $this->sensor = MyLib::sensorToken();
  }

  public function store(Request $request)
  {
    DB::beginTransaction();
    try {
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

      $datetime = new \DateTime($created_at);
      $datetime->setTimezone(new \DateTimeZone('UTC'));

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

            if (!SensorDataRaw::where("created_at", $utc_millis)->where("sensor_list_id", $sl->id)->where("value", $value)->first())
              SensorDataRaw::insert($dt);

            $dt['sensor_token_id'] = $this->sensor->id;
            $dt['sensor_list_name'] = $sl->name;
            array_push($return, $dt);
          }
        }
      }


      DB::commit();
      return response()->json([
        "message" => "Proses tambah data berhasil",
      ], 200);
    } catch (\Exception $e) {
      DB::rollback();

      if ($e->getCode() == 1) {
        return response()->json([
          "message" => $e->getMessage(),
        ], 400);
      }

      return response()->json([
        "message" => "Proses tambah data gagal"
      ], 400);
    }
  }
}
