<?php

function writeIDFormat($val)
{
  return rtrim(rtrim((string)number_format($val, 2, ",", "."), "0"), ",");
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
