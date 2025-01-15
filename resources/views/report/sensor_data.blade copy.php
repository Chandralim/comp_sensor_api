<table>
  <thead>
    <tr>
      @php
      $colspan = 1 + count($sensor_lists);
      @endphp
      <th colspan="{{$colspan}}" style="font-weight: bold; text-align:center;"> Laporan analisa {{$info['name']}} AVC Bangun Bandar </th>
    </tr>
    <tr>
      <th style="border:1px solid #000; text-align:center;">Tanggal</th>
      @foreach($sensor_lists as $v)
      <th style="border:1px solid #000; text-align:center;">{{$v['name']}} ({{$v['unit_name']}})</th>
      @endforeach
    </tr>
  </thead>
  <tbody>
    @php $i=1 @endphp
    @foreach($myData as $k => $d)
    <tr>
      <td style="border:1px solid #000; text-align:center;">{{date("d-m-Y H:i:s",strtotime($d['date_from']))}}</td>
      @foreach($sensor_lists as $v)
      <th style="border:1px solid #000;">{{$v['sensor_postVal'][$k]}}</th>
      @endforeach
    </tr>
    @endforeach
  </tbody>
</table>