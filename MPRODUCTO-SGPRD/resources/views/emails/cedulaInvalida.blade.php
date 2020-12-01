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
          <p>Estimado Centro de Atención President´s.</p>
          <p>Le informamos que se presentó una transacción fallida motivado a cédula inválida del cliente. A continuación detalles de la operación: </p>
          <p>Rif.: <strong>{{ $rif }}</strong></p>
          <p>Comercio: <strong>{{ $nombreComercio }}</strong></p>
          <p>Cédula: <strong>{{ $cedula }}</strong></p>
          <p>Fecha y Hora: <strong>{{ $fecha }}</strong></p>
          <p>&nbsp;</p>
        </td>
    </tr>
</table>
@endcomponent
