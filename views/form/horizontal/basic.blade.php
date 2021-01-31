{{-- text, email, password, number --}}
<div class="form-group row {{ $error_class ?? '' }}">
    <label for="{{ $id ?? '' }}" class="col-sm-3 control-label" style="text-align: end">{!! $label ?? '' !!}@if(isset($required) && $required) <span style="color: red">*</span>@endif</label>
    <div class="col-sm-9">
        <input type="{{ $type }}" name="{{ $field }}" class="form-control" @if(isset($required) && $required) required @endif>
        <span class="help-block">{{ $error_text ?? '' }}</span>
    </div>
</div>
