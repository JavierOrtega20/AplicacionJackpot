@component('mail::message')
<style>
.center {
  text-align: center;
  letter-spacing: 0.3em;
  font-size: x-large;
}
.centerText {
  text-align: center;
}
.centerImg {
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
          <p>Estimado(a) Sr(a).: {{ $nombre_completo }}</p>
		  <p class="centerText"><strong>Su código de autorizaci&oacute;n</strong></p>
		  <p class="center"><strong>{{ $token }}</strong></p>
		  <p class="centerText"><strong>Entregue este c&oacute;digo al comercio</strong></p>
		  <p>Informamos transacción  President&#39;s Pay por {{ $moneda }} {{ $montos }} Producto: <strong>{{ $producto }}</strong> de fecha {{ $hoy }}.</p>
		  <p>Por seguridad, esta clave tendrá validez durante 20 minutos.</p>
		  <p>Le invitamos a seguir disfrutando de la atención, productos y servicios que le brinda Banplus. Si desea contactarnos puede hacerlo a través del Centro de Atención President's Club,  Vía WhatsApp 0412BANPLUS (+58 4122267587), +58 212 909.20.03 o escribanos al correo electrónico: presidentsclub@banplus.com</p>
		  
		<p>&nbsp;</p>

        </td>
    </tr>
</table>


@endcomponent

