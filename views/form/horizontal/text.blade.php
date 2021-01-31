<div class="form-group {{ $error_class ?? '' }}">
    <label for="{{ $id ?? '' }}" class="col-sm-3 control-label">{!! $label ?? '' !!} @if($required) <span style="color: red">*</span>@endif</label>
    <div class="col-sm-6">
        <input type="text" name="{{ $field }}" class="form-control">
        <span class="help-block">{{ $error_text ?? '' }}</span>
    </div>
</div>
