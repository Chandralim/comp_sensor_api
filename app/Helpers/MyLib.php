<?php
//app/Helpers/Envato/User.php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use File;
use Request;
use App\Exceptions\MyException;

class MyLib
{
  public static $midUID = "2481";

  public static function admin()
  {
    $token = Request::bearerToken();
    if ($token == "") {
      throw new MyException(["message" => "Get user info cannot complete, please restart the apps"], 401);
    }

    $model_query = \App\Model\Admin::where("api_token", $token)->first();
    if (!$model_query) {
      throw new MyException(["message" => "Unauthenticate"], 401);
    }

    return $model_query;
  }

  public static function sensorToken()
  {
    $token = Request::bearerToken();
    if ($token == "") {
      throw new MyException(["message" => "Get sensor info cannot complete, please restart the apps"], 401);
    }

    $model_query = \App\Model\SensorToken::where("token", $token)->first();
    if (!$model_query) {
      throw new MyException(["message" => "Unauthenticate"], 401);
    }

    return $model_query;
  }

  public static function getMillis()
  {
    return round(microtime(true) * 1000);
  }

  public static function local_to_utc($tz, $datetime)
  {
    $date = new \DateTime($datetime);
    $millis = round((float)($date->format("U") . "." . $date->format("v")) * 1000);
    return $millis + ($tz * 60 * 1000);
  }

  public static function millisToDateUTC($millis)
  {
    $date = date("Y-m-d H:i:s", $millis / 1000);
    return $date;
  }

  public static function millisToDateLocal($millis)
  {
    $date = new \DateTime(self::millisToDateUTC($millis));
    $date->add(new \DateInterval('PT7H'));
    return $date->format('Y-m-d H:i:s');
  }

  public static function mime($ext)
  {
    $result = [
      "contentType" => "",
      "exportType" => "",
      "dataBase64" => "",
      "ext" => $ext
    ];

    switch ($ext) {
      case 'csv':
        $result["contentType"] = "text/csv";
        $result["exportType"] = \Maatwebsite\Excel\Excel::CSV;
        break;

      case 'xls':
        $result["contentType"] = "application/vnd.ms-excel";
        $result["exportType"] = \Maatwebsite\Excel\Excel::XLSX;
        break;

      case 'xlsx':
        $result["contentType"] = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
        $result["exportType"] = \Maatwebsite\Excel\Excel::XLSX;
        break;

      case 'pdf':
        $result["contentType"] = "application/pdf";
        $result["exportType"] = \Maatwebsite\Excel\Excel::DOMPDF;
        break;

      default:
        // code...
        break;
    }

    $result["dataBase64"] = "data:" . $result["contentType"] . ";base64,";
    return $result;
  }
}
