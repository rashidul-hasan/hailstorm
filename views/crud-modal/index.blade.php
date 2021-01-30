@extends(config('hailstorm.crud.layout'))


@section('hailstorm')
    <button class="btn btn-info btn-sm" id="btn-create">Add New</button>
    <div class="card">
        <div class="card-body">
            <table id="dtable" class="table table-striped table-hover table-sm " style="width:100%">
                <thead>
                <tr>
                    <th width="25%">Name</th>
                    <th width="10%">Type</th>
                    <th width="15%">Balance</th>
                    <th width="15%">Status</th>
                    <th width="10%">Default</th>
                    <th width="25%">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    <!--add account modal-->
    <div class="modal fade" id="modal-create" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="" method="POST" id="form-create"
                      class="form-horizontal" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="head_text"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger print-error-msg" style="display:none">
                            <ul></ul>
                        </div>
                        <div class="form-group row">
                            <label for="staticEmail" class="col-sm-3 col-form-label">Email</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" value="email@example.com">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputPassword" class="col-sm-2 col-form-label">Password</label>
                            <div class="col-sm-10">
                                <input type="password" class="form-control" id="inputPassword" placeholder="Password">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-save">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@stop

@push('scripts')
    <script type="text/javascript">
        $(function () {

            var storeRoute = '{{ route("{$routePrefix}.store") }}';
            var entityName = '{{ $entityName}}';

            /*/!*var dtable = $('#dtable').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                // pageLength: 50,
                ajax: "{{ $dataRoute }}",
                dom: 'Brtip',
                bFilter: false, // hide search box
                buttons: [
                    'copy'
                ],
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'type', name: 'type'},
                    {data: 'Balance', name: 'Balance', searchable: false, sortable: false},
                    {data: 'is_active', name:'is_active', render: function ( data, type, row ) {
                            return (data === 1)  ? '<span class="badge badge-success font-size-12">Active</span>'
                                : '<span class="badge badge-danger font-size-12">Inactive</span>';
                        }
                    },
                    {data: 'is_default', name:'is_default', render: function ( data, type, row ) {
                            return (data === 1)  ? '<span class="text-info font-size-12">Yes</span>'
                                : '<span class="badge badge-default font-size-12">No</span>';
                        }
                    },
                    {
                        data: null,
                        "searchable": false,
                        defaultContent: "<button class='btn btn-sm btn-info dt-btn-edit'><i class=\"fa " +
                            "fa-edit\"></i></button>" +
                            "<button class='btn btn-sm btn-danger dt-btn-delete'><i class=\"fa " +
                            "fa-trash\"></i></button>" +
                            "<button class='btn btn-sm btn-success dt-btn-addBalance'><i class=\"fa " +
                            "fa-plus\"></i>Add Money</button>"
                    },
                ]
            });*!/*/

            // open account create modal
            $("#btn-create").on("click", function (e) {
                console.log('ksdfjsdhgf');
                e.preventDefault();
                $(".print-error-msg").css('display','none');
                $("#head_text").html(`Add ${entityName}`);

                var form = $("#form-create");
                form.attr("action", storeRoute);
                form.attr("method", "POST");
                $("input[name=name]").val("");
                $("select[name=type]").val("");
                $("textarea[name=notes]").val("");
                $('input[name=is_active]').removeAttr('Checked');
                $('input[name=is_default]').removeAttr('Checked');
                $("#modal-create").modal();
            });


            //insert data
            $("#form-create").on("click", ".btn-save", function (e) {
                e.preventDefault();
                $(".print-error-msg").css('display','none');
                var form = $("#form-create");
                var action = form.attr("action");
                var data = new FormData(form[0]);
                data.append("_method", form.attr("method"));

                $.ajax({
                    url: action,
                    method: "POST",
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false
                })
                    .done(function(data) {
                        if(data.success){
                            dtable.ajax.reload();
                            form.trigger("reset");
                            $("#modal-create").modal('hide');
                            toastr.success(data.message, '');

                        } else {
                            toastr.error("Something went wrong!", 'Error');
                        }
                    })
                    .fail(function(xhr) {
                        //button.html("Save").attr("disabled", false);
                        if(xhr.status == 422){
                            printErrorMsg(xhr.responseJSON.errors);
                        } else {
                            toastr.error("Something went wrong!", 'Error');
                        }
                    });
            });

            //insert money
            $("#money-form-create").on("click", ".btn-save", function (e) {
                e.preventDefault();
                $(".print-error-msg").css('display','none');
                var form = $("#money-form-create");
                var action = form.attr("action");
                var data = new FormData(form[0]);
                data.append("_method", form.attr("method"));

                $.ajax({
                    url: action,
                    method: "POST",
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false
                })
                    .done(function(data) {
                        if(data.success){
                            dtable.ajax.reload();
                            form.trigger("reset");
                            $("#money-modal-create").modal('hide');
                            toastr.success(data.message, '');

                        } else {
                            toastr.error("Something went wrong!", 'Error');
                        }
                    })
                    .fail(function(xhr) {
                        //button.html("Save").attr("disabled", false);
                        if(xhr.status == 422){
                            printErrorMsg(xhr.responseJSON.errors);
                        } else {
                            toastr.error("Something went wrong!", 'Error');
                        }
                    });
            });

            //open Edit modal
            dtable.on('click','.dt-btn-edit', function (e) {
                e.preventDefault();
                $(".print-error-msg").css('display','none');
                $("#head_text").html('Edit Account');
                var data = dtable.row($(this).closest('tr')).data();
                $("input[name=name]").val(data.name);
                $("select[name=type]").val(data.type);
                $("textarea[name=notes]").val(data.notes);
                if(data.is_active) {
                    $("input[name=is_active]").attr("checked", true);
                } else {
                    $("input[name=is_active]").attr("checked", false);
                }
                if(data.is_default) {
                    $("input[name=is_default]").attr("checked", true);
                } else {
                    $("input[name=is_default]").attr("checked", false);
                }
                var form = $("#form-create");
                form.attr("action", route('accounts.update', data.id));
                form.attr("method", "PUT");

                $("#modal-create").modal();
            });

            //delete data
            dtable.on('click', '.dt-btn-delete', function(e) {
                e.preventDefault();
                var data = dtable.row($(this).closest('tr')).data();
                if (confirm("Delete this item?")) {
                    $.ajax({
                        url: route('expenses.destroy', data.id) ,
                        method: "DELETE",
                        data:{"_token":"{{csrf_token()}}"}
                    })
                        .done(function(data) {
                            if(data.success){
                                dtable.ajax.reload();
                                toastr.success(data.message, '');
                            } else {
                                toastr.error("Something went wrong!", 'Error');
                            }
                        })
                        .fail(function(xhr) {
                            if(xhr.status == 422){
                                alert("Validation error");
                            } else {
                                toastr.error("Something went wrong!", 'Error');
                            }
                        });
                }

            });

            // error masage
            function printErrorMsg (msg) {
                $(".print-error-msg").find("ul").html('');
                $(".print-error-msg").css('display','block');
                $.each( msg, function( key, value ) {
                    $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
                });
            }
        })
    </script>
@endpush



