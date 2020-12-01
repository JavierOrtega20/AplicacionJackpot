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
          <p>Estimado(a) Sr(a).: {{ $nombre_completo }}</p>
          <p>Se aprobó una transacción con las siguientes características: </p>
          <p>Monto {{ $moneda }}.: <strong>{{ $montos }}</strong></p>
          <p>Referencia: <strong>{{ $idTrans }}</strong></p>
		  <p>Producto: <strong>{{ $producto }}</strong></p>
          <p>Establecimiento: <strong>{{ $desc_comercio }}</strong></p>
          <p>Fecha y hora: <strong>{{ $hoy }}</strong></p>
		  @if($es_giftcard)
			  <p>Saldo disponible {{ $moneda }}.: <strong>{{ $saldo_gift }}</strong></p>
			  <p>Para m&aacute;s informaci&oacute;n puede escribirnos a giftcard@banplus.com</p>
		  @else
			  <p>Le invitamos a seguir disfrutando de la atención, productos y servicios que le brinda Banplus. Si desea contactarnos puede hacerlo a través del Centro de Atención President’s Club, vía WhatsApp +58 412 – Banplus (2267587), con una llamada al +58 212 909.20.03 o escribanos al correo electrónico: presidentsclub@banplus.com</p>
		  @endif		  
          

<p>&nbsp;</p>
        </td>
    </tr>
</table>

@endcomponent
