@extends('layouts.auth')

@section('app-auth-css')
<style type="text/css">  
  .invalid-feedback{
    display: block;    
  }
</style>
@endsection

@section('content')
  <div class="login-logo">
    <a href="{{ route('home')}} "><b>{{ env('APP_NAME')}}</b></a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">You are only one step a way from your new password, change your password now.</p>

      <form action="{{ route('set-password.store') }}" method="post">
        @csrf
        <input type="hidden" name="user_id" value="{{$user->id}}">
        <div class="form-group">          
          <div class="input-group">
            <input type="password" name="password" class="form-control" placeholder="Password">
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-lock"></span>
              </div>
            </div>
          </div>
          @error('password')
            <span class="text-red text-red-500">{{ $message }}</span>
          @enderror
        </div>
        <div class="form-group">          
        <div class="input-group mb-3">
          <input type="password" class="form-control" name="password_confirmation" placeholder="Confirm Password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        @error('password_confirmation')
          <span class="text-red text-red-500">{{ $message }}</span>
        @enderror
      </div>
        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Change password</button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <p class="mt-3 mb-1">
        <a href="{{ route('login')}}">Login</a>
      </p>
    </div>
    <!-- /.login-card-body -->
  </div>
@endsection