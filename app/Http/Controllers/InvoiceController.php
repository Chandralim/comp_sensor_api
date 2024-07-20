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
use PDF;

class InvoiceController extends Controller
{

  public function index(Request $request)
  {
    $this->admin = MyLib::admin();

    // $companies = \App\Model\Location::where("is_tenant","true")->orderBy("name","asc")->get();
    // return response()->json($companies ,200);

    //======================================================================================================
    // Pembatasan Data hanya memerlukan limit dan offset
    //======================================================================================================

    $limit = 30; // Limit +> Much Data
    if (isset($request->limit)) {
      if ($request->limit <= 250) {
        $limit = $request->limit;
      } else {
        throw new MyException("Max Limit 250");
      }
    }

    $offset = isset($request->offset) ? (int) $request->offset : 0; // example offset 400 start from 401

    //======================================================================================================
    // Jika Halaman Ditentutkan maka $offset akan disesuaikan
    //======================================================================================================
    $page = 1;
    if (isset($request->page)) {
      $page =  (int) $request->page;
      $offset = ($page * $limit) - $limit;
    }


    $get_date = new \DateTime("now");
    $get_date->add(new \DateInterval('PT7H'));
    // $get_date->add(new \DateInterval('P1M'));
    $d_to = $get_date->format('Y-m') . "-21 00:00:00";

    $date_t = new \DateTime($d_to);

    $my_querys = \App\Model\Location::where("is_tenant", "true")->orderBy("name", "asc")->get();

    $my_data_returns = [];

    $date_f = MyLib::utcMillis("2022-12-21 00:00:00");

    for ($i = ($page - 1) * $limit; $i < $page * $limit; $i++) {
      $start = clone $date_t;
      $start->sub(new \DateInterval('P' . $i . "M"));
      $x = MyLib::utcMillis($start->format("Y-m-d H:i:s"));


      if ($x < $date_f) {
        break;
      }

      foreach ($my_querys as $my_k => $my_q) {
        array_push($my_data_returns, [
          "location_id" => $my_q->id,
          "location_name" => $my_q->name,
          "period" => $x,
        ]);
      }
    }

    // $companies = \App\Model\Location::where("is_tenant","true")->orderBy("name","asc")->get();
    // return response()->json($my_data_returns ,200);
    return response()->json(
      [
        "data" => $my_data_returns,
      ],
      200
    );
  }


  public function download(Request $request)
  {
    $this->admin = MyLib::admin();

    //======================================================================================================
    // Validasi Input
    //======================================================================================================

    $rules = [
      'location_id' => 'required|exists:\App\Model\Location,id',
      'period' => 'required',
    ];

    $messages = [
      'location_id.required' => 'Location ID is required',
      'location_id.exists' => 'Location ID not listed',

      'period.required' => 'Period is required',
    ];

    $validator = \Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
      throw new ValidationException($validator);
    }

    if (!isset($request->_TimeZoneOffset)) {
      throw new MyException(
        [
          "message" => "Please Refresh Your Page, TimeZoneOffset Required",
        ],
        400
      );
    }

    $location_id = $request->location_id;
    $location = \App\Model\Location::where("id", $location_id)->first();

    $period = $request->period;

    $date_millis = [];

    $date_from = date("Y-m", $period / 1000) . "-21 00:00:00";
    array_push($date_millis, MyLib::utcMillis($date_from));

    $date_to = new \DateTime($date_from);
    $date_to->sub(new \DateInterval('P1M'));
    array_push($date_millis, MyLib::utcMillis($date_to->format("Y-m-d H:i:s")));

    // $date_millis = array_reverse($date_millis);
    $my_data_returns = [];

    $first_q = "";
    foreach ($date_millis as $k => $v) {
      $created_at = $v - 1000;
      $temp = \App\Model\AirLimbahFlowMeter::select('*', DB::Raw("CONCAT(" . $created_at . ") AS date_to_front_end,CONCAT(" . $v . ") AS date_to_compare"))->where("location_id", $location_id)->where("created_at", "<", $v)->orderBy("created_at", "desc")->limit(1);
      if ($k == 0) {
        $first_q = $temp;
      } else {
        $first_q = $first_q->unionAll($temp);
      }
    }

    if ($first_q) {
      $lists = $first_q->get();

      foreach ($lists as $k => $v) {
        if (count($lists) == 1) {
          $from = 0;
          $to = $v->total_val;
        }

        if ($k < count($lists) - 1) {
          $from = $lists[$k + 1]->total_val;
          $to = $v->total_val;
        }

        if (count($lists) == 1 || $k < count($lists) - 1) {
          array_push($my_data_returns, [
            "description" => "Waste Water",
            "created_at" => (int) $v->date_to_front_end,
            "usage" => number_format(($to - $from) / 1000, 2, ",", "."),
            "from" => number_format($from / 1000, 2, ",", "."),
            "to" => number_format($to / 1000, 2, ",", "."),
          ]);
        }
      }
    }

    $sendData = [
      "location_id" => $location_id,
      "location_name" => $location->name,
      "datas" => $my_data_returns
      // "date"=>$date,
      // "ttl_trx"=>$ttl_trx,
      // "trx_fee"=>number_format(30000,2,",","."),
      // "ttl_gestun"=>number_format($ttl_gestun,2,",","."),
      // "ttl_biaya"=>number_format($ttl_biaya,2,",","."),
      // "ttl_provider_cost"=>number_format($ttl_provider_cost,2,",","."),
      // "ttl_gross_profit"=>number_format($ttl_gross_profit,2,",","."),
      // "ttl_net_profit"=>number_format($ttl_net_profit,2,",","."),
      // "ttl_trx_fee"=>number_format($ttl_trx_fee,2,",","."),
      // "all_profit"=>number_format($all_profit,2,",","."),
      // "admin_name"=>$admin_name
    ];
    $date = new \DateTime();
    $filename = $date->format("YmdHis");
    Pdf::setOption(['dpi' => 150, 'defaultFont' => 'sans-serif']);
    $pdf = PDF::loadView('report.invoice', $sendData)->setPaper('a4', 'portrait');

    // return response(base64_encode($pdf->download($filename.'.pdf')))->header('Content-Type', 'application/pdf')
    // ->header('Content-Disposition', 'attachment; filename="custom-filename.pdf"');

    $mime = MyLib::mime("pdf");
    $bs64 = base64_encode($pdf->download($filename . "." . $mime["ext"]));

    $result = [
      "contentType" => $mime["contentType"],
      "data" => $bs64,
      "dataBase64" => $mime["dataBase64"] . $bs64,
      "filename" => $filename . "." . $mime["ext"]
    ];

    return $result;
    // return $data;
  }
}
