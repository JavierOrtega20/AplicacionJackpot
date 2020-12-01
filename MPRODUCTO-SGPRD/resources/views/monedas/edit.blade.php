@extends('layouts.app')
@section('titulo', 'Monedas')

@section('contenido')

{!! Form::model($monedas, ['method' => 'PATCH','route' => ['monedas.update',$monedas->mon_id],'class'=>'form-horizontal']) !!}


 <div class="row wrapper border-bottom white-bg page-heading">
          <div class="col-lg-8">
            <h2><i class="fa fa-university"></i>   Monedas</h2>
            <ol class="breadcrumb">
              <li>
              <a href="{{ url('home') }}">Panel</a>
              </li>
              <li>Monedas
              </li>
              <li class="active">
              <strong>Editar Moneda</strong>
              </li>
            </ol>
            </div>
            <div class="col-lg-4">
              <div class="title-action">
                <a href="{{URL('list/monedas')}}" class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>
                <button type="submit" class="btn btn-primary" id="form-validation"><span class="btn-label">
                            <i class="fa fa-check"></i>
                        </span>Guardar</button>
              </div>
          </div>

        </div>
        <div class="wrapper wrapper-content animated fadeInRight ecommerce">

          @include('error')

            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Editar Moneda</h5>
                        </div>
                        <div class="ibox-content">

                              <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Divisa <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                     {!! Form::text('mon_nombre', null, array('placeholder' => 'Divisa','class' => 'form-control input-lg m-b', 'maxlength'=>20)) !!}
                                  </div>
                              </div>
                               <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Símbolo <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
                                    {!! Form::text('mon_simbolo', null, array('placeholder' => 'Símbolo','class' => 'form-control input-lg m-b', 'maxlength'=>7)) !!}

                                  </div>
                              </div>

                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Descripción <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">


                                    {!! Form::text('mon_observaciones', null, array('placeholder' => 'Descripción','class' => 'form-control input-lg m-b', 'maxlength'=>50 )) !!}
                                  </div>
                              </div>


                              <div class="hr-line-dashed"></div>

                              <div class="form-group">
                                <div class="title-action">
                                  <a href="{{url('list/monedas')}}" class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>
                                  <button type="submit" class="btn btn-primary" id="form-validation"><span class="btn-label">
                                              <i class="fa fa-check"></i>
                                          </span>Guardar</button>
                                </div>
                              </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
           {!! Form::close() !!}




@stop

@section('scripts')
    <script type="text/javascript">


function justNumbers(e)
        {
        var keynum = window.event ? window.event.keyCode : e.which;
        if ((keynum == 8) || (keynum == 46))
        return true;

        return /\d/.test(String.fromCharCode(keynum));
        }
</script>

<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>
{!! JsValidator::formRequest('App\Http\Requests\MonedasRequest') !!}
@endsection
