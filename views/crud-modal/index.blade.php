@extends(config('hailstorm.crud.layout'))
@section('css')
    <style>
        .loader {
            border: 3px solid #f3f3f3;
            border-radius: 50%;
            border-top: 3px solid #F1C40F;
            width: 20px;
            height: 20px;
            -webkit-animation: spin 2s linear infinite; /* Safari */
            animation: spin 0.5s linear infinite;
        }

        /* Safari */
        @-webkit-keyframes spin {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        #snackbar {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            color: #fff;
            text-align: center;
            border-radius: 5px;
            padding: 16px;
            position: fixed;
            z-index: 1;
            left: 50%;
            top: 30px;
            height: 55px;
            font-size: 17px;
        }

        /*snackbar*/
        #snackbar.show {
            visibility: visible;
            -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
            animation: fadein 0.5s, fadeout 0.5s 2.5s;
        }

        @-webkit-keyframes fadein {
            from {bottom: 0; opacity: 0;}
            to {bottom: 30px; opacity: 1;}
        }

        @keyframes fadein {
            from {bottom: 0; opacity: 0;}
            to {bottom: 30px; opacity: 1;}
        }

        @-webkit-keyframes fadeout {
            from {bottom: 30px; opacity: 1;}
            to {bottom: 0; opacity: 0;}
        }

        @keyframes fadeout {
            from {bottom: 30px; opacity: 1;}
            to {bottom: 0; opacity: 0;}
        }
    </style>
@endsection

@section('hailstorm')
    <button class="btn btn-info btn-sm" id="btn-create">Add New</button>
    <div class="card">
        <div class="card-body">
            <table id="dtable" class="table table-striped table-hover table-sm " style="width:100%">
                <thead>
                <tr>
                    @foreach($indexFields as $fieldName => $options)
                    <th>{{$options['label']}}</th>
                    @endforeach
                    @if(count($dtActions))
                    <th></th>
                    @endif
                </tr>
                </thead>
            </table>
        </div>
    </div>

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
                        <div class="alert-wrap"></div>
                        {!! $form ?? '' !!}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-save">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="snackbar"></div>
@stop

@push('scripts')
    <script type="text/javascript">
        $(function () {

            var storeRoute = '{{ route("{$routePrefix}.store") }}';
            var updateRoute = '{{ route("{$routePrefix}.update", "xXx") }}';
            var entityName = '{{ $entityName}}';
            var formFields = @json($formFields, JSON_PRETTY_PRINT);

            //selectors
            var form = $("#form-create");
            var modal = $("#modal-create");
            var btnSave = $("#modal-create .btn-save");

            // utils
            function loading(selector, isLoading, label = "") {
                //selector can be jq selector string or a jq object
                var selectorObj;
                if (typeof selector === 'string' || selector instanceof String) {
                    selectorObj = $(selector);
                } else if (selector instanceof jQuery) {
                    selectorObj = selector;
                }
                if(isLoading) {
                    selectorObj.html("<div class='loader'></div>").attr('disabled', true);
                } else {
                    selectorObj.html(label).attr('disabled', false);
                }
            }

            function show_alert(msg, isSuccess = true) {
                if(isSuccess) {
                    $(".alert-wrap").html('<div class="alert alert-success">'+ msg+'</div>')
                } else {
                    $(".alert-wrap").html('<div class="alert alert-danger">'+ msg+'</div>')
                }
            }

            function remove_alert() {
                $(".alert-wrap").html('');
            }

            function printErrorMsg (msg) {
                var html = '';
                $.each( msg, function( key, value ) {
                    html += '<li>'+value+'</li>';
                });
                show_alert(html, false);
            }

            function showSnackbar(msg, isSuccess) {
                var x = document.getElementById("snackbar");
                x.style.backgroundColor = isSuccess ? '#0c0' : '#c00';
                x.innerText = msg;
                x.className = "show";
                setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
            }

            function setValueOnEditForm(data) {
                for (const [key, value] of Object.entries(formFields)) {
                    if(value.type === 'text' || value.type === 'email' || value.type === 'number') {
                        $(`input[name=${key}]`).val(data[key]);
                    }
                    if(value.type === 'select' || value.type === 'select_db') {
                        $(`select[name=${key}]`).val(data[key]);
                    }
                    if(value.type === 'checkbox') {
                        if(data[key]) {
                            $(`input[name=${key}]`).attr("checked", true);
                        } else {
                            $(`input[name=${key}]`).attr("checked", false);
                        }
                    }
                    if(value.type === 'radio') {
                        $(`input[name=${key}][value=${data[key]}]`).attr("checked", true);
                    }

                }
            }

            function resetForm() {
                for (const [key, value] of Object.entries(formFields)) {
                    if(value.type === 'text' || value.type === 'email' || value.type === 'number') {
                        $(`input[name=${key}]`).val('');
                    }
                    if(value.type === 'select' || value.type === 'select_db') {
                        $(`select[name=${key}]`).val('--Select One--');
                    }
                    if(value.type === 'checkbox') {
                        $(`input[name=${key}]`).attr("checked", false);
                    }
                    if(value.type === 'radio') {
                        $(`input[name=${key}]`).attr("checked", false);
                    }

                }
            }

            // data table
            var dtable = $('#dtable').DataTable({
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
                    @foreach($indexFields as $fieldName => $options)
                    {data: '{{$fieldName}}', name: '{{$fieldName}}'},
                    @endforeach
                    //action column
                    @if(count($dtActions))
                    {
                        data: null,
                        searchable: false,
                        sortable: false,
                        defaultContent:
                            "@foreach($dtActions as $action)<button class='btn btn-primary btn-sm {{$action[3]}}'><i class='{{$action[1]}}''></i> @endforeach"
                    },
                    @endif
                ]
            });

            // open account create modal
            $("#btn-create").on("click", function (e) {
                e.preventDefault();
                remove_alert();
                $("#head_text").html(`Add ${entityName}`);
                form.attr("action", storeRoute);
                form.attr("method", "POST");
                resetForm();
                modal.modal();
            });


            //insert data
            form.on("click", ".btn-save", function (e) {
                e.preventDefault();
                loading(btnSave, true);
                remove_alert();
                $(".print-error-msg").css('display','none');
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
                    .done(data => {
                        if(data.success){
                            dtable.ajax.reload();
                            form.trigger("reset");
                            modal.modal('hide');
                            showSnackbar(data.data.message, true);
                            loading(btnSave, false, "Save");
                        } else {
                            show_alert("Something went wrong!", false);
                            loading(btnSave, false, "Save");
                        }
                    })
                    .fail(function(xhr) {
                        //button.html("Save").attr("disabled", false);
                        if(xhr.status == 422){
                            printErrorMsg(xhr.responseJSON.errors);
                            loading(btnSave, false, "Save");
                        } else {
                            show_alert("Something went wrong!", false);
                            loading(btnSave, false, "Save");
                        }
                    });
            });

            //open Edit modal
            dtable.on('click','.btn-edit', function (e) {
                e.preventDefault();
                var data = dtable.row($(this).closest('tr')).data();
                remove_alert();
                $("#head_text").html(`Edit ${entityName}`);
                setValueOnEditForm(data);
                form.attr("action", updateRoute.replace('xXx', data.id));
                form.attr("method", "PUT");
                modal.modal();
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


        })
    </script>

    <script>
        console.log({dtable});
    </script>
@endpush



