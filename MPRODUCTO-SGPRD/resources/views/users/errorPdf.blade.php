<html>
	<head>
		<style>
			body {font-family: sans-serif;
				font-size: 10pt;
			}
			p {	margin: 0pt; }
			table.items {
				border: 0.1mm solid #000000;
			}
			td { vertical-align: top; }
			.items td {
				border-left: 0.1mm solid #000000;
				border-right: 0.1mm solid #000000;
			}
			table thead td { background-color: #EEEEEE;
				text-align: center;
				border: 0.1mm solid #000000;				
			}
			#cabecera{
				position:fixed; left:0; top:5;
			}
			#piepagina{
				position:fixed; left:0;bottom: 135;
			}
		</style>
		<title>Errores en Carga Masiva</title>
	</head>
	<body>
		<div style="text-align: right;">
				<br><br><br>
				<table width="96%" border = "0">      
					<tr>
						<td colspan = "3" rowspan="5" width="20%"><img src="" width="200" /></td>
						<td width="60%"></td>
						
					</tr>
					<tr>
						<td width="40%"> </td>
						<td width="20%" style="text-align: right;font-size: 12px">{{Carbon\Carbon::createFromDate()->format('d/m/Y h:i') }}</td>
					</tr>
					<tr>
						<td width="50%" style="text-align: center;font-size: 10px"><h1 style="margin-right: 80px">Lista de Errores en Carga Masiva</h1></td>
						<td width="50%" style="text-align: right;font-size: 12px">{{ Auth::user()->last_name.' '.Auth::user()->first_name }} <br> <br>
							<!--<strong>Nro. Nota:</strong> {{-- $despacho->nro --}}-->
						</td>  
					</tr>									
					<tr>
						<td width="80%"></td>
						<td width="20%">{{-- $despacho->nro --}}</td>
					</tr>
					
				</table>				
		</div>
		<h4>Errores en Carga Masiva</h4>
		<table border=1 class="items" width="100%" style="font-size: 10pt; border-collapse: collapse; " cellpadding="2" align="left">
			<thead>
				<tr>
					<th style="background-color: #F1F1F1 ;width: 10%;text-align: left"><strong>Nro Fila</strong></th>
					<th style="background-color: #F1F1F1 ;width: 60%;text-align: left"><strong>Descripción del Error</strong></th>
					<th style="background-color: #F1F1F1 ;width: 30%;text-align: left"><strong>Valor en Celda</strong></th>
				</tr>
			</thead>
			<tbody>
				@foreach($cargaErrors as $error)
				<tr>
					<td align="left" style="width: 10%;text-align: left">
						{{ strtoupper($error->error_filas) }}
					</td>
					<td align="left" style="width: 60%;text-align: left">
						{{ $error->error_descr }}
					</td>
					<td align="left" style="width: 30%;text-align: left">
						{{ strtoupper($error->fila_descr) }}
						
					</td>
				</tr>
				@endforeach
				@php $contador = 1; @endphp
				
			</tbody>
		</table>
		
	</body>
</html>