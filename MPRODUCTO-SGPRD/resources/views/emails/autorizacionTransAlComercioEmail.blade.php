@component('mail::message')
<style>

.centerImg {
  display: block;
  margin-left: auto;
  margin-right: auto;
  width: 180px; height: 61px;
}

</style>
<table>
	<tr>
		<td>
			<img alt="Banplus" class="centerImg" src="{{asset('img/banplus.png')}}"/>
		</td>
	</tr>
	<tr>
		<td>
		</td>
	</tr>
    <tr>
        <td>
		  <p>Querido establecimiento: <strong>{{ $desc_comercio }}</strong></p>		          
          <p>Se aprobó una transacción con las siguientes características: </p>
		  <p>Cliente: <strong>{{ $nombre_completo }}</strong></p>		  
          <p>Monto {{ $moneda }}.: <strong>{{ $montos }}</strong></p>
          <p>Referencia: <strong>{{ $idTrans }}</strong></p>          
          <p>Fecha y hora: <strong>{{ $hoy }}</strong></p>
		  <p>Le invitamos a seguir disfrutando de la atención, productos y servicios que le brinda Banplus. Si desea contactarnos puede hacerlo a través del Centro de Atención President’s Club, vía WhatsApp +58 412 – Banplus (2267587), con una llamada al +58 212 909.20.03 o escribanos al correo electrónico: presidentsclub@banplus.com</p>
          
<p>&nbsp;</p>
        </td>
    </tr>
</table>

@endcomponent
