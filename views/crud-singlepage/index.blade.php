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

        #dtable tbody .form-control {
            display: initial;
            width: auto;
        }
    </style>
@endsection

@section('hailstorm')
    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <div class="alert-wrap"></div>
                    <form action="" id="form-create">
                        @csrf
                        {!! $form ?? '' !!}
                        <button class="btn btn-primary btn-save" type="submit" value="save">Add {{$entityName}}</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card">
                <div class="card-body">
                    <table id="dtable" class="table table-striped table-hover table-sm " style="width:100%">
                        <thead>
                        <tr>
                            @foreach($indexFields as $fieldName => $options)
                                <th>{{$options['label']}}</th>
                            @endforeach
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div id="snackbar"></div>
@stop

@if($includeView !== null)
    @includeIf($includeView)
@endif

@push('scripts')
    @include('hailstorm::crud-singlepage.datatable-celledit')
    <script type="text/javascript">
        $(function () {

            var storeRoute = '{{ route("{$routePrefix}.store") }}';
            var updateRoute = '{{ route("{$routePrefix}.update", "xXx") }}';
            var entityName = '{{ $entityName}}';
            var formFields = @json($formFields, JSON_PRETTY_PRINT);

            //selectors
            var form = $("#form-create");
            var btnSave = $(".btn-save");
            var btnSaveAnother = $("#modal-create .btn-save[value=save-another]");

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
            var op = {};
            if (typeof getDtOptions === "function") {
                op = getDtOptions();
            }

            var dtOptions = {
                processing: true,
                serverSide: true,
                searching: true,
                // pageLength: 50,
                ajax: "{{ $dataRoute }}",
                // dom: 'Brtip',
                // bFilter: false, // hide search box
                buttons: [
                    'copy'
                ],
                columns: [
                    @foreach($indexFields as $fieldName => $options)
                    {data: '{{$fieldName}}', name: '{{$fieldName}}'},
                    @endforeach
                ]
            };
            var finalOptions = $.extend({}, dtOptions, op);
            var dtable = $('#dtable').DataTable(finalOptions);

            dtable.MakeCellsEditable({
                "onUpdate": myCallbackFunction,
                "inputCss":'form-control',
                "columns": [0, 1],
                /*"allowNulls": {
                    "columns": [3],
                    "errorClass": 'error'
                },*/
                "confirmationButton": { // could also be true
                    "confirmCss": 'btn btn-primary btn-sm',
                    "cancelCss": 'btn btn-link'
                },
                "inputTypes": [
                    {
                        "column": 0,
                        "type": "text",
                        "options": null
                    },
                    {
                        "column":1,
                        "type": "list",
                        "inputCss": "form-control",
                        "options":[
                            { "value": "1", "display": "Beaty" },
                            { "value": "2", "display": "Doe" },
                            { "value": "3", "display": "Dirt" }
                        ]
                    }

                    // Nothing specified for column 3 so it will default to text

                ]
            });

            function myCallbackFunction (updatedCell, updatedRow, oldValue) {
                var data = updatedRow.data();
                console.log(data);
                $.ajax({
                    url: '/admin/expenses-cat/' + data.id,
                    method: "PUT",
                    data: data
                })
                    .done(function(data) {
                        if(data.success){
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

            function onClickSaveButton(btn) {
                var btnObj = btnSave;
                var btnText = "Save";

                loading(btnObj, true);
                remove_alert();
                var data = new FormData(form[0]);
                $.ajax({
                    url: storeRoute,
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
                        if(btn === 'save') {
                            showSnackbar(data.data.message, true);
                        }
                        loading(btnObj, false, btnText);
                    } else {
                        show_alert("Something went wrong!", false);
                        loading(btnObj, false, btnText);
                    }
                })
                .fail(function(xhr) {
                    if(xhr.status == 422){
                        printErrorMsg(xhr.responseJSON.errors);
                        loading(btnObj, false, btnText);
                    } else {
                        show_alert("Something went wrong!", false);
                        loading(btnObj, false, btnText);
                    }
                });
            }

            //insert data
            form.on("click", ".btn-save", function (e) {
                e.preventDefault();
                onClickSaveButton($(this).val());
            });

            //open Edit modal
            dtable.on('click','.btn-edit', function (e) {
                e.preventDefault();
                var data = dtable.row($(this).closest('tr')).data();
                remove_alert();
                btnSaveAnother.hide();
                $("#head_text").html(`Edit ${entityName}`);
                setValueOnEditForm(data);
                form.attr("action", updateRoute.replace('xXx', data.id));
                form.attr("method", "PUT");
                modal.modal();
            });

            //delete data
            dtable.on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var data = dtable.row($(this).closest('tr')).data();
                if (confirm("Delete this item?")) {
                    $.ajax({
                        url: updateRoute.replace('xXx', data.id) ,
                        method: "DELETE",
                        data:{"_token": $('input[name=_token]').val()}
                    })
                    .done(function(data) {
                        if(data.success){
                            dtable.ajax.reload();
                            showSnackbar(data.data.message, true);
                        } else {
                            showSnackbar("Something went wrong!", false);
                        }
                    })
                    .fail(function(xhr) {
                        showSnackbar("Something went wrong!", false);
                    });
                }

            });


        })
    </script>

    <script>
        console.log({dtable});
    </script>
@endpush



