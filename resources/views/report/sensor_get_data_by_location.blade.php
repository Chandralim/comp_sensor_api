<table>
  <thead>
    <tr>
      <th>No</th>
      <th>Date</th>
      <th>Flow Rate</th>
      <th>Deviation</th>
      <th>Totalizer</th>
      <th>COD</th>
      <th>PH</th>
      <th>Temperature</th>
      <th>Electricity ( UPS )</th>
    </tr>
  </thead>
  <tbody>
    @php $i=1 @endphp
    @foreach($data as $d)
    <tr>
      <td>{{ $i++ }}</td>
      <td>{{myDateFormat($d['created_at'],"Y-m-d H:i:s")}}</td>
      <td>{{$d['flow_rate']}}</td>
      <td>{{$d['deviation']}}</td>
      <td>{{$d['totalizer']}}</td>
      <td>-</td>
      <td>-</td>
      <td>-</td>
      <td>{{$d["electricity_is_off"]}}</td>
    </tr>
    @endforeach
  </tbody>
</table>