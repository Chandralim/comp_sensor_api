<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Helpers\MyLib;
use App\Exceptions\MyException;

use App\Model\AirLimbahFlowMeter;

use App\Exports\MyReport;
use Excel;
use DB;
use \App\Helpers\EzLog;
use App\Helpers\MyLog;
use App\Model\SensorDataDay;
use App\Model\SensorDataHour;
use App\Model\SensorDataMonth;
use App\Model\SensorDataRaw;
use App\Model\SensorDataYear;
use App\Model\SensorList;
use App\Model\SensorToken;


class DashboardController extends Controller
{
  private $Model = [];

  public $admin = "";

  public function __construct(Request $request)
  {
    $this->Model = [
      "Hourly" => new \App\Model\AirLimbahDataHour(),
      "Daily" => new \App\Model\AirLimbahDataDay(),
      "Monthly" => new \App\Model\AirLimbahDataMonth(),
      "Yearly" => new \App\Model\AirLimbahDataYear(),
    ];
  }

  // public function getSensorData1(Request $request)
  // {

  //   $this->admin = MyLib::admin();

  //   $waste_waters = [];
  //   $new_queries = DB::table("locations");
  //   // if($request->search_location_name){
  //   //   $new_queries->where("name","ilike","%".$request->search_location_name."%");
  //   // }    
  //   $new_queries->leftJoin("air_limbah_sensors", "air_limbah_sensors.location_id", "=", "locations.id");
  //   $new_queries->select(
  //     'locations.id as location_id',
  //     'locations.is_tenant as location_is_tenant',
  //     'locations.grup as location_grup',
  //     'locations.name as location_name',
  //     'air_limbah_sensors.coor_lon as sensor_coor_lon',
  //     'air_limbah_sensors.coor_lat as sensor_coor_lat',
  //     'air_limbah_sensors.id as sender_id',
  //     'air_limbah_sensors.qmax as flowmeter_qmax'
  //   );
  //   $new_queries->orderBy("location_name");
  //   $new_queries = $new_queries->get();

  //   foreach ($new_queries as $k => $v) {
  //     $alfm_temp = "";
  //     $alfm_last_change = "";
  //     $alfm = DB::table("air_limbah_data_realtimes")->where("sensor_id", $v->sender_id)->orderBy("created_at", "desc")->first();
  //     if ($alfm) {
  //       // $alfm_temp = DB::table("air_limbah_flow_meters")->where("air_limbah_sensor_id",$v->sender_id)->where("total_val","!=",$alfm->total_val)->orderBy("created_at","desc")->first();
  //       // if($alfm_temp)
  //       //   $alfm_last_change = DB::table("air_limbah_flow_meters")->where("air_limbah_sensor_id",$v->sender_id)->where("created_at",">",$alfm_temp->created_at)->orderBy("created_at","asc")->first();

  //       array_push($waste_waters, [
  //         "type" => "air_limbah",
  //         "location_is_tenant" => $v->location_is_tenant,
  //         "location_id" => $v->location_id,
  //         "location_grup" => $v->location_grup,
  //         "location_name" => $v->location_name,
  //         "sensor_coor_lon" => $v->sensor_coor_lon,
  //         "sensor_coor_lat" => $v->sensor_coor_lat,

  //         "sender_id" => $v->sender_id,
  //         "flowmeter_qmax" => $v->flowmeter_qmax,

  //         "flowmeter_flow_rate" => $alfm ? $alfm->flow_rate : 0,
  //         "flowmeter_totalizer" => $alfm ? ($alfm->totalizer / 1000) : 0,

  //         "record_created_at" => $alfm ? $alfm->created_at : 0,
  //         "record_last_change" => $alfm ? $alfm->before_change_created_at : 0,

  //         "electricity_is_off" => $alfm ? $alfm->electricity_is_off : 0,
  //         // "alfm_temp"=>$alfm_temp ? $alfm_temp->created_at : 0,
  //       ]);
  //     }
  //   }

  //   return response()->json([
  //     "waste_waters" => $waste_waters,
  //   ], 200);
  // }


  public function getSensorData(Request $request)
  {

    // $this->admin = MyLib::admin();

    $data = [];
    $sensor_token = SensorToken::get();
    if (count($sensor_token) > 0) {
      foreach ($sensor_token as $k => $v) {
        $dt = [
          "sensor_token_id"     => $v->id,
          "sensor_token_name"   => $v->name,
          "lists"               => []
        ];

        $sensor_lists = SensorList::where("sensor_token_id", $v->id)->get();
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

    $this->admin = MyLib::admin();

    $rules = [
      '_TimeZoneOffset' => "required|numeric",
      'date_from' => "required|date_format:Y-m-d",
      'date_to' => "required|date_format:Y-m-d",
      'sensor_token_id' => "required|exists:\App\Model\SensorToken,id",
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

    $date_from = $date_from . " 00:00:00";
    $date_to = $date_to . " 00:00:00";

    $mod_date_to = new \DateTime($date_to);
    $mod_date_to->add(new \DateInterval('P1D'));

    $utc_date_from = MyLib::local_to_utc($tz, $date_from);
    $utc_date_to = MyLib::local_to_utc($tz, $mod_date_to->format("Y-m-d H:i:s"));

    $sensor_token_id = $request->sensor_token_id;

    $data = SensorToken::with(['sensor_lists' => function ($q) use ($utc_date_from, $utc_date_to) {
      $q->with(['sensor_datas' => function ($q2) use ($utc_date_from, $utc_date_to) {
        $q2->where('created_at', ">=", $utc_date_from)->where('created_at', "<", $utc_date_to)->orderBy("created_at", "asc");
      }]);
    }])->find($sensor_token_id);


    return response()->json(["data" => $data], 200);
  }

  // public function detailPeriodDataDownload(Request $request)
  // {
  //   try {
  //     $this->admin = MyLib::admin();

  //     $rules = [
  //       '_TimeZoneOffset' => "required|numeric",
  //       'periodic' => "required|date_format:Y-m",
  //       'sensor_token_id' => "required|exists:\App\Model\SensorToken,id",
  //     ];

  //     $messages = [
  //       'periodic.required' => 'Periodic is required',
  //       'periodic.date_format' => 'Please Select Periodic',
  //     ];

  //     $validator = \Validator::make($request->all(), $rules, $messages);

  //     if ($validator->fails()) {
  //       throw new ValidationException($validator);
  //     }

  //     $tz = $request->_TimeZoneOffset;

  //     $date_from = new \DateTime($request->periodic . "-01");

  //     $date_to = clone ($date_from);
  //     $date_to->add(new \DateInterval('P1M'));
  //     $date_to = $date_to->format('Y-m-d');
  //     $date_from = $date_from->format('Y-m-d');


  //     $date_from = $date_from . " 00:00:00";
  //     $date_to = $date_to . " 00:00:00";

  //     $utc_date_from = MyLib::local_to_utc($tz, $date_from);
  //     $utc_date_to = MyLib::local_to_utc($tz, $date_to);

  //     $sensor_token_id = $request->sensor_token_id;

  //     $data = SensorToken::with(['sensor_lists' => function ($q) use ($utc_date_from, $utc_date_to) {
  //       $q->with(['sensor_datas' => function ($q2) use ($utc_date_from, $utc_date_to) {
  //         $q2->where('created_at', ">=", $utc_date_from)->where('created_at', "<", $utc_date_to)->orderBy("created_at", "asc");
  //       }]);
  //     }])->find($sensor_token_id);

  //     $info = [
  //       "name" => $data->name,
  //     ];

  //     $myData = [];

  //     $dDate = $date_from;
  //     while ($dDate != $date_to) {
  //       $md = [
  //         "utc_from" => MyLib::local_to_utc($tz, $dDate),
  //         "date_from" => $dDate,
  //       ];

  //       $dDate = new \DateTime($dDate);
  //       $dDate->add(new \DateInterval('P1D'));
  //       $dDate = $dDate->format("Y-m-d H:i:s");

  //       $md["utc_to"] = MyLib::local_to_utc($tz, $dDate);
  //       array_push($myData, $md);
  //     }

  //     $sensor_lists = $data->sensor_lists->toArray();
  //     foreach ($sensor_lists as $k => $v) {

  //       $sensor_lists[$k]["sensor_postVal"] = [];

  //       foreach ($myData as $k1 => $v1) {

  //         $af = array_values(array_filter($v['sensor_datas'], function ($x) use ($v1) {
  //           return $x['created_at'] >= $v1['utc_from'] && $x['created_at'] < $v1['utc_to'];
  //         }));

  //         $result = 0;
  //         if (count($af) == 0) {
  //         } elseif (count($af) == 1 && $v['type'] == 'inc') {
  //         } elseif (count($af) == 1 && $v['type'] == 'ran') {
  //         } elseif ($v['type'] == 'inc') {
  //           $result = ($af[count($af) - 1]['value'] - $af[0]['value']) / 2;
  //         } elseif ($v['type'] == 'ran') {
  //           $result = array_reduce($af, function ($carry, $item) {
  //             $carry += $item['value'];
  //             return $carry;
  //           }) / count($af);
  //         }

  //         array_push($sensor_lists[$k]["sensor_postVal"], $result);
  //       }
  //     }




  //     // $last_date = clone ($date_from);
  //     // $last_date->modify('last day of this month');

  //     // for ($i=1; $i < (int)$last_date->format("d") ; $i++) { 
  //     //   array_push($myData,[
  //     //     "date" => $date_from,
  //     //   ]);
  //     // }


  //     // $date_to->modify('last day of this month');

  //     // $data

  //     // foreach ($data->sensor_lists as $key => $value) {
  //     //   # code...
  //     // }



  //     // return response()->json(["data" => $data, "myData" => $myData, "sensor_lists" => $sensor_lists], 200);


  //     // $ori = json_decode(json_encode($this->detailHistoryAirLimbah($request, true)), true)["original"];
  //     // $data = $ori["data"];
  //     // $additional = $ori["additional"];

  //     $date = new \DateTime();
  //     // $filename = $date->format("YmdHis") . "-" . $additional["company_name"] . "[" . $additional["date_from"] . "-" . $additional["date_to"] . "]";
  //     $filename = $date->format("YmdHis") . "-" . $info["name"] . "[" . $request->periodic . "]";
  //     // $filename=$date->format("YmdHis");

  //     // return response()->json(["message"=>$filename],200);

  //     // $mime = MyLib::mime("csv");
  //     $mime = MyLib::mime("xls");

  //     //     Excel::loadView('folder.file', $data)
  //     // ->setTitle('FileName')
  //     // ->sheet('SheetName')
  //     // ->mergeCells('A2:B2')
  //     // ->export('xls');

  //     $bs64 = base64_encode(Excel::raw(new MyReport(["myData" => $myData, "sensor_lists" => $sensor_lists, "info" => $info], 'report.sensor_data'), $mime["exportType"]));

  //     $result = [
  //       "contentType" => $mime["contentType"],
  //       "data" => $bs64,
  //       "dataBase64" => $mime["dataBase64"] . $bs64,
  //       "filename" => $filename . "." . $mime["ext"]
  //     ];
  //     return $result;
  //   } catch (\Exception $e) {
  //     return response()->json([
  //       // "getCode" => $e->getCode(),
  //       // "line" => $e->getLine(),
  //       // "message" => $e->getMessage(),
  //       "message" => "error",
  //     ], 400);
  //   }

  //   // return response()->json(["data"=>$result],200);
  //   // return $data;
  // }

  public function detailPeriodDataDownload(Request $request)
  {
    try {
      $this->admin = MyLib::admin();

      $rules = [
        '_TimeZoneOffset' => "required|numeric",
        'periodic' => "required|date_format:Y-m-d",
        'sensor_token_id' => "required|exists:\App\Model\SensorToken,id",
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

      $sensor_token_id = $request->sensor_token_id;

      $d1millis = 1000 * 60 * 60;

      $data = SensorToken::with(['sensor_lists' => function ($q) use ($utc_date_from, $utc_date_to, $d1millis) {
        $q->with(['sensor_datas' => function ($q2) use ($utc_date_from, $utc_date_to, $d1millis) {
          $q2->where('created_at', ">=", $utc_date_from - $d1millis)->where('created_at', "<", $utc_date_to)->orderBy("created_at", "asc");
        }]);
      }])->find($sensor_token_id);

      $info = [
        "name" => $data->name,
      ];

      $myData = [];

      $dDate = $date_from;
      while ($dDate != $date_to) {
        $md = [
          "utc_from" => MyLib::local_to_utc($tz, $dDate),
          "date_from" => $dDate,
        ];

        $dDate = new \DateTime($dDate);
        $dDate->add(new \DateInterval('PT1H'));
        $dDate = $dDate->format("Y-m-d H:i:s");

        $md["utc_to"] = MyLib::local_to_utc($tz, $dDate);
        array_push($myData, $md);
      }

      $sensor_lists = $data->sensor_lists->toArray();
      foreach ($sensor_lists as $k => $v) {

        $sensor_lists[$k]["sensor_postVal"] = [];

        foreach ($myData as $k1 => $v1) {

          $af = array_values(array_filter($v['sensor_datas'], function ($x) use ($v1, $d1millis) {
            return $x['created_at'] >= ($v1['utc_from'] - $d1millis) && $x['created_at'] < ($v1['utc_to'] - $d1millis);
          }));

          $result = 0;
          if (count($af) == 0) {
          } elseif (count($af) == 1 && $v['type'] == 'inc') {
          } elseif (count($af) == 1 && $v['type'] == 'ran') {
          } elseif ($v['type'] == 'inc') {
            $result = $af[count($af) - 1]['value'];
            // $result = ($af[count($af) - 1]['value'] - $af[0]['value']) / 2;
          } elseif ($v['type'] == 'ran') {
            $result = $af[count($af) - 1]['value'];
            // $result = array_reduce($af, function ($carry, $item) {
            //   $carry += $item['value'];
            //   return $carry;
            // }) / count($af);
          }

          array_push($sensor_lists[$k]["sensor_postVal"], $result);
        }
      }




      // $last_date = clone ($date_from);
      // $last_date->modify('last day of this month');

      // for ($i=1; $i < (int)$last_date->format("d") ; $i++) { 
      //   array_push($myData,[
      //     "date" => $date_from,
      //   ]);
      // }


      // $date_to->modify('last day of this month');

      // $data

      // foreach ($data->sensor_lists as $key => $value) {
      //   # code...
      // }



      // return response()->json(["data" => $data, "myData" => $myData, "sensor_lists" => $sensor_lists], 200);


      // $ori = json_decode(json_encode($this->detailHistoryAirLimbah($request, true)), true)["original"];
      // $data = $ori["data"];
      // $additional = $ori["additional"];

      $date = new \DateTime();
      // $filename = $date->format("YmdHis") . "-" . $additional["company_name"] . "[" . $additional["date_from"] . "-" . $additional["date_to"] . "]";
      $filename = $date->format("YmdHis") . "-" . $info["name"] . "[" . $request->periodic . "]";
      // $filename=$date->format("YmdHis");

      // return response()->json(["message"=>$filename],200);

      // $mime = MyLib::mime("csv");
      $mime = MyLib::mime("xls");

      //     Excel::loadView('folder.file', $data)
      // ->setTitle('FileName')
      // ->sheet('SheetName')
      // ->mergeCells('A2:B2')
      // ->export('xls');

      $bs64 = base64_encode(Excel::raw(new MyReport(["myData" => $myData, "sensor_lists" => $sensor_lists, "info" => $info], 'report.sensor_data'), $mime["exportType"]));

      $result = [
        "contentType" => $mime["contentType"],
        "data" => $bs64,
        "dataBase64" => $mime["dataBase64"] . $bs64,
        "filename" => $filename . "." . $mime["ext"]
      ];
      return $result;
    } catch (\Exception $e) {
      return response()->json([
        // "getCode" => $e->getCode(),
        // "line" => $e->getLine(),
        // "message" => $e->getMessage(),
        "message" => "error",
      ], 400);
    }

    // return response()->json(["data"=>$result],200);
    // return $data;
  }

  // Air Limbah
  // public function detailHistoryAirLimbah(Request $request, $download = false)
  // {
  //   set_time_limit(0);

  //   $result_return = $this->generateDateHistory($request, $download);
  //   $location_id = $request->location_id;
  //   $arr_datex = $result_return['arr_datex'];
  //   $arr_date_for_electricity = $result_return['arr_date_for_electricity'];
  //   $my_data_returns = [];

  //   if (count($arr_datex) > 0) {
  //     $first = "";
  //     // $queries = $this->Model[$result_return["period"]]::where("location_id",$location_id)->where("created_at","<=",$result_return["new_date_to"])->where("created_at",">",$result_return["new_date_from"])->get()->toArray();
  //     $queries = $this->Model[$result_return["period"]]::where("location_id", $location_id)->where("created_at", "<=", $arr_datex[0])->where("created_at", ">", $arr_datex[count($arr_datex) - 1])->orderBy("created_at", "desc")->get()->toArray();

  //     foreach ($arr_datex as $key => $value) {
  //       $created_at = $value - 1000;

  //       if (count($queries) == 0) {
  //         $last = $this->Model[$result_return["period"]]::where("location_id", $location_id)->where("created_at", "<=", $arr_datex[count($arr_datex) - 1])->orderBy("created_at", "desc")->first();
  //         if ($last)
  //           $queries[0] = $last->toArray();
  //         else
  //           break;
  //       }

  //       $result = $this->calALMillisUPS($arr_date_for_electricity[$key + 1], $arr_date_for_electricity[$key], $queries[0]['sensor_id']);
  //       $result["total"] = $result["total"] ? millisToTime($result["total"]) : 0;
  //       if ($queries[0]["created_at"] == $created_at) {

  //         if ($download) {
  //           $created_at = $created_at + MyLib::reverseTimeZoneOffset($result_return['timeZoneOffset']);
  //         } else {
  //           $created_at = $created_at;
  //         }
  //         array_push($my_data_returns, [
  //           "created_at" => $created_at,
  //           "totalizer" => $queries[0]["totalizer"] / 1000,
  //           "deviation" => $queries[0]["deviation"] / 1000,
  //           "flow_rate" => $queries[0]["flow_rate"],
  //           "electricity_is_off" => $result["total"],
  //         ]);

  //         // \App\Helpers\EzLog::logging([
  //         //   "same"=>true,
  //         //   "q"=>$queries[0]["created_at"],
  //         //   "c"=>$created_at,
  //         //   "d"=>[
  //         //     "created_at"=>$created_at,
  //         //     "totalizer"=>$queries[0]["totalizer"] / 1000,
  //         //     "deviation"=>$queries[0]["deviation"] / 1000,
  //         //     "flow_rate"=>$queries[0]["flow_rate"]
  //         //   ],
  //         // ],"checking");

  //         array_splice($queries, 0, 1);
  //       } else {
  //         if ($download) {
  //           $created_at = $created_at + MyLib::reverseTimeZoneOffset($result_return['timeZoneOffset']);
  //         } else {
  //           $created_at = $created_at;
  //         }
  //         array_push($my_data_returns, [
  //           "created_at" => $created_at,
  //           "totalizer" => $queries[0]["totalizer"] / 1000,
  //           "deviation" => 0,
  //           "flow_rate" => 0,
  //           "electricity_is_off" => $result["total"],
  //         ]);

  //         // \App\Helpers\EzLog::logging([
  //         //   "same"=>false,
  //         //   "q"=>$queries[0]["created_at"],
  //         //   "c"=>$created_at,
  //         //   "d"=>[
  //         //     "created_at"=>$created_at,
  //         //     "totalizer"=>$queries[0]["totalizer"] / 1000,
  //         //     "deviation"=>0,
  //         //     "flow_rate"=>0
  //         //   ],
  //         // ],"checking");

  //       }
  //     }
  //   }

  //   // if(!$result_return["overflow"]){
  //   //   $first = "";
  //   //   $queries = $this->Model[$result_return["period"]]::where("location_id",$location_id)->where("created_at","<=",$result_return["new_date_to"])->where("created_at",">",$result_return["new_date_from"])->get();

  //   //   foreach ($queries as $k => $v) {  
  //   //     if($download){
  //   //       $created_at = $v->created_at + MyLib::reverseTimeZoneOffset($result_return['timeZoneOffset']);
  //   //     }else{
  //   //       $created_at = $v->created_at;
  //   //     }

  //   //     array_push($my_data_returns,[
  //   //       "created_at"=>$created_at,
  //   //       "totalizer"=>$v->totalizer / 1000,
  //   //       "deviation"=>$v->deviation / 1000,
  //   //       "flow_rate"=>$v->flow_rate
  //   //     ]);
  //   //   }
  //   // }

  //   if (!$download)
  //     return response()->json(
  //       [
  //         "data" => $my_data_returns,
  //         "overflow" => $result_return["overflow"],
  //         "new_date_from" => $result_return["new_date_from"],
  //         "new_date_to" => $result_return["new_date_to"],
  //         // "model"=>$this->Model[$result_return["period"]]
  //       ],
  //       200
  //     );
  //   else {
  //     $location = \App\Model\Location::where("id", $location_id)->first();
  //     return response()->json(
  //       [
  //         "data" => $my_data_returns,
  //         "additional" => [
  //           "company_name" => $location->name,
  //           "date_from" => $result_return['d_from_ori'],
  //           "date_to" => $result_return['d_to_ori'],
  //         ]
  //       ],
  //       200
  //     );
  //   }
  // }

  // public function detailHistoryAirLimbahDownload(Request $request)
  // {
  //   set_time_limit(0);

  //   $this->admin = MyLib::admin();

  //   $rules = [
  //     'date_from' => "required|date_format:Y-m-d H:i:s",
  //   ];

  //   $messages = [
  //     'date_from.required' => 'Date From is required',
  //     'date_from.date_format' => 'Please Select Date From',
  //   ];

  //   $validator = \Validator::make($request->all(), $rules, $messages);

  //   if ($validator->fails()) {
  //     throw new ValidationException($validator);
  //   }


  //   // Change some request value
  //   $request['period'] = "Daily";

  //   $date_from = $request->date_from;
  //   $d_from = date("Y-m", MyLib::manualMillis($date_from) / 1000) . "-01 00:00:00";
  //   $date_f = new \DateTime($d_from);

  //   $start = clone $date_f;
  //   $start->add(new \DateInterval('P1M'));
  //   $start->sub(new \DateInterval('P1D'));
  //   $x = $start->format("Y-m-d H:i:s");

  //   $request['date_from'] = $d_from;
  //   $request['date_to'] = $x;
  //   // return response()->json(["data"=>[$d_from,$x]],200);

  //   $ori = json_decode(json_encode($this->detailHistoryAirLimbah($request, true)), true)["original"];
  //   $data = $ori["data"];
  //   $additional = $ori["additional"];

  //   $date = new \DateTime();
  //   $filename = $date->format("YmdHis") . "-" . $additional["company_name"] . "[" . $additional["date_from"] . "-" . $additional["date_to"] . "]";
  //   // $filename=$date->format("YmdHis");

  //   // return response()->json(["message"=>$filename],200);

  //   $mime = MyLib::mime("csv");
  //   $bs64 = base64_encode(Excel::raw(new MyReport($data, 'report.sensor_get_data_by_location'), $mime["exportType"]));

  //   $result = [
  //     "contentType" => $mime["contentType"],
  //     "data" => $bs64,
  //     "dataBase64" => $mime["dataBase64"] . $bs64,
  //     "filename" => $filename . "." . $mime["ext"]
  //   ];
  //   return $result;
  //   // return response()->json(["data"=>$result],200);
  //   // return $data;
  // }

  // public function detailGraphAirLimbah(Request $request)
  // {
  //   $this->admin = MyLib::admin();

  //   $rules = [
  //     'location_id' => 'required|exists:\App\Model\Location,id',
  //     'period' => 'required|in:Realtime,Hourly,Daily,Monthly,Yearly',
  //   ];

  //   $messages = [
  //     'location_id.required' => 'Location ID is required',
  //     'location_id.exists' => 'Location ID not listed',

  //     'period.required' => 'Period is required',
  //     'period.exists' => 'Please Selected the Period',
  //   ];

  //   $validator = \Validator::make($request->all(), $rules, $messages);

  //   if ($validator->fails()) {
  //     throw new ValidationException($validator);
  //   }


  //   $location_id = $request->location_id;
  //   $period = $request->period;

  //   $get_date = new \DateTime("now");
  //   $get_date->add(new \DateInterval('PT7H'));
  //   $date_to = $get_date->format('Y-m-d H:i:s');

  //   $limit = 25;

  //   $my_data_returns = [];

  //   if ($period == "Realtime") {
  //     $sqls = \App\Model\AirLimbahDataRealtime::where("location_id", $location_id)->orderBy("created_at", "desc")->limit($limit)->get();
  //     foreach ($sqls as $s => $sql) {
  //       array_push($my_data_returns, [
  //         "created_at" => $sql->created_at,
  //         "totalizer" => $sql->totalizer / 1000,
  //         "flow_rate" => $sql->flow_rate,
  //         "electricity_is_off" => $sql->electricity_is_off ? 1 : 0
  //       ]);
  //     }

  //     return response()->json([
  //       "data" => $my_data_returns,
  //     ], 200);
  //   }

  //   // $arr_dates = [];

  //   $time_interval = "";
  //   $date_interval = "";
  //   $Model = "";
  //   if ($period == "Minutes") {
  //     $time_interval = "T";
  //     $date_interval = "M";
  //     // $d_to = date("Y-m-d H:i", MyLib::manualMillis($date_to) / 1000) . ":00";
  //   } elseif ($period == "Hourly") {
  //     $time_interval = "T";
  //     $date_interval = "H";
  //     // $d_to = date("Y-m-d H", MyLib::manualMillis($date_to) / 1000) . ":00:00";
  //     $Model = new \App\Model\AirLimbahDataHour();
  //   } elseif ($period == "Daily") {
  //     $date_interval = "D";
  //     // $d_to = date("Y-m-d", MyLib::manualMillis($date_to) / 1000) . " 00:00:00";
  //     $Model = new \App\Model\AirLimbahDataDay();
  //   } elseif ($period == "Monthly") {
  //     $date_interval = "M";
  //     // $d_to = date("Y-m", MyLib::manualMillis($date_to) / 1000) . "-01 00:00:00";
  //     $Model = new \App\Model\AirLimbahDataMonth();
  //   } elseif ($period == "Yearly") {
  //     $date_interval = "Y";
  //     // $d_to = date("Y", MyLib::manualMillis($date_to) / 1000) . "-01-01 00:00:00";
  //     $Model = new \App\Model\AirLimbahDataYear();
  //   }

  //   // $date_t = new \DateTime($d_to);
  //   // $date_t->add(new \DateInterval('P' . $time_interval . '1' . $date_interval));

  //   // $date_f = new \DateTime($date_t->format("Y-m-d H:i:s"));
  //   // $date_f->sub(new \DateInterval('P'.$time_interval.'25'.$date_interval));

  //   $queries = $Model::where("location_id", $location_id)
  //     // ->where("created_at", "<=", MyLib::utcMillis($date_t->format("Y-m-d H:i:s")))
  //     // ->where("created_at",">",MyLib::utcMillis($date_f->format("Y-m-d H:i:s")))
  //     ->orderBy("created_at", "desc")->limit($limit);

  //   // EzLog::logging([
  //   //   "q"=>$queries->toSql(),
  //   //   "date_t"=>$date_t->format("Y-m-d H:i:s"),
  //   //   "date_x"=>MyLib::utcMillis($date_t->format("Y-m-d H:i:s")),
  //   //   // "date_f"=>$date_f->format("Y-m-d H:i:s"),
  //   // ],"xx");

  //   $queries = $queries->get();

  //   $millisList = $queries->pluck("created_at")->toArray();
  //   if (count($millisList) > 0) {
  //     $injectDate = new \DateTime(date("Y-m-d H:i:s", $millisList[count($millisList) - 1] / 1000));
  //     $injectDate->sub(new \DateInterval('P' . $time_interval . '1' . $date_interval));
  //     array_push($millisList, MyLib::manualMillis($injectDate->format("Y-m-d H:i:s")));
  //   }

  //   // $first = MyLib::utcMillis($date_t->format("Y-m-d H:i:s"));

  //   foreach ($queries as $k => $v) {
  //     $result = $this->calALMillisUPS($millisList[$k + 1], $millisList[$k], $v->sensor_id);
  //     $total = $result["total"] ?? 0;
  //     // $center = DB::table('air_limbah_electricities')
  //     //   ->select(DB::raw('SUM( CASE WHEN on_at is null THEN ' . MyLib::manualMillis(date("Y-m-d H:i:s")) . ' - off_at WHEN on_at is not null THEN on_at - off_at END) as usage_millis'))
  //     //   ->where('off_at', "<=", $first)
  //     //   ->where(function ($q) use ($v) {
  //     //     $q->where("on_at", ">=", $v->created_at);
  //     //     $q->orWhereNull("on_at");
  //     //   })
  //     //   ->where('location_id', $location_id)->first();

  //     // $electricityx = DB::table('air_limbah_electricities')
  //     //   ->select(DB::raw('SUM( CASE WHEN on_at is null THEN ' . MyLib::manualMillis(date("Y-m-d H:i:s")) . ' - off_at WHEN on_at is not null THEN on_at - off_at END) as usage_millis'))
  //     //   ->where('off_at', "<=", $first)
  //     //   ->where(function ($q) use ($v) {
  //     //     $q->where("on_at", ">=", $v->created_at);
  //     //     $q->orWhereNull("on_at");
  //     //   })
  //     //   ->where('location_id', $location_id);
  //     // // ->orderBy("off_at", "desc");
  //     // EzLog::logging([
  //     //   // "data" => json_encode($electricityx)
  //     //   "from" => $v->created_at,
  //     //   "to" => $first,
  //     //   "result" => $result
  //     // ], "xx");

  //     // \App\Helpers\EzLog::logging($electricityx->toBoundSql(), "xx");

  //     // $electricity = \App\Model\AirLimbahElectricity::where("off_at", "<", $first)->orderBy("off_at", "desc")->first();


  //     array_push($my_data_returns, [
  //       "created_at" => $v->created_at,
  //       "totalizer" => $v->totalizer / 1000,
  //       "deviation" => $v->deviation / 1000,
  //       "flow_rate" => $v->flow_rate,
  //       "electricity_is_off" => $total / 1000 / 3600
  //     ]);

  //     // $first = $v->created_at;
  //   }
  //   return response()->json(
  //     [
  //       "data" => $my_data_returns,
  //     ],
  //     200
  //   );

  //   // for ($i=0; $i <= $limit; $i++) { 
  //   //   $start = clone $date_t;
  //   //   $start->sub(new \DateInterval('P'.$time_interval.$i.$date_interval));
  //   //   $x = $start->format("Y-m-d H:i:s");

  //   //   array_push($arr_dates,MyLib::utcMillis($x));
  //   // }

  //   // // $arr_dates = array_reverse($arr_dates); // data terbaru
  //   // $arr_datex = $arr_dates;

  //   // $first_q="";
  //   // foreach ($arr_datex as $k => $v) {
  //   //   $created_at = $v-1000;
  //   //   $temp = \App\Model\AirLimbahFlowMeter::select('*',DB::Raw("CONCAT(".$created_at.") AS date_to_front_end,CONCAT(".$v.") AS date_to_compare"))->where("location_id",$location_id)->where("created_at","<",$v)->orderBy("created_at","desc")->limit(1);
  //   //   if($k==0){
  //   //     $first_q=$temp;
  //   //   }else{
  //   //     $first_q = $first_q->unionAll($temp);
  //   //   }
  //   // }

  //   // if($first_q){

  //   //   // \App\Helpers\EzLog::logging($first_q->toBoundSql(),"check totalizer");

  //   //   $lists = $first_q->get();

  //   //   foreach ($lists as $k => $v) {
  //   //     if(count($lists) == 1){
  //   //       $deviation = $v->total_val;
  //   //       $compare = $v->date_to_compare - $arr_datex[$k + 1];
  //   //     }

  //   //     if($k < count($lists) - 1 ){
  //   //       $deviation = $v->total_val - $lists[$k + 1]->total_val;
  //   //       $compare = $v->date_to_compare - $lists[$k + 1]->date_to_compare;
  //   //     }

  //   //     if(count($lists) == 1 || $k < count($lists) - 1){
  //   //       array_push($my_data_returns,[
  //   //         "created_at"=>(int) $v->date_to_front_end,
  //   //         "totalizer"=>$v->total_val / 1000,
  //   //         "deviation"=>$deviation / 1000,
  //   //         "flow_rate"=>$deviation / ($compare / 1000) * 3600 / 1000,
  //   //       ]);
  //   //     }

  //   //   }
  //   // }

  //   // return response()->json(
  //   //   [ 
  //   //     "data"=>$my_data_returns,
  //   //   ]
  //   // ,200);


  // }

  // public function generateDateHistory(Request $request, $download = false)
  // {
  //   $this->admin = MyLib::admin();

  //   //======================================================================================================
  //   // Validasi Input
  //   //======================================================================================================

  //   $rules = [
  //     'location_id' => 'required|exists:\App\Model\Location,id',
  //     'period' => 'required|in:Hourly,Daily,Weekly,Monthly,Yearly',
  //     'date_from' => "required|date_format:Y-m-d H:i:s",
  //     'date_to' => "required|date_format:Y-m-d H:i:s",
  //     // 'date_to'=>"required|date_format:Y-m-d H:i:s|after:date_from",
  //   ];

  //   $messages = [
  //     'location_id.required' => 'Location ID is required',
  //     'location_id.exists' => 'Location ID not listed',

  //     'period.required' => 'Period is required',
  //     'period.exists' => 'Please Selected the Period',

  //     'date_from.required' => 'Date From is required',
  //     'date_from.date_format' => 'Please Select Date From',

  //     'date_to.required' => 'Date To is required',
  //     'date_to.date_format' => 'Please Select Date To',
  //     // 'date_to.after'=>'Date To mush after Date From',
  //   ];

  //   $validator = \Validator::make($request->all(), $rules, $messages);

  //   if ($validator->fails()) {
  //     throw new ValidationException($validator);
  //   }

  //   //======================================================================================================
  //   // Pembatasan Data hanya memerlukan limit dan offset
  //   //======================================================================================================

  //   $limit = 30; // Limit +> Much Data
  //   if (isset($request->limit)) {
  //     if ($request->limit <= 250) {
  //       $limit = $request->limit;
  //     } else {
  //       throw new MyException("Max Limit 250");
  //     }
  //   }

  //   $offset = isset($request->offset) ? (int) $request->offset : 0; // example offset 400 start from 401

  //   //======================================================================================================
  //   // Jika Halaman Ditentutkan maka $offset akan disesuaikan
  //   //======================================================================================================
  //   $page = 1;
  //   if (isset($request->page)) {
  //     $page =  (int) $request->page;
  //     $offset = ($page * $limit) - $limit;
  //   }

  //   $result_return = [];

  //   if ($download) {
  //     if (!isset($request->_TimeZoneOffset)) {
  //       throw new MyException(
  //         [
  //           "message" => "Please Refresh Your Page, TimeZoneOffset Required",
  //         ],
  //         400
  //       );
  //     }
  //     $result_return['timeZoneOffset'] = $request->_TimeZoneOffset;

  //     $offset = 0;
  //     $limit  = 1001;
  //   }

  //   $period = $request->period;
  //   $date_from = $request->date_from;
  //   $date_to = $request->date_to;

  //   $arr_dates = [];

  //   $time_interval = "";
  //   $date_interval = "";

  //   if ($period == "Minutes") {
  //     $time_interval = "T";
  //     $date_interval = "M";
  //     $d_from = date("Y-m-d H:i", MyLib::manualMillis($date_from) / 1000) . ":00";
  //     $d_to = date("Y-m-d H:i", MyLib::manualMillis($date_to) / 1000) . ":00";
  //   } elseif ($period == "Hourly") {
  //     $time_interval = "T";
  //     $date_interval = "H";
  //     $d_from = date("Y-m-d H", MyLib::manualMillis($date_from) / 1000) . ":00:00";
  //     $d_to = date("Y-m-d H", MyLib::manualMillis($date_to) / 1000) . ":00:00";
  //   } elseif ($period == "Daily") {
  //     $date_interval = "D";
  //     $d_from = date("Y-m-d", MyLib::manualMillis($date_from) / 1000) . " 00:00:00";
  //     $d_to = date("Y-m-d", MyLib::manualMillis($date_to) / 1000) . " 00:00:00";
  //   } elseif ($period == "Monthly") {
  //     $date_interval = "M";
  //     $d_from = date("Y-m", MyLib::manualMillis($date_from) / 1000) . "-01 00:00:00";
  //     $d_to = date("Y-m", MyLib::manualMillis($date_to) / 1000) . "-01 00:00:00";
  //   } elseif ($period == "Yearly") {
  //     $date_interval = "Y";
  //     $d_from = date("Y", MyLib::manualMillis($date_from) / 1000) . "-01-01 00:00:00";
  //     $d_to = date("Y", MyLib::manualMillis($date_to) / 1000) . "-01-01 00:00:00";
  //   }

  //   if (MyLib::manualMillis($d_from) > MyLib::manualMillis($d_to)) {
  //     throw new MyException(
  //       [
  //         "date_to" => ["Date To mush after Date From"],
  //       ],
  //       422
  //     );
  //   }


  //   $result_return['d_from_ori'] = $d_from;
  //   $result_return['d_to_ori'] = $d_to;

  //   $date_t = new \DateTime($d_to);
  //   $date_t->add(new \DateInterval('P' . $time_interval . '1' . $date_interval));

  //   // $date_f = new \DateTime($d_from);
  //   $date_f = MyLib::utcMillis($d_from);
  //   // $checks = [];
  //   $arr_date_for_electricity = [];
  //   for ($i = ($page - 1) * $limit; $i < $page * $limit; $i++) {
  //     $start = clone $date_t;
  //     $start->sub(new \DateInterval('P' . $time_interval . $i . $date_interval));
  //     $x = MyLib::utcMillis($start->format("Y-m-d H:i:s"));

  //     if ($x <= $date_f) {
  //       break;
  //     }

  //     array_push($arr_dates, $x);
  //     array_push($arr_date_for_electricity, $x);

  //     // array_push($checks, $start->format("Y-m-d H:i:s"));
  //   }

  //   if (count($arr_date_for_electricity) > 0) {
  //     $injectDate = new \DateTime(date("Y-m-d H:i:s", $arr_date_for_electricity[count($arr_date_for_electricity) - 1] / 1000));
  //     $injectDate->sub(new \DateInterval('P' . $time_interval . '1' . $date_interval));
  //     array_push($arr_date_for_electricity, MyLib::manualMillis($injectDate->format("Y-m-d H:i:s")));
  //   }

  //   // \App\Helpers\EzLog::logging($checks, "checking");


  //   $result_return['arr_datex'] = $arr_dates;
  //   $result_return['arr_date_for_electricity'] = $arr_date_for_electricity;


  //   $result_return["period"] = $period;

  //   $new_date_to = clone $date_t;
  //   $new_date_to->sub(new \DateInterval('P' . $time_interval . ($page - 1) * $limit . $date_interval));
  //   $new_date_to = MyLib::utcMillis($new_date_to->format("Y-m-d H:i:s"));

  //   $result_return["new_date_to"] = $new_date_to;

  //   $new_date_from = clone $date_t;
  //   $new_date_from->sub(new \DateInterval('P' . $time_interval . ($page * $limit) . $date_interval));
  //   $new_date_from = MyLib::utcMillis($new_date_from->format("Y-m-d H:i:s"));

  //   if ($new_date_from < $date_f) {
  //     $new_date_from = $date_f;
  //   }
  //   $result_return["new_date_from"] = $new_date_from;

  //   $result_return["overflow"] = $new_date_to < $date_f ? true : false;

  //   return $result_return;
  // }

  // public function calALMillisUPS($from, $to, $sensor_id)
  // {
  //   $lastMillis = 0;
  //   $centerMillis = 0;
  //   $firstMillis = 0;
  //   $last = $first = $center = "";

  //   $nowMillis = MyLib::manualMillis(date("Y-m-d H:i:s.v"));
  //   $to =  $to <= $nowMillis ? $to : $nowMillis;

  //   try {
  //     if ($from > $nowMillis) throw new \Exception("All 0", 1);


  //     $last = DB::table('air_limbah_electricities')
  //       ->where(function ($q) use ($to, $sensor_id) {
  //         $q->where("sensor_id", $sensor_id);
  //         $q->where("off_at", "<", $to);
  //         $q->where("on_at", ">", $to);
  //       })
  //       ->orWhere(function ($q) use ($to, $sensor_id) {
  //         $q->where("sensor_id", $sensor_id);
  //         $q->where("off_at", "<", $to);
  //         $q->whereNull("on_at");
  //       });

  //     // $lastQ = $last->toBoundSql();
  //     $last = $last->first();


  //     if ($last && $last->on_at == null) {

  //       if ($from >= $last->off_at) {
  //         $lastMillis = $to - $from;
  //         throw new \Exception("Other 0", 1);
  //       } else {
  //         $lastMillis = $to - $last->off_at;
  //       }
  //     }

  //     if ($last && $last->on_at != null) {
  //       if ($from >= $last->off_at) {
  //         $lastMillis = $to - $from;
  //         throw new \Exception("Other 0", 1);
  //       } else {
  //         $lastMillis = $to - $last->off_at;
  //       }
  //     }

  //     $center = DB::table('air_limbah_electricities')
  //       ->select(DB::raw('sum( CASE WHEN on_at is null THEN ' . $to . ' - off_at WHEN on_at is not null THEN on_at - off_at ELSE 0 END) as sum_all'))
  //       ->where("sensor_id", $sensor_id)
  //       ->where("off_at", ">=", $from)
  //       ->where("on_at", "<=", $to)
  //       ->first();

  //     if ($center) $centerMillis = (int)$center->sum_all;

  //     $first = DB::table('air_limbah_electricities')
  //       ->where("sensor_id", $sensor_id)
  //       ->where("off_at", "<", $from)
  //       ->where("on_at", ">", $from)
  //       ->first();

  //     if ($first) $firstMillis = $first->on_at - $from;
  //   } catch (\Throwable $th) {
  //     //throw $th;
  //   }

  //   $result = [
  //     "from" => $from,
  //     "to" => $to,
  //     "total" => $lastMillis + $centerMillis + $firstMillis,
  //     "last" => $lastMillis,
  //     "center" => $centerMillis,
  //     "first" => $firstMillis,
  //     "lastDt" => $last,
  //     "firstDt" => $first,
  //     "centerDt" => $center,
  //   ];
  //   // \App\Helpers\EzLog::logging($result, "graph");


  //   return $result;
  // }
}


// $date_f = new \DateTime($d_from);
    // $date_t = new \DateTime($d_to);

    // $date_t->add(new \DateInterval($time_interval.'1'.$date_interval));

    // $interval = $date_f->diff($date_t);

    // $sign = $interval->format('%r');
    // if($sign == '-'){
    //   throw new MyException([
    //     "message"=>"Date Not Right Please Check Again"
    //   ]);  
    // }
    // $diff_a=$interval->format('%r%a');
    // $diff_y=$interval->format('%r%y');
    // $diff_m=$interval->format('%r%m');
    // $diff_d=$interval->format('%r%d');
    // $diff_h=$interval->format('%r%h');
    // $diff_i=$interval->format('%r%i');

    // if($period=="Monthly"){
    //   $diff_interval = $diff_y * 12 + $diff_m;
    // }elseif($period=="Daily"){
    //   $diff_interval = $diff_a;
    // }elseif ($period=="Hourly") {
    //   $diff_interval = ($diff_a * 24) + $diff_h;
    // }elseif ($period=="Minutes") {
    //   $diff_interval = (($diff_a * 24) + $diff_h) * 60 + $diff_i;
    // }

    // // \App\Helpers\EzLog::logging([
    // //   "??"=>$diff_interval,
    // // ],"check totalizer");

    // // \App\Helpers\EzLog::logging([
    // //   "r"=>$sign,
    // //   "a"=>$diff_a,
    // //   "y"=>$diff_y,
    // //   "m"=>$diff_m,
    // //   "d"=>$diff_d,
    // //   "h"=>$diff_h,
    // //   "i"=>$diff_i,
    // //   "??"=>$diff_interval,
    // // ],"check totalizer");

    // if($download){
    //   $offset=0;
    //   $last_limit = 1001;
    //   if($last_limit > $diff_interval) $last_limit=$diff_interval;

    // }else {
    //   $last_limit = ($page*$limit);
    //   if($last_limit > $diff_interval) $last_limit=$diff_interval;
    // }    

    // for ($i=$offset; $i <= $last_limit; $i++) { 
    //   $start = clone $date_t;
    //   $start->sub(new \DateInterval($time_interval.$i.$date_interval));
    //   $x = $start->format("Y-m-d H:i:s");
    //   array_push($arr_dates,MyLib::utcMillis($x));
    // }

    // $result_return['arr_datex']=$arr_dates;

    // \App\Helpers\EzLog::logging([
    //   "total"=>count($arr_dates),
    // ],"check totalizer");

    // for ($i=0; $i <= $interval->format('%'.$date_interval); $i++) { 
    //   $start = clone $date_f;
    //   $start->add(new \DateInterval($time_interval.$i.$date_interval));
    //   $x = $start->format("Y-m-d H:i:s");
    //   array_push($arr_dates,MyLib::utcMillis($x));
    // }
    // $arr_dates = array_reverse($arr_dates); // semua data terbaru

    // if (!$download)
    //   $arr_datex = array_slice($arr_dates, $offset, $limit+1); // limit dan offset
    // else
    //   $arr_datex = array_slice($arr_dates, 0, 1001); // limit dan offset

    // $result_return['arr_datex']=$arr_datex;

    // throw new MyException([
    //   "data"=>[]
    // ]);

  // public function index(Request $request)
  // {
  //   $this->admin = MyLib::admin();

  //   $new_query = DB::table("locations")->leftJoin("air_limbah_sensors", "air_limbah_sensors.location_id", "=", "locations.id");
  //   $new_query->leftJoin(DB::raw("(select alfm.created_at as alfm_created_at , alfm.location_id as alfm_location_id, alfm.real_time_val as alfm_real_time_val, alfm.total_val as alfm_total_val, alfm.air_limbah_sensor_id as alfm_als_id, alfm_b.min as alfm_min from air_limbah_flow_meters as alfm, (select location_id,air_limbah_sensor_id,max(created_at) as created_at from air_limbah_flow_meters group by location_id, air_limbah_sensor_id) as alfmb, (select min(created_at) as min, location_id , air_limbah_sensor_id, total_val from air_limbah_flow_meters group by location_id , air_limbah_sensor_id, total_val order by min(created_at) desc) as alfm_b where alfm.air_limbah_sensor_id = alfmb.air_limbah_sensor_id and alfm.created_at = alfmb.created_at and alfm_b.total_val = alfm.total_val ) as alfm"), function ($join) {
  //     $join->on("locations.id", "=", "alfm_location_id");
  //     $join->on("air_limbah_sensors.id", "=", "alfm_als_id");
  //   });
  //   $new_query->select(
  //     'locations.id as l_id',
  //     'locations.is_tenant as l_is_tenant',
  //     'locations.grup as l_grup',
  //     'locations.name as l_name',
  //     'locations.created_at as l_created_at',
  //     'locations.updated_at as l_updated_at',
  //     'air_limbah_sensors.id as als_id',
  //     'air_limbah_sensors.qmax as als_qmax',
  //     'air_limbah_sensors.token as als_token',
  //     'air_limbah_sensors.location_id as als_location_id',
  //     'alfm_created_at',
  //     'alfm_location_id',
  //     'alfm_real_time_val',
  //     'alfm_total_val',
  //     'alfm_als_id',
  //     'alfm_min'
  //   );
  //   $new_query = $new_query->orderBy("l_name");
  //   $new_query = $new_query->get();

  //   $arr_q = [];
  //   foreach ($new_query as $key => $n_q) {
  //     array_push($arr_q, "(select * from air_limbah_flow_meters where location_id = $n_q->l_id and air_limbah_sensor_id = $n_q->als_id order by created_at desc limit 20)");
  //   }

  //   $m_query = DB::select(implode(" UNION ALL ", $arr_q));

  //   $r_data = [];
  //   foreach ($new_query as $key => $n_q) {
  //     $lists = [];
  //     foreach ($m_query as $m_key => $m_q) {
  //       if ($m_q->location_id == $n_q->l_id && $m_q->air_limbah_sensor_id == $n_q->als_id) {
  //         array_push($lists, $m_q);
  //       }
  //     }
  //     $new_val = (array)$n_q;
  //     $new_val["lists_2m"] = $lists;
  //     array_push($r_data, $new_val);
  //   }

  //   return response()->json($r_data, 200);
  // }

  // public function getAirLimbahSensorData(Request $request)
  // {

  //   $this->admin = MyLib::admin();
  //   $r_data = [];

  //   $new_queries = DB::table("locations");

  //   if ($request->search_location_name) {
  //     $new_queries->where("name", "ilike", "%" . $request->search_location_name . "%");
  //   }
  //   $new_queries->leftJoin("air_limbah_sensors", "air_limbah_sensors.location_id", "=", "locations.id");
  //   $new_queries->select(
  //     'locations.id as location_id',
  //     'locations.is_tenant as location_is_tenant',
  //     'locations.grup as location_grup',
  //     'locations.name as location_name',
  //     'locations.coor_lon as location_coor_lon',
  //     'locations.coor_lat as location_coor_lat',
  //     'air_limbah_sensors.id as sender_id',
  //     'air_limbah_sensors.qmax as flowmeter_qmax'
  //   );
  //   $new_queries->orderBy("location_name");
  //   $new_queries = $new_queries->get();

  //   foreach ($new_queries as $k => $v) {
  //     $alfm_temp = "";
  //     $alfm_last_change = "";
  //     $alfm = DB::table("air_limbah_flow_meters")->where("air_limbah_sensor_id", $v->sender_id)->orderBy("created_at", "desc")->first();
  //     if ($alfm) {
  //       $alfm_temp = DB::table("air_limbah_flow_meters")->where("air_limbah_sensor_id", $v->sender_id)->where("total_val", "!=", $alfm->total_val)->orderBy("created_at", "desc")->first();
  //       $alfm_last_change = DB::table("air_limbah_flow_meters")->where("air_limbah_sensor_id", $v->sender_id)->where("created_at", ">", $alfm_temp->created_at)->orderBy("created_at", "asc")->first();
  //     }

  //     array_push($r_data, [
  //       "type" => "air_limbah",
  //       "location_is_tenant" => $v->location_is_tenant,
  //       "location_id" => $v->location_id,
  //       "location_grup" => $v->location_grup,
  //       "location_name" => $v->location_name,
  //       "location_coor_lon" => $v->location_coor_lon,
  //       "location_coor_lat" => $v->location_coor_lat,

  //       "sender_id" => $v->sender_id,
  //       "flowmeter_qmax" => $v->flowmeter_qmax,

  //       "flowmeter_flow_rate" => $alfm ? $alfm->real_time_val : 0,
  //       "flowmeter_totalizer" => $alfm ? $alfm->total_val : 0,

  //       "record_created_at" => $alfm ? $alfm->created_at : 0,
  //       "record_last_change" => $alfm_last_change ? $alfm_last_change->created_at : 0,
  //       // "alfm_temp"=>$alfm_temp ? $alfm_temp->created_at : 0,
  //     ]);
  //   }

  //   return response()->json([
  //     "data" => $r_data,
  //   ], 200);
  // }

// public function getAirLimbahCenterSensorData(Request $request)
  // {
  //   $this->admin = MyLib::admin();

  //   $new_query = DB::table("locations")->leftJoin("air_limbah_sensors", "air_limbah_sensors.location_id", "=", "locations.id");
  //   $new_query->leftJoin(DB::raw("(select alfm.created_at as record_created_at , alfm.location_id as alfm_location_id, alfm.real_time_val as flowmeter_flow_rate, alfm.total_val as flowmeter_totalizer, alfm.air_limbah_sensor_id as alfm_sender_id, alfm_b.min as record_last_change from air_limbah_flow_meters as alfm, (select location_id,air_limbah_sensor_id,max(created_at) as created_at from air_limbah_flow_meters group by location_id, air_limbah_sensor_id) as alfmb, (select min(created_at) as min, location_id , air_limbah_sensor_id, total_val from air_limbah_flow_meters group by location_id , air_limbah_sensor_id, total_val order by min(created_at) desc) as alfm_b where alfm.air_limbah_sensor_id = alfmb.air_limbah_sensor_id and alfm.created_at = alfmb.created_at and alfm_b.total_val = alfm.total_val ) as alfm"), function ($join) {
  //     $join->on("locations.id", "=", "alfm_location_id");
  //     $join->on("air_limbah_sensors.id", "=", "alfm_sender_id");
  //   });
  //   $new_query->select(
  //     'locations.id as location_id',
  //     'locations.is_tenant as location_is_tenant',
  //     'locations.grup as location_grup',
  //     'locations.name as location_name',
  //     'locations.created_at as location_created_at',
  //     'locations.updated_at as location_updated_at',
  //     'air_limbah_sensors.id as sender_id',
  //     'air_limbah_sensors.qmax as flowmeter_qmax',
  //     'air_limbah_sensors.token as sender_token',
  //     'air_limbah_sensors.location_id as als_location_id',
  //     'record_created_at',
  //     'alfm_location_id',
  //     'flowmeter_flow_rate',
  //     'flowmeter_totalizer',
  //     'alfm_sender_id',
  //     'record_last_change'
  //   );
  //   $new_query = $new_query->where("locations.is_tenant", false);
  //   $new_query = $new_query->orderBy("location_name");
  //   // return response()->json($new_query->toSql() ,400);

  //   $new_query = $new_query->get();

  //   $arr_q = [];
  //   foreach ($new_query as $key => $n_q) {
  //     array_push($arr_q, "(select * from air_limbah_flow_meters where location_id = $n_q->location_id and air_limbah_sensor_id = $n_q->sender_id order by created_at desc limit 20)");
  //   }

  //   $m_query = DB::select(implode(" UNION ALL ", $arr_q));

  //   $r_data = [];
  //   foreach ($new_query as $key => $n_q) {
  //     $lists = [];
  //     foreach ($m_query as $m_key => $m_q) {
  //       if ($m_q->location_id == $n_q->location_id && $m_q->air_limbah_sensor_id == $n_q->sender_id) {
  //         array_push($lists, [
  //           "created_at" => $m_q->created_at,
  //           "type" => "air_limbah",
  //           "sender_id" => $m_q->air_limbah_sensor_id,
  //           "location_id" => $m_q->location_id,
  //           "flow_rate" => $m_q->real_time_val,
  //           "totalizer" => $m_q->total_val,
  //           "u_id" => $m_q->u_id,
  //           "totalizer_deviation" => $m_q->deviation,
  //           "electricity_is_off" => $m_q->electricity_is_off,
  //         ]);
  //       }
  //     }
  //     $new_val = (array)$n_q;
  //     $new_val["lists_2m"] = $lists;
  //     $new_val["type"] = "air_limbah";
  //     array_push($r_data, $new_val);
  //   }

  //   return response()->json($r_data, 200);
  // }

  // public function getDetail(Request $request)
  // {

  //   $admin = MyLib::admin();
  //   $air_limbah_sensor_id = $request->air_limbah_sensor_id;
  //   $location_id = $request->location_id;

  //   $dt = new \DateTime();
  //   $ed = $dt->format('Y-m-d H') . ":00:00";
  //   $end_date = MyLib::manualMillis($ed);
  //   // $dt->add(new \DateInterval('PT1H')); 
  //   $dt->sub(new \DateInterval('PT25H'));
  //   $sd = $dt->format('Y-m-d H') . ":00:00";
  //   $start_date = MyLib::manualMillis($sd);

  //   //===============================
  //   //select all and calculate
  //   //===============================
  //   $flowmeters = \App\Model\AirLimbahFlowMeter::where("location_id", $location_id)->where("air_limbah_sensor_id", $air_limbah_sensor_id)->where("created_at", ">=", $start_date)->where("created_at", "<", $end_date)->orderby("created_at", "desc")->get();
  //   $r_data = [];

  //   foreach ($flowmeters as $key => $fm) {
  //     $i = $end_date;
  //     $sql_arr = [];
  //     while ($i >= $start_date) {

  //       if ($fm->created_at <= $i &&  $fm->created_at > $i - 3600000) {
  //         $arr_filter = array_filter($r_data, function ($x) use ($i) {
  //           return $x["date_at"] == $i;
  //         });

  //         if (count($arr_filter) == 0) {
  //           array_push($r_data, [
  //             "date_at" => $i,
  //             "total_val" => $fm->total_val / 1000,
  //           ]);
  //         }
  //       }
  //       $i -= 3600000;
  //     }
  //   }

  //   return response()->json($r_data, 200);
  // }
