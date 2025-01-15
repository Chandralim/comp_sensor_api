<table>
  <thead>
    <tr>
      @php
      $colspan = 1 + count($sensor_lists[0])+ count($sensor_lists[1]);
      @endphp
      <th colspan="{{$colspan}}" style="font-weight: bold; text-align:center;"> Laporan analisa AVC Bangun Bandar </th>
    </tr>
    <tr>
      <th rowspan="2" style="font-weight: bold; border:1px solid #000; text-align:center;">Tanggal</th>
      @foreach($info as $v)
      <th colspan="6" style="font-weight: bold; border:1px solid #000; text-align:center;"> {{$v}} </th>
      @endforeach
    </tr>
    <tr>
      @foreach($sensor_lists as $v0)
      @foreach($v0 as $v)
      <th style="font-weight: bold; border:1px solid #000; text-align:center;">{{$v['name']}} ({{$v['unit_name']}})</th>
      @endforeach
      @endforeach

    </tr>
  </thead>
  <tbody>
    @php $i=1 @endphp
    @foreach($myData as $k => $d)
    <tr>
      <td style="border:1px solid #000; text-align:center;">{{date("d-m-Y H:i:s",strtotime($d['date_from']))}}</td>
      @foreach($sensor_lists as $v0)
      @foreach($v0 as $v)
      <th style="border:1px solid #000;">{{$v['sensor_postVal'][$k]}}</th>
      @endforeach
      @endforeach
    </tr>
    @endforeach
  </tbody>
</table>