<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Helpers\MyLib;
use App\Exceptions\MyException;

use App\Model\SensorToken;


class GetJSONController extends Controller
{
  public $sensor = "";

  public function __construct(Request $request)
  {

    $token = $request->bearerToken();

    if ($token == "")
      throw new MyException(["message" => "Butuh Token"], 401);

    if ($token != env('STOKEN'))
      throw new MyException(["message" => "Token Tidak Cocok"], 400);
  }

  public function store(Request $request)
  {
    try {
      $start_datetime = $request->tanggalwaktu_mulai;
      $end_datetime = $request->tanggalwaktu_akhir;
      if (!$start_datetime) {
        throw new MyException(["message" => "Tanggal Waktu Mulai harus diisi"], 422);
      }

      if (!$end_datetime) {
        throw new MyException(["message" => "Tanggal Waktu Akhir harus diisi"], 422);
      }

      $pattern_asia = "/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/i";

      if (!preg_match($pattern_asia, $start_datetime))
        throw new MyException(["message" => "Format Tanggal Waktu Mulai Salah"], 422);

      if (!preg_match($pattern_asia, $end_datetime))
        throw new MyException(["message" => "Format Tanggal Waktu Akhir Salah"], 422);


      // echo date("Y-m-d H:i:s", strtotime($created_at));
      $sd = new \DateTime($start_datetime);
      $sd->sub(new \DateInterval('PT7H'));

      $ed = new \DateTime($end_datetime);
      $ed->sub(new \DateInterval('PT7H'));

      $sd_utc_millis = $sd->getTimestamp() * 1000;

      $ed_utc_millis = $ed->getTimestamp() * 1000;

      $sensors = SensorToken::with(['sensor_lists' => function ($q) use ($sd_utc_millis, $ed_utc_millis) {
        $q->with(['sensor_datas' => function ($q2) use ($sd_utc_millis, $ed_utc_millis) {
          $q2->where('created_at', ">=", $sd_utc_millis)->where('created_at', "<=", $ed_utc_millis)->orderBy("created_at", "asc");
        }]);
      }])->whereIn("id", [1, 2])->get();

      $data = [];
      foreach ($sensors as $k => $ss) {
        $dt = [];

        foreach ($ss->sensor_lists as $k1 => $sl) {
          $dt2 = [];

          foreach ($sl->sensor_datas as $k2 => $sds) {
            array_push($dt2, [
              // "rawcreated_at" => $sds->created_at,
              "created_at" => MyLib::millisToDateLocal($sds->created_at),
              "value" => $sds->value
            ]);
          }

          array_push($dt, [
            "name" => $sl->name,
            "unit_name" => $sl->unit_name,
            "data_sensor" => $dt2
          ]);
        }

        $lvl1 = [
          "name" => $ss->name,
          "daftar_sensor" => $dt
        ];

        array_push($data, $lvl1);
      }

      return response()->json(["data" => $data], 200);
      // return response()->json(["data" => $sensors], 200);
    } catch (\Exception $e) {
      // return response()->json([
      //   "message" => $e->getMessage(),
      // ], 400);

      if ($e->getCode() == 1) {
        return response()->json([
          "message" => $e->getMessage(),
        ], 400);
      }

      return response()->json([
        "message" => "Proses ambil data gagal"
      ], 400);
    }
  }
}
