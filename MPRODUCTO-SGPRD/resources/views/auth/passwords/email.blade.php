@extends('layouts.appLogin')

@section('contenido')

    <div class="passwordBox animated fadeInDown">
        <div class="row">

            <div class="col-md-12">
                <div class="ibox-content">

                    <h2 class="font-bold">Olvido de contraseña</h2>

                    <p>
                        Ingrese su correo electrónico para que el sistema le envie las instrucciones para restablecer su contraseña.
                    </p>

                    <div class="row">

                        <div class="col-lg-12">
                            @if (session('status'))
                          <div class="alert alert-success">
                              {{ session('status') }}
                          </div>
                            @endif
                            <form class="form-horizontal" method="POST" action="{{ route('password.email') }}">
                                        {{ csrf_field() }}
                                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <center><input id="email" type="email" class="form-control input-lg" style="width: 92%;" name="email" value="{{ old('email') }}" required></center>
                                    

                                        @if ($errors->has('email'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                                        @endif
                                </div>

                                <button type="submit" class="btn btn-primary block full-width m-b">Enviar instrucciones</button>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr/>
        <div class="row">
            <div class="col-md-6">
                <strong>Meritop C.A.</strong>
            </div>
            <div class="col-md-6 text-right">
               <small> &copy; 2018</small>
            </div>
        </div>
    </div>

@endsection
