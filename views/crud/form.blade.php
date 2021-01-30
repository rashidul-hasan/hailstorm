@extends(config('hailstorm.crud.layout'))

@section('hailstorm')

    <div class="row">
        <div class="col-md-12">
            {!! $form->render() !!}
        </div>
    </div>

@stop

