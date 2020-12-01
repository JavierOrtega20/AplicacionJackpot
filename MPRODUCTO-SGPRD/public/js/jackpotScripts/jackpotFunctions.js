//función para traer la consulta de un comercio

function show_transacciones(id,requiere_pin = 0) {
	
  $("#idTransR").val(id);
  $("#validate_pin").val(requiere_pin);  
  
  if(requiere_pin == 1){	  
	$("#no_requiere_pin").hide(1000);
	$("#h_no_requiere_pin").hide(1000);
	$("#requiere_pin").show("slow");
	$("#h_requiere_pin").show("slow");	
  }
  else{
	$("#requiere_pin").hide(1000);
	$("#h_requiere_pin").hide(1000);
	$("#no_requiere_pin").show("slow");
	$("#h_no_requiere_pin").show("slow");	  
  }

  var url = window.location;


  var pat = /filter/;

  // alert(pat.test(url));


  if (pat.test(url) == true) {
    url = String(url);

    url = url.replace("/filter", '');

  }

  $.ajax({
    data: '',
    url: url + '/' + id,
    method: 'GET',
    cache: false,
    processData: false,
    contentType: false,
    success: function (data) {
      
      $("#id").html('<li><h3><span class="font-normal">Transacción: </span>' + data['data'][0].idTrans + '</h3></li>');
      $("#idTrans").val(document.getElementById("idTrans").value = data['data'][0].idTrans);
      $("#idTransR").val(document.getElementById("idTrans").value = data['data'][0].idTrans);
      $("#idTransC").val(document.getElementById("idTrans").value = data['data'][0].idTrans);
      $("#fecha").html('<li><h3><span class="font-normal">Fecha: </span>' + data['data'][0].fechaTrans + '</h3></li>');
      $("#dni").html('<li><h3><span class="font-normal">Cédula: </span>' + data['data'][0].nacionalidad + '-' + data['data'][0].dni + '</h3></li>');
      $("#miembro").html('<li><h3><span class="font-normal">Cliente: </span>' + data['data'][0].first_name + ' ' + data['data'][0].last_name + '</h3></li>');
      $("#carnet").html('<li><h3><span class="font-normal">Tarjeta de Membresía: </span>' + data['data'][0].carnet.substr(-20, 4) + ' XXXX XXXX ' + data['data'][0].carnet.substr(-4) + '</h3></li>');
      $("#currency").html('<li><h3><span class="font-normal">Moneda: </span>' + data['data'][0].moneda + '</h3></li>');
      $("#comercio").html('<li><h3><span class="font-normal">Comercio: </span>' + data['data'][0].descripcionComercios + '</h3></li>');
      var amount_parts = data['data'][0].monto.split('.');
      var regexp = /(\d+)(\d{3})/;
      while (regexp.test(amount_parts[0])) {
        amount_parts[0] = amount_parts[0].replace(regexp, '$1' + '.' + '$2');
      }

      $("#mont").html('<li><h3><span class="font-normal">Monto: </span>' + amount_parts.join(',') + '</h3></li>');
      var amount_parts2 = data['data'][0].propina.split('.');
      var regexp2 = /(\d+)(\d{3})/;
      while (regexp.test(amount_parts2[0])) {
        amount_parts2[0] = amount_parts2[0].replace(regexp2, '$1' + '.' + '$2');
      }
      $("#propina").html('<li><h3><span class="font-normal">Propina: </span>' + amount_parts2.join(',') + '</h3></li>');
      // $("#banco").html('<li><h3><span class="font-normal">Banco: </span>'+data['data'][0].descripcionBancos+'</h3></li>');
    }	
  })

}


function show_banco(id) {

  var url = window.location;

  $.ajax({
    data: '',
    url: url + '/' + id,
    method: 'GET',
    cache: false,
    processData: false,
    contentType: false,
    success: function (data) {
      $("#descripcion").html('<li><h3><span class="font-normal">Nombre: </span>' + data['data'][0].descripcion + '</h3></li>');
      $("#rif").html('<li><h3><span class="font-normal">Rif: </span>' + data['data'][0].rif + '</h3></li>');
      $("#telefono1").html('<li><h3><span class="font-normal">Teléfono 1: </span>' + data['data'][0].telefono1 + '</h3></li>');
      if (data['data'][0].telefono2) {
        $("#telefono2").html('<li><h3><span class="font-normal">Teléfono 2: </span>' + data['data'][0].telefono2 + '</h3></li>');
      }
      $("#contacto").html('<li><h3><span class="font-normal">Contacto: </span>' + data['data'][0].contacto + '</h3></li>');
    }
  })

}

function show_monedas(id) {

  var url = window.location;

  $.ajax({
    data: '',
    url: url + '/' + id,
    method: 'GET',
    cache: false,
    processData: false,
    contentType: false,
    success: function (data) {
      $("#divisa").html('<li><h3><span class="font-normal">Divisa: </span>' + data['data'][0].mon_nombre + '</h3></li>');
      $("#simbolo").html('<li><h3><span class="font-normal">Símbolo: </span>' + data['data'][0].mon_simbolo + '</h3></li>');
      $("#descripcion").html('<li><h3><span class="font-normal">Descripción: </span>' + data['data'][0].mon_observaciones + '</h3></li>');

    }
  })

}

function show_comercio(rif) {

  var url = window.location;

  $.ajax({
    data: '',
    url: url + '/' + rif,
    method: 'GET',
    cache: false,
    processData: false,
    contentType: false,
    success: function (data) {
                //$("#id").html('<li><h3><span class="font-normal">ID: </span>'+data['data'][0].id+'</h3></li>');
                $("#estatus").html('<li><h3><span class="font-normal">Estatus del comercio: </span>'+data['data'][0].estatus+'</h3></li>');          
                $("#descripcion").html('<li><h3><span class="font-normal">Nombre: </span>'+data['data'][0].descripcion+'</h3></li>');
                $("#razon_social").html('<li><h3><span class="font-normal">Razón Social: </span>'+data['data'][0].razon_social+'</h3></li>');
                $("#rif").html('<li><h3><span class="font-normal">Rif: </span>'+data['data'][0].rif+'</h3></li>');  
                $("#direccion").html('<li><h3><span class="font-normal">Dirección: </span>'+data['data'][0].direccion+'</h3></li>');
                $("#telefono1").html('<li><h3><span class="font-normal">Teléfono 1: </span>'+data['data'][0].telefono1+'</h3></li>');
                if(data['data'][0].telefono2){
                $("#telefono2").html('<li><h3><span class="font-normal">Teléfono 2: </span>'+data['data'][0].telefono2+'</h3></li>');
                }
                $("#email").html('<li><h3><span class="font-normal">Correo Electrónico: </span>'+data['data'][0].email+'</h3></li>');
                 if (data['data'][0].propina_act == true) {
                   var propina = 'Activo';
                 }else{
                   var propina = 'Inactivo';
                 }
                $("#propina_act").html('<li><h3><span class="font-normal">Propina: </span>'+propina+'</h3></li>');
                $("#num_cta_princ").html('<li><h3><span class="font-normal">Nº Cuenta en Bolívares Consumo: </span>' + data['data'][0].num_cta_princ + '</h3></li>');
                if(data['data'][0].num_cta_princ_dolar != null){
                  $("#num_cta_princ_dolar").html('<li><h3><span class="font-normal">Nº Cuenta en Dólares Consumo: </span>' + data['data'][0].num_cta_princ_dolar + '</h3></li>');  
                }
                if(data['data'][0].num_cta_princ_euro != null){
                  $("#num_cta_princ_euro").html('<li><h3><span class="font-normal">Nº Cuenta en Euros Consumo: </span>' + data['data'][0].num_cta_princ_euro + '</h3></li>');  
                }
                
                
                if (data['data'][0].propina_act == true) {
                  $("#num_cta_secu").html('<li><h3><span class="font-normal">Nº Cuenta en Boĺivares Propina: </span>' + data['data'][0].num_cta_secu + '</h3></li>');
                  $("#num_cta_secu_dolar").html('<li><h3><span class="font-normal">Nº Cuenta en Dólares Propina: </span>' + data['data'][0].num_cta_secu_dolar + '</h3></li>');
                  $("#num_cta_secu_euro").html('<li><h3><span class="font-normal">Nº Cuenta en Euros Propina: </span>' + data['data'][0].num_cta_secu_euro + '</h3></li>');
                }else {
                  $("#num_cta_secu").html('');
                  $("#num_cta_secu_dolar").html('');
                  $("#num_cta_secu_euro").html('');
                }


    }
  })

}

function show_users(id) {
  var url = window.location;
  $.ajax({
    data: '',
    url: url + '/' + id,
    method: 'GET',
    cache: false,
    processData: false,
    contentType: false,
    success: function (data) {
      $("#carnet").html('');

      $("#dni").html('<li><h3><span class="font-normal">Cédula: </span>' + data['data'][0].nacionalidad + '-' + data['data'][0].dni + '</h3></li>');
      $("#first_name").html('<li><h3><span class="font-normal">Nombre: </span>' + data['data'][0].first_name + '</h3></li>');
      $("#last_name").html('<li><h3><span class="font-normal">Apellido: </span>' + data['data'][0].last_name + '</h3></li>');
      $("#email").html('<li><h3><span class="font-normal">Correo Electrónico: </span>' + data['data'][0].email + '</h3></li>');
      if (data['data'][0].carnets && data['data'][0].carnets.length) {
        var carnetText = ''
        data['data'][0].carnets.forEach(function (carnet) {
          carnetText += '<li><h3><span class="font-normal">Tarjeta de membresia: </span>' + carnet.carnet.substr(-20, 4) + ' XXXX XXXX ' + carnet.carnet.substr(-4) + ' (' + ((carnet.moneda) ? carnet.moneda : '-') + ')</h3></li>';
          carnetText += '<li><h3><span class="font-normal">Limite: </span>' + carnet.limite + '</h3></li>';


        });
        $("#carnet").html(carnetText);
      }
      $("#birthdate").html('<li><h3><span class="font-normal">Fecha de Nacimiento: </span>' + data['data'][0].birthdate + '</h3></li>');
    }
  })

}

function show_log_transacciones(id){
  
  var url = window.location;
  
  var pat = /filter/;


  if(pat.test(url) == true){
    url=String(url);

    url=url.replace("/filter",'');

  }  
  
  $("#idTable").empty();
  $("#TituloTransaccionId").empty();
  $('#TituloTransaccionId').append('Historial de la transacción ' + id);

  $.ajax({
            data:'',
            url:url+'/logTrans/'+id,
            method:'GET',
            cache:false,
            processData:false,
            contentType: false,
           success: function (data) {        
        var trHTML = '';
            $.each(data.data, function (i, item) {
              trHTML += '<div class="row"><div class="col-md-3">' + item.accion + '</div><div class="col-md-3">' + item.created_at + '</div><div class="col-md-2">' + item.username + '</div></div>';
            });
        $('#idTable').append(trHTML);
           }
    })

}



$(document).ready(function () {

  $('#perfil').on('change', function () {

    var selectValue = '.perfil' + $(this).val();
    //alert (selectValue);
    $('#rol').val($(this).val());

    $('.dinamico').children().hide(1000);
    $(selectValue).toggle(1000);
  });

});

$(document).ready(function () {

  $("input[name=propina]").on('change', function () {
    var selectValue = '.propina' + $(this).val();
    //alert (selectValue);
    $('#val_prop').val($(this).val());

    $('.dinamico').children().hide(1000);
    $(selectValue).toggle(1000);
  });

});
//$('.dinamicoPropina').children().hide();
$(document).ready(function () {
  $('#fk_id_comercio').on('change', function () {
    var url = window.location;
    $('.resetP').prop('checked', false);
    var selectValue = $(this).val();

    if (selectValue == "" && $('.dinamicoPropina').is(":visible")) {
      $('.dinamicoPropina').toggle(1000);
    } else {


      $.ajax({
        data: '',
        url: url + '/' + selectValue,
        method: 'GET',
        cache: false,
        processData: false,
        contentType: false,
        success: function (data) {
          var comerPropina = data['data'][0].propina_act;
          if (comerPropina == true && $('.dinamicoPropina').is(":hidden")) {
            $('.dinamicoPropina').toggle(1000);
          } else if (comerPropina == false && $('.dinamicoPropina').is(":visible")) {
            $('.dinamicoPropina').toggle(1000);
          }


        }
      })
    }



  });

});
