
@extends('layouts.app')
@section('titulo', 'Bancos')

@section('contenido')

{!! Form::model($bancos, ['method' => 'PATCH','route' => ['bancos.update',$bancos->id],'class'=>'form-horizontal']) !!}


 <div class="row wrapper border-bottom white-bg page-heading">
          <div class="col-lg-8">
            <h2><i class="fa fa-university"></i>   Bancos</h2>
            <ol class="breadcrumb">
              <li>
              <a href="{{ url('home') }}">Panel</a>
              </li>
              <li>Bancos
              </li>
              <li class="active">
              <strong>Editar Banco</strong>
              </li>
            </ol>
            </div>
            <div class="col-lg-4">
              <div class="title-action">
                <a href="{{route('bancos.index')}}" class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>
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
                            <h5>Editar banco</h5>
                        </div>
                        <div class="ibox-content">
                            
                              <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Nombre <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                     {!! Form::text('descripcion', null, array('placeholder' => 'Nombre','class' => 'form-control input-lg m-b', 'value' =>'$bancos->descripcion')) !!}
                                  </div>
                              </div>
                               <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Rif <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
                                    {!! Form::text('rif', null, array('placeholder' => 'Rif','class' => 'form-control input-lg m-b', 'value' =>'$bancos->rif', 'maxlength' => '11')) !!}
                                    <span class="help-block m-b-none">Inserte el número de Rif sin carácteres especiales con solo estas iniciales. Ej: J, G, C.</span>
                                  </div>
                              </div>

                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Teléfono 1 <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">


                                    {!! Form::text('telefono1', null, array('placeholder' => 'Teléfono1','class' => 'form-control input-lg m-b', 'value' =>'$bancos->telefono1', 'onkeypress' => 'return justNumbers(event);')) !!}
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Teléfono 2</label>
                                  <div class="col-sm-10">
                                    {!! Form::text('telefono2', null, array('placeholder' => 'Teléfono2','class' => 'form-control input-lg m-b', 'value' =>'$bancos->telefono2', 'onkeypress' => 'return justNumbers(event);')) !!}
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Contacto <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
                                    {!! Form::text('contacto', null, array('placeholder' => 'Contacto','class' => 'form-control input-lg m-b', 'value' =>'$bancos->contacto')) !!}
                                  </div>
                              </div> 

                              <div class="hr-line-dashed"></div>
                              <div class="form-group">
                                <div class="title-action">
                                  <a href="{{route('bancos.index')}}" class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>
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
<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>
{!! JsValidator::formRequest('App\Http\Requests\BancoRequest') !!}
@endsection


    <script type="text/javascript">


function justNumbers(e)
        {
        var keynum = window.event ? window.event.keyCode : e.which;
        if ((keynum == 8) || (keynum == 46))
        return true;
         
        return /\d/.test(String.fromCharCode(keynum));
        }
</script>