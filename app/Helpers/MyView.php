<?php
// function imgBase64($url)
// {
//   $public_path = public_path($url);
//   $fgc = file_get_contents($public_path);
//   $type = pathinfo($fgc, PATHINFO_EXTENSION);

//   if ($type == "svg") {
//     $base64 = "data:image/svg+xml;base64,".base64_encode($fgc);
//   } else {
//     $base64 = "data:image/". $type .";base64,".base64_encode($fgc);
//   }
//   return $base64;
// }


// function dateFormalID($tanggal)
// {
//   $source = strtotime($tanggal);
//   $day=["Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"];
//   $month=["","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
//   return $day[date("w",$source)]." , ".date("d",$source)." ".$month[date("n",$source)]." ".date("Y",$source);
// }

function writeIDFormat($val)
{
  return rtrim(rtrim((string)number_format($val, 2, ",", "."), "0"), ",");

  // $string = preg_replace('/[^,0-9]/ig', "", $string);
  // $string = preg_replace(',', ".", $string);
  // $string = preg_replace('/,/ig', "", $string);
  //
  // $splitM =  explode(".",$string);
  // $splitM[0] = preg_replace('/\B(?=(\d{3})+(?!\d))/g', ".", $splitM[0]);
  //
  // if (count($splitM) > 1) {
  //     $splitM[1] = preg_replace('/0+$/', "", $splitM[1]);
  //   }
  //
  // $string = implode(".",$splitM);
  // return $string;
}

function myDateFormat($millis, $format = "Y-m-d H:i:s")
{
  return date($format, round($millis / 1000));
}

function millisToTime($millis)
{
  $time = "";

  $f_s = 1000;
  $f_m = 60 * $f_s;
  $f_h = 60 * $f_m;
  $f_day = 24 * $f_h;

  $day = floor($millis / $f_day) ?? 0;
  $millis -= $day * $f_day;

  $hour = floor($millis / $f_h) ?? 0;
  $millis -= $hour * $f_h;

  $minute = floor($millis / $f_m) ?? 0;
  $millis -= $minute * $f_m;

  $second = floor($millis / $f_s) ?? 0;
  $millis -= $second * $f_s;

  $ms = $millis;

  if ($day != 0) {
    $time .= $day . ($day == 1 ? "Day " : "Days ");
  }
  if ($hour != 0) {
    $time .= $hour . ($hour == 1 ? "Hour " : "Hours ");
  }
  if ($minute != 0) {
    $time .= $minute . ($minute == 1 ? "Minute " : "Minutes ");
  }
  if ($second != 0) {
    $time .= $second . ($second == 1 ? "Second " : "Seconds ");
  }
  if ($ms != 0) {
    $time .= $ms . ($ms == 1 ? "Milisecond " : "Miliseconds ");
  }

  return $time;
}
