<div class="row">
    <div class="col-sm-3"></div>
    <div class="col-sm-9">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="{{ $id ?? $field }}" name="{{$field}}">
            <label class="form-check-label" for="{{ $id ?? $field }}">{{$label}}</label>
        </div>
    </div>
</div>

{{--
<div class="form-group {{ $error_class ?? '' }}">
    <div class="col-sm-offset-3 col-sm-6">
        <div class="checkbox">
            <label for="{{ $id ?? '' }}">
                <input type="checkbox" name="{{$field}}" class="form-control" @if(isset($required) && $required) required @endif>
                {{$label}}
                <span class="help-block">{{ $error_text ?? '' }}</span>
            </label>
        </div>
    </div>
</div>
--}}
