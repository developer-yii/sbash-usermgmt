@extends('layouts.auth')

@section('content')

<div class="login-box">
  <!-- /.login-logo -->
  <div class="">
    <div class="card-header text-center">
      <div class="col-12 mt-50 mb-50">
        @if(config('app.project_alias') == 'sFlow')
          <img class="mx-auto d-block" src="{{ asset('adminlte/dist/img/logo-login.png')}}" style="width:150px; align:center;">
        @else
          <img class="mx-auto d-block" src="{{ asset('img/sBash.png')}}" style="width:150px; align:center;">
        @endif
      </div>
    </div>
    <div class="card-body">
      <p class="login-box-msg">{{ __('usermgmt')['user']['page']['set_password_msg'] }}</p>                        

      <form action="{{ route('set-password.store') }}" method="post">
        @csrf
        <input type="hidden" name="user_id" value="{{$user->id}}">
        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ __('usermgmt')['user']['form']['password'] }}" required autofocus>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="input-group mb-3">
          <input type="password" name="password_confirmation" class="form-control" placeholder="{{ __('usermgmt')['user']['form']['confirm_password'] }}" required autocomplete="current-password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
            @error('password_confirmation')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="row">
          
          <!-- /.col -->
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">{{ __('usermgmt')['user']['buttons']['change_password']}}</button>
          </div>
          <!-- /.col -->
        </div>
      </form>              
      <p class="mt-3 mb-1">
        <a href="{{ route('login')}}" class="text-center">{{ __('usermgmt')['user']['buttons']['login']}}</a>
      </p>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
</div>
<!-- /.login-box -->
@endsection
