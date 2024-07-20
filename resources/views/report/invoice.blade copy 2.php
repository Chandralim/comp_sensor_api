<!DOCTYPE html>
<html>
<head>
	<title>Laporan Transaksi</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
	<style type="text/css">
        .page-break {
            page-break-after: always;
        }

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
		<h2>Laporan Transaksi Tanggal  </h2>
	</center>

	<table id="mdt" class='table-bordered table-striped'>
		<thead>
			<tr>
				<th>No</th>
				
			</tr>
		</thead>
		<tbody>
			<tr>
                <td>
                {{$location_id}}
                </td>
            </tr>
		</tbody>
		
	</table>
    <div class="page-break"></div>
    <table id="mdt" class='table-bordered table-striped'>
		<thead>
			<tr>
				<th>No</th>
				
			</tr>
		</thead>
		<tbody>
			<tr>
                <td>
                {{$location_id}}
                </td>
            </tr>
		</tbody>
		
	</table>
</body>
</html>
