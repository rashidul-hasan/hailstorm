@extends(config('hailstorm.crud.layout'))



@section('raindrops-action')
    {!! $buttons !!}
@stop

@section('hailstorm')

    <div class="row">
        <div class="col-md-12">
            {!! $table->render() !!}
        </div>
    </div>

@stop



