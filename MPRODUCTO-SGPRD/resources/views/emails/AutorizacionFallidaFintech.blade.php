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
          <p>Le informamos que se presentó una transacción rechazada por Fintech. El número de la transacción es: {{ $referencia }} y el mensaje recibido fue el siguiente: {{ $mensaje }}.</p>
          <p>&nbsp;</p>
        </td>
    </tr>
</table>
@endcomponent
