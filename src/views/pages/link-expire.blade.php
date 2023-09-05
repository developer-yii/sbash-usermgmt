@extends('layouts.auth')

@section('app-auth-css')
<style type="text/css">  
  .center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
  }
</style>
@endsection

@section('content')
  <div class="center">
    <p>{{__('usermgmt')['notification']['link_expire_message']}}</p>
  </div>
@endsection