@extends('layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
  <div class="row mb-2">
    <div class="col-sm-6">
    <h1>{{ __('usermgmt::user.sidemenu') }}</h1>
    </div>
    <div class="col-sm-6">
    <ol class="breadcrumb float-sm-right">
      <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="fa fa-home"></i></a></li>
      <li class="breadcrumb-item active">{{ __('usermgmt::user.sidemenu') }}</li>
    </ol>
    </div>
  </div>
  <div class="row mb-2">
    <div class="col-sm-12 right-title">
      <div class="dropdown text-right content-right">
        @can('user_add')
          <button type="button" class="btn btn-block btn-success btn-sm " id="btn-add" data-toggle="modal" data-target="#myModal"><i class="fa fa-plus-square"></i> {{ __('usermgmt::user.page.add_user') }}</button>
        @endcan
        </div>
    </div>
  </div>
  </div><!-- /.container-fluid -->
</section>

<section class="content">
    <div class="row">
    <div class="col-12">
      <div class="card">
        <!-- /.box-header -->
        <div class="card-body">

          <div class="table-responsive">

            <table id="datatable" class="table table-bordered table-hover display nowrap margin-top-10 w-p100">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>{{ __('usermgmt::user.table.action') }}</th>
                </tr>
              </thead>
            </table>

          </div>

        </div>
        <!-- /.box-body -->
      </div>
    </div>
  </div>
</section>

<div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title mt-0" id="myModalLabel">{{ __('usermgmt::user.page.add_user') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('users.add') }}" method="post" id="forms"> 
        @csrf
        <input type="hidden" name="id_edit" id="id_edit" />
        <div class="modal-body">
          <div class="form-group">
            <label>{{ __('usermgmt::user.form.role') }}</label>
            <select class="form-control" name="role" id="role" required>
              <option value="">{{ __('usermgmt::user.form.choose_role') }}</option>
              @foreach ($role as $item)                                
                @if ($item->name != 'Developer')
                  <option value="{{ $item->name }}">{{ $item->name }}</option>
                @endif        
              @endforeach
            </select>
            <span class="error"></span>
          </div>
          <div class="form-group">
            <label>Name</label>
            <input type="text" class="form-control" value="{{ old('name') }}" name="name" id="name"
              placeholder:="Name" />
              <span class="error"></span>
          </div>

          <div class="form-group">
            <label>Email</label>
            <input data-parsley-type="email" type="text" value="{{ old('email') }}" name="email" id="email"
              class="form-control" />
              <span class="error"></span>
          </div>
          <div class="form-group">
            <label>{{ __('usermgmt::user.form.password') }}</label>
            <input type="password" value="{{ old('password') }}" name="password" id="password" class="form-control" />
            <span class="error"></span>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" id="btn-close" class="btn btn-secondary waves-effect"
            data-dismiss="modal">{{ __('usermgmt::user.buttons.close') }}</button>
          <button type="submit" class="btn btn-primary waves-effect waves-light">{{ __('usermgmt::user.buttons.save') }}</button>
        </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection

@section('app-js')
<script>
    $(document).ready(function() {
      $('#datatable').DataTable({
        processing: true,
        serverSide: true, 
        scrollX: true,
        fixedColumns: true,
        language: {          
              "sEmptyTable": '{{__('usermgmt::event.table.empty')}}',
              "sInfo":  '{{__('usermgmt::event.table.info.sh')}} ' +"_START_ "+'{{__('usermgmt::event.table.info.to')}} '+" _END_"+' {{__('usermgmt::event.table.info.of')}} '+"_TOTAL_"+' {{__('usermgmt::event.table.info.ent')}}',
              "sInfoEmpty": "Showing 0 to 0 of 0 entries",
              "sInfoFiltered": "(filtered from _MAX_ total entries)",
              "sInfoPostFix": "",
              "sInfoThousands": ",",
              "sLengthMenu": '{{__('usermgmt::event.table.info.length_a')}} '+'_MENU_'+' {{__('usermgmt::event.table.info.length_b')}}',
              "sLoadingRecords": "Loading...",
              "sProcessing": "Processing...",
              "sSearch": '{{__('usermgmt::event.table.sc')}}',
              "sZeroRecords": '{{__('usermgmt::event.table.nr')}}',
              "oPaginate": {
              "sFirst": "First",
              "sLast": "Last",
              "sNext": '{{__('usermgmt::event.table.paginate.next')}}',
              "sPrevious": '{{__('usermgmt::event.table.paginate.prev')}}',
              },
              "oAria": {
              "sSortAscending": ": activate to sort column ascending",
              "sSortDescending": ": activate to sort column descending"
              }
        },
        ajax: {
          url: "{{ route('users.list') }}",
          type: 'GET'
        },
        columns: [
          {
            data: 'name',
            name: 'name'
          },
          {
            data: 'email',
            name: 'email'
          },
          {
            data: 'role',
            name: 'role'
          },
          {
            data: 'actions',
            name: 'actions'
          },
        ],
      })
    });

    $('#myModal').on('hidden.bs.modal', function(e) {
      $('#forms').attr('action', "{{ route('users.add') }}")
      $('.error').html("");
      $('#myModalLabel').html('{{__('usermgmt::user.page.add_user')}}');
      $('#forms')[0].reset();
    })


    $(document).on('submit', 'form', function(event) {
      event.preventDefault();
      var $this = $(this);

      $.ajax({
        url: $(this).attr('action'),
        type: $(this).attr('method'),
        typeData: "JSON",
        data: new FormData(this),
        processData: false,
        contentType: false,
        beforeSend: function() {
            $($this).find('button[type="submit"]').prop('disabled', true);
        },
        success: function(res) {
          $($this).find('button[type="submit"]').prop('disabled', false);
          if (res.status == true) {
              $('#forms')[0].reset();
              $('#btn-close').click();
              toastr.success(res.message);
              $("#datatable").DataTable().ajax.reload();
              $('.error').html("");
          }
          else{
              first_input = "";
              $('.error').html("");
              $.each(res.message, function(key) {
                  if(first_input=="") first_input=key;
                  $('#'+key).closest('.form-group').find('.error').html(res.message[key]);
              });
              $('#forms').find("#"+first_input).focus();
          }          
        },
        error: function(xhr) {
          $($this).find('button[type="submit"]').prop('disabled', false);
          toastr.error(xhr.responseJSON.message)
        }
      })
    })

    $(document).on('click', '.edit', function() {
      $('#forms').attr('action', "{{ route('users.update') }}")
      $('#myModalLabel').html('{{__('usermgmt::user.page.edit_user')}}');
      let id = $(this).attr('id');
      $.ajax({
        url: "{{ route('users.edit') }}",
        type: "GET",
        data: {
          id: id,
          _token: "{{ csrf_token() }}"
        },
        success: function(res) {
          
          $('#btn-add').click();
          $('#id_edit').val(res.id);
          $('#name').val(res.name);
          $('#email').val(res.email);
          $('#role').val(res.roles[0].name);
        },
        error: function(xhr) {
          toastr.error(xhr.responseJSON.message);
        }
      })
    })


    $(document).on('click', '.delete', function() {      
      let id_del = $(this).attr('id');
      Swal.fire({
        title: "{{__('usermgmt::user.alert.alert_1')}}",
        text: '{{__('usermgmt::user.alert.alert_2')}}',
        icon: "{{__('usermgmt::user.icon.warning')}}",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: '{{__('usermgmt::user.icon.cancel')}}',
        confirmButtonText: "{{__('usermgmt::user.alert.alert_3')}}"
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('users.delete') }}",
            type: "post",
            data: {
              id: id_del,
              _token: "{{ csrf_token() }}"
            },
            success: function(res, status) {
              if (status = '200') {
                toastr.success(res.message);
                $("#datatable").DataTable().ajax.reload();
                // setTimeout(() => {
                //   Swal.fire({
                //     position: 'top-end',
                //     icon: 'success',
                //     title: res.message,
                //     showConfirmButton: false,
                //     timer: 1500
                //   }).then((res) => {
                //   })
                // })
              }
            },
            error: function(xhr) {              
              toastr.error(xhr.responseJSON.message);
              // Swal.fire({
              //   icon: 'error',
              //   title: 'Error',
              //   text: 'User delete failed',
              //   footer: 'User delete failed'
              // })
            }
          })
        }
      })
    })


    // $(document).on('click', '.detail', function() {
    //   let id_detail = $(this).attr('id');
    //   window.location.replace("{{ url('administrator/users/detail/') }}/" + id_detail);
    // })
</script>
@endsection