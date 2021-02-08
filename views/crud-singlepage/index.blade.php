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
                            @if(count($dtActions))
                                <th></th>
                            @endif
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
    @include('hailstorm::scripts.crud-utils')
    @include('hailstorm::crud-singlepage.datatable-celledit')
    <script type="text/javascript">
        $(function () {

            var storeRoute = '{{ route("{$routePrefix}.store") }}';
            var updateRoute = '{{ route("{$routePrefix}.update", "xXx") }}';
            var entityName = '{{ $entityName}}';
            var formFields = @json($formFields, JSON_PRETTY_PRINT);
            var indexFields = @json($indexFields, JSON_PRETTY_PRINT);

            //selectors
            var form = $("#form-create");
            var btnSave = $(".btn-save");
            var btnSaveText = "Add {{$entityName}}";
            var btnSaveAnother = $("#modal-create .btn-save[value=save-another]");

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
                    //action column
                    @if(count($dtActions))
                    {
                        data: null,
                        searchable: false,
                        sortable: false,
                        defaultContent:
                            "@foreach($dtActions as $action)<button title='{{$action[0]}}' class='{{$action[3]}}'><i class='{{$action[1]}}''></i> @endforeach"
                    },
                    @endif
                ]
            };
            var finalOptions = $.extend({}, dtOptions, op);
            var dtable = $('#dtable').DataTable(finalOptions);

            function getCellEditConfigs() {
                let config = [];
                let count = 0;
                for (const [key, value] of Object.entries(indexFields)) {
                    if(value.type === 'text' || value.type === 'email' || value.type === 'number') {
                        config.push({
                            "column": count,
                            "type": "text",
                        });
                    }
                    if(value.type === 'select') {
                        let options = [];
                        if(value.options) {
                            for(const [i, v] of Object.entries(value.options)) {
                                options.push({
                                    value: i,
                                    display: v
                                });
                            }
                        }
                        config.push({
                            "column": count,
                            "type": "list",
                            "options": options
                        });
                    }
                    //TODO support select_db, checkbox, radio etc
                    /*if(value.type === 'checkbox') {
                        if(data[key]) {
                            $(`input[name=${key}]`).attr("checked", true);
                        } else {
                            $(`input[name=${key}]`).attr("checked", false);
                        }
                    }
                    if(value.type === 'radio') {
                        $(`input[name=${key}][value=${data[key]}]`).attr("checked", true);
                    }*/
                    count++;
                }

                return config;
            }

            dtable.MakeCellsEditable({
                "onUpdate": myCallbackFunction,
                "inputCss":'form-control',
                "columns": [@foreach($indexFields as $fieldName => $options) {{$loop->index}},@endforeach],
                /*"allowNulls": {
                    "columns": [3],
                    "errorClass": 'error'
                },*/
                "confirmationButton": { // could also be true
                    "confirmCss": 'btn btn-primary btn-sm',
                    "cancelCss": 'btn btn-link'
                },
                "inputTypes": getCellEditConfigs()
            });

            function myCallbackFunction (updatedCell, updatedRow, oldValue) {
                var data = updatedRow.data();
                var route = updateRoute.replace('xXx', data.id);
                data._method = 'PUT';
                data._token = $('input[name=_token]').val();
                $.ajax({
                    url: route,
                    method: "POST",
                    data: data
                })
                .done(function(data) {
                    if(data.success){
                        showSnackbar(data.data.message, true);
                    } else {
                        showSnackbar("Something went wrong!", false);
                    }
                })
                .fail(function(xhr) {
                    if(xhr.status == 422){
                        alert("Validation error");
                    } else {
                        showSnackbar("Something went wrong!", false);
                    }
                });

            }

            function onClickSaveButton(btn) {
                var btnObj = btnSave;
                var btnText = btnSaveText;

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



