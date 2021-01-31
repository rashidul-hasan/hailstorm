<div class="row">
    <div class="col-sm-3"></div>
    <div class="col-sm-9">
        <div class="form-group">
            @foreach($options as $key => $option)
                <div class="radio">
                    <label>
                        <input type="radio" name="{{$field}}" id="{{ $id ?? $field }}" value="{{$key}}">
                        {{$option}}
                    </label>
                </div>
            @endforeach
        </div>
    </div>
</div>
