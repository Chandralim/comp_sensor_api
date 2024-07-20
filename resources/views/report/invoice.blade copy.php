<!DOCTYPE html>
<html>
<head>
	<title>Laporan Transaksi</title>
</head>
<body>
	<style type="text/css">
		table {
			border-collapse: collapse;
			clear:both;
		}

		table tr td,
		table tr th{
			font-size: 9pt;
			padding:5px;
			text-align:right;
		}

		table#mdt tr td,
		table#mdt tr th{
			border: 1px solid #000;
			text-align: center;
		}

		table#mdt tbody tr:nth-child(odd){
			background: #cbcbcb;
		}
	</style>
	<center>
		<h2>Laporan Transaksi Tanggal {{$date}} ({{$admin_name}}) </h2>
	</center>

	<table id="mdt" class='table-bordered table-striped'>
		<thead>
			<tr>
				<th>No</th>
				<th>No Pesanan</th>
				<th>Nama Lengkap</th>
				<th>Nomor Rekening</th>
				<th>Nama Bank</th>
				<th>Platform</th>
				<th>Bank Transfer</th>
				<th>Provider Name</th>
				<th>Provider Spread</th>
				<th>Nominal</th>
				<th>Gross Profit</th>
				<th>Biaya</th>
				<th>Provider Cost</th>
				<th>Net Profit</th>
				<th>Keterangan</th>
			</tr>
		</thead>
		<tbody>
			@php $i=1 @endphp
			@foreach($datas as $d)
			<tr>
				<td>{{ $i++ }}</td>
				<td>{{$d['proof_number']}}</td>
				<td>{{$d['fullname']}}</td>
				<td>{{$d['account_number']}}</td>
				<td>{{$d['bank_name']}}</td>
				<td>{{$d['platform']}}</td>
				<td>{{$d['bank_transfer']['account_name']}}</td>
				<td>{{$d['provider_name']}}</td>
				<td>{{$d['provider_spread']}}%</td>
				<td style="text-align:right">Rp.{{$d['nominal_written']}}</td>
				<td style="text-align:right">Rp.{{$d['gp']}}</td>
				<td style="text-align:right">Rp.{{$d['transfer_fee']}}</td>
				<td>Rp.{{$d['provider_cost']}}</td>
				<td style="text-align:right">Rp.{{$d['np']}}</td>
				<td>{{$d['note']}}</td>
			</tr>
			@endforeach
		</tbody>
		<tfoot>
			<tr>
				<td colspan=9></td>
				<td style="text-align:right"><b>Rp.{{$ttl_gestun}}</b></td>
				<td style="text-align:right"><b>Rp.{{$ttl_gross_profit}}</b></td>
				<td style="text-align:right"><b>Rp.{{$ttl_biaya}}</b></td>
				<td style="text-align:right"><b>Rp.{{$ttl_provider_cost}}</b></td>
				<td style="text-align:right"><b>Rp.{{$ttl_net_profit}}</b></td>
				<td></td>
			</tr>
			<tr>
				<td colspan=13> Jumlah Transaksi : {{$ttl_trx}} x {{$trx_fee}}  </td>
				<td style="text-align:right"><b>Rp.{{$ttl_trx_fee}}</b></td>
				<td></td>
			</tr>
			<tr>
				<td colspan=13></td>
				<td style="text-align:right"><b>Rp.{{$all_profit}}</b></td>
				<td></td>
			</tr>

		</tfoot>
	</table>
</body>
</html>
