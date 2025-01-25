<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Helpers\MyLib;
use App\Exceptions\MyException;

use App\Exports\MyReport;
use Excel;
use DB;
use App\Model\SensorDataRaw;
use App\Model\SensorList;
use App\Model\SensorToken;


class DashboardController extends Controller
{

  public $admin = "";

  public function __construct(Request $request) {}


  public function getSensorData(Request $request)
  {

    $data = [];
    $sensor_token = SensorToken::get();
    if (count($sensor_token) > 0) {
      foreach ($sensor_token as $k => $v) {
        $dt = [
          "sensor_token_id"     => $v->id,
          "sensor_token_name"   => $v->name,
          "lists"               => []
        ];

        $sensor_lists = SensorList::where("sensor_token_id", $v->id)->orderBy("id", "asc")->get();
        if (count($sensor_lists) > 0) {
          foreach ($sensor_lists as $k1 => $v1) {
            $dt2 = [
              "sensor_list_id"        => $v1->id,
              "sensor_list_uname"     => $v1->uname,
              "sensor_list_name"      => $v1->name,
              "sensor_list_unit_name" => $v1->unit_name,
            ];

            $sdr = SensorDataRaw::where("sensor_list_id", $v1->id)->orderBy('created_at', 'desc')->first();
            if ($sdr) {
              $dt2["created_at"] = $sdr->created_at;
              $dt2["value"] = $sdr->value;
            } else {
              $dt2["created_at"] = '';
              $dt2["value"] = '';
            }
            array_push($dt["lists"], $dt2);
          }
        }
        array_push($data, $dt);
      }
    }

    return response()->json($data, 200);




    $data = SensorDataRaw::rightJoin(
      DB::raw("( select sensor_list_id as sli,max(created_at) as ca from sensor_data_raws group by sensor_list_id) as a"),
      function ($join) {
        $join->on("sensor_data_raws.sensor_list_id", "a.sli");
        $join->on("sensor_data_raws.created_at", "a.ca");
      }
    )
      ->select('sensor_data_raws.sensor_list_id', "sensor_data_raws.created_at", "sensor_data_raws.value")
      ->get();

    return response()->json($data, 200);
  }

  public function detailPeriodData(Request $request)
  {
    // dual mode
    $this->admin = MyLib::admin();

    $rules = [
      '_TimeZoneOffset' => "required|numeric",
      'date_from' => "required|date_format:Y-m-d",
      'date_to' => "required|date_format:Y-m-d",
    ];

    $messages = [
      'date_from.required' => 'Period Start is required',
      'date_from.date_format' => 'Please Select Period Start',

      'date_to.required' => 'Period End is required',
      'date_to.date_format' => 'Please Select Period End',
    ];

    $validator = \Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
      throw new ValidationException($validator);
    }

    $tz = $request->_TimeZoneOffset;

    $date_from = $request->date_from;
    $date_to = $request->date_to;

    $date_from = $date_from . " 07:00:00";
    $date_to = $date_to . " 07:00:00";

    $mod_date_to = new \DateTime($date_to);
    $mod_date_to->add(new \DateInterval('P1D'));

    $utc_date_from = MyLib::local_to_utc($tz, $date_from);
    $utc_date_to = MyLib::local_to_utc($tz, $mod_date_to->format("Y-m-d H:i:s"));

    $sensor_token_id = $request->sensor_token_id;

    $data = SensorToken::with(['sensor_lists' => function ($q) use ($utc_date_from, $utc_date_to) {
      $q->with(['sensor_datas' => function ($q2) use ($utc_date_from, $utc_date_to) {
        $q2->where('created_at', ">=", $utc_date_from)->where('created_at', "<", $utc_date_to)->orderBy("created_at", "asc");
      }]);
    }])->whereIn("id", [1, 2])->get();

    return response()->json(["data" => $data], 200);
  }

  public function detailPeriodDataDownload(Request $request)
  {
    // dual mode
    try {
      $this->admin = MyLib::admin();

      $rules = [
        '_TimeZoneOffset' => "required|numeric",
        'periodic' => "required|date_format:Y-m-d",
      ];

      $messages = [
        'periodic.required' => 'Periodic is required',
        'periodic.date_format' => 'Please Select Periodic',
      ];

      $validator = \Validator::make($request->all(), $rules, $messages);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $tz = $request->_TimeZoneOffset;

      $date_from = new \DateTime($request->periodic . "-01");

      $date_to = clone ($date_from);
      $date_to->add(new \DateInterval('P1D'));
      $date_to = $date_to->format('Y-m-d');
      $date_from = $date_from->format('Y-m-d');


      $date_from = $date_from . " 00:00:00";
      $date_to = $date_to . " 00:00:00";

      $utc_date_from = MyLib::local_to_utc($tz, $date_from);
      $utc_date_to = MyLib::local_to_utc($tz, $date_to);

      # code...
      $myData = [];

      $dDate = $date_from;
      while ($dDate != $date_to) {
        $md = [
          "utc_from" => MyLib::local_to_utc($tz, $dDate),
          "date_from" => $dDate,
        ];

        $dDate = new \DateTime($dDate);
        $dDate->add(new \DateInterval('PT30M'));
        $dDate = $dDate->format("Y-m-d H:i:s");

        $md["utc_to"] = MyLib::local_to_utc($tz, $dDate);
        array_push($myData, $md);
      }

      $d1millis = 1000 * 60 * 30;

      $datas = SensorToken::with(['sensor_lists' => function ($q) use ($utc_date_from, $utc_date_to, $d1millis) {
        $q->with(['sensor_datas' => function ($q2) use ($utc_date_from, $utc_date_to, $d1millis) {
          $q2->where('created_at', ">=", $utc_date_from - $d1millis)->where('created_at', "<", $utc_date_to)->orderBy("created_at", "asc");
        }]);
      }])->whereIn("id", [1, 2])->get();

      $output_data = [];
      $info = [];
      foreach ($datas as $kex => $data) {
        array_push($info, strtoupper($data->name));
        $sensor_lists = $data->sensor_lists->toArray();
        array_push($output_data, $sensor_lists);
        foreach ($output_data[$kex] as $k => $v) {

          $output_data[$kex][$k]["sensor_postVal"] = [];

          foreach ($myData as $k1 => $v1) {

            $af = array_values(array_filter($v['sensor_datas'], function ($x) use ($v1, $d1millis) {
              return $x['created_at'] >= ($v1['utc_from'] - $d1millis) && $x['created_at'] < ($v1['utc_to'] - $d1millis);
            }));

            $result = 0;
            if (count($af) == 0) {
            } elseif (count($af) == 1 && $v['type'] == 'inc') {
              $result = $af[0]['value'];
            } elseif (count($af) == 1 && $v['type'] == 'ran') {
              $result = $af[0]['value'];
            } elseif ($v['type'] == 'inc') {
              $result = $af[count($af) - 1]['value'];
            } elseif ($v['type'] == 'ran') {
              $result = $af[count($af) - 1]['value'];
            }

            array_push($output_data[$kex][$k]["sensor_postVal"], $result);
          }
        }
      }

      $date = new \DateTime();
      $filename = $date->format("YmdHis") . "-" . "[" . $request->periodic . "]";
      $mime = MyLib::mime("xls");

      $bs64 = base64_encode(Excel::raw(new MyReport(["myData" => $myData, "sensor_lists" => $output_data, "info" => $info], 'report.sensor_data'), $mime["exportType"]));

      $result = [
        "contentType" => $mime["contentType"],
        "data" => $bs64,
        "dataBase64" => $mime["dataBase64"] . $bs64,
        "filename" => $filename . "." . $mime["ext"]
      ];
      return $result;
    } catch (\Exception $e) {
      return response()->json([
        "message" => "error",
      ], 400);
    }
  }
}
