@if(isset($options->model) && isset($options->type))

    @if(class_exists($options->model))

        @php $relationshipField = $row->field; @endphp

        @if($options->type == 'belongsTo')

            @if(isset($view) && ($view == 'browse' || $view == 'read'))

                @php
                    $relationshipData = (isset($data)) ? $data : $dataTypeContent;
                    $model = app($options->model);
                    $query = $model::where($options->key,$relationshipData->{$options->column})->first();
                @endphp

                @if(isset($query))
                    <p>{!! str_replace(':', '<br>', $query->{$options->label}) !!}</p>
                @else
                    <p>-</p>
                @endif

            @else

                <select
                    class="form-control select2-ajax" name="{{ $options->column }}"
                    data-get-items-route="{{route('voyager.' . $dataType->slug.'.relation')}}"
                    data-get-items-field="{{$row->field}}"
                    @if(!is_null($dataTypeContent->getKey())) data-id="{{$dataTypeContent->getKey()}}" @endif
                    data-method="{{ !is_null($dataTypeContent->getKey()) ? 'edit' : 'add' }}"
                >
                    @php
                        $model = app($options->model);
                        $query = $model::where($options->key, old($options->column, $dataTypeContent->{$options->column}))->get();
                    @endphp

                    @if(!$row->required)
                        <option value="">-- Select --</option>
                    @endif

                    @foreach($query as $relationshipData)
                        <option value="{{ $relationshipData->{$options->key} }}" @if(old($options->column, $dataTypeContent->{$options->column}) == $relationshipData->{$options->key}) selected="selected" @endif>{{ $relationshipData->{$options->label} }}</option>
                    @endforeach
                </select>

            @endif

        @elseif($options->type == 'hasOne')

            @php
                $relationshipData = (isset($data)) ? $data : $dataTypeContent;

                $model = app($options->model);
                $query = $model::where($options->column, '=', $relationshipData->{$options->key})->first();

            @endphp

            @if(isset($query))
                <p>{!! str_replace(':', '<br>', $query->{$options->label}) !!}</p>
            @else
                <p>-</p>
            @endif

        @elseif($options->type == 'hasMany')

            @if(isset($view) && ($view == 'browse' || $view == 'read'))

                @php
                    $relationshipData = (isset($data)) ? $data : $dataTypeContent;
                    $model = app($options->model);

                    $selected_values = $model::where($options->column, '=', $relationshipData->{$options->key})->get()->map(function ($item, $key) use ($options) {
                        return $item->{$options->label};
                    })->all();
                @endphp

                @if($view == 'browse')
                    @php
                        $string_values = implode(", ", $selected_values);
                        if(mb_strlen($string_values) > 25){ $string_values = mb_substr($string_values, 0, 25) . '...'; }
                    @endphp
                    @if(empty($selected_values))
                        <p>-</p>
                    @else
                        <p>{!! str_replace('', '<br>', $string_values) !!}</p>
                    @endif
                @else
                    @if(empty($selected_values))
                        <p>-</p>
                    @else
                        <ul>
                            @foreach($selected_values as $selected_value)
                                <li>!! str_replace('', '<br>', $selected_value) !!}</li>
                            @endforeach
                        </ul>
                    @endif
                @endif

            @else
                <select
                    class="form-control @if(isset($options->taggable) && $options->taggable === 'on') select2-taggable @else select2-ajax @endif"
                    name="{{ $relationshipField }}[]" multiple
                    data-get-items-route="{{route('voyager.' . $dataType->slug.'.relation')}}"
                    data-get-items-field="{{$row->field}}"
                    @if(!is_null($dataTypeContent->getKey())) data-id="{{$dataTypeContent->getKey()}}" @endif
                    data-method="{{ !is_null($dataTypeContent->getKey()) ? 'edit' : 'add' }}"
                    @if(isset($options->taggable) && $options->taggable === 'on')
                    data-route="{{ route('voyager.'.\Illuminate\Support\Str::slug($options->table).'.store') }}"
                    data-label="{{$options->label}}"
                    data-error-message="{{__('voyager::bread.error_tagging')}}"
                    @endif
                >

                    @php
                        $selected_values = isset($dataTypeContent) ? $dataTypeContent->hasMany($options->model)->get()->map(function ($item, $key) use ($options) {
                            return $item->{$options->key};
                        })->all() : array();
                        $relationshipOptions = app($options->model)->all();
                        $selected_values = old($relationshipField, $selected_values);
                    @endphp

                    @if(!$row->required)
                        <option value="">-- Select --</option>
                    @endif

                    @foreach($relationshipOptions as $relationshipOption)
                        <option value="{{ $relationshipOption->{$options->key} }}" @if(in_array($relationshipOption->{$options->key}, $selected_values)) selected="selected" @endif>{{ $relationshipOption->{$options->label} }}</option>
                    @endforeach

                </select>

            @endif

        @elseif($options->type == 'belongsToMany')

            @if(isset($view) && ($view == 'browse' || $view == 'read'))

                @php
                    $relationshipData = (isset($data)) ? $data : $dataTypeContent;

                    $selected_values = isset($relationshipData) ? $relationshipData->belongsToMany($options->model, $options->pivot_table, $options->foreign_pivot_key ?? null, $options->related_pivot_key ?? null, $options->parent_key ?? null, $options->key)->get()->map(function ($item, $key) use ($options) {
            			return $item->{$options->label};
            		})->all() : array();

                @endphp

                @if($view == 'browse')
                    @if($options->table == 'media')
                        @php
                            $files = $selected_values;
                            $filesToDisplay = 1;
                        @endphp
                        @if ($files)
                            @foreach (array_slice($files, 0, $filesToDisplay) as $file)
                                <img src="@if( !filter_var($file, FILTER_VALIDATE_URL)){{ Voyager::image( $file ) }}@else{{ $file }}@endif" style="width:50px">
                            @endforeach
                            @if (count($files) > $filesToDisplay)
                                <br>
                                {{ __('voyager::media.files_more', ['count' => (count($files) - $filesToDisplay)]) }}
                            @endif
                        @else
                            <p>-</p>
                        @endif
                    @else
                        @php
                            $string_values = implode(", ", $selected_values);
                            if(mb_strlen($string_values) > 25){ $string_values = mb_substr($string_values, 0, 25) . '...'; }
                        @endphp
                        @if(empty($selected_values))
                            <p>-</p>
                        @else
                            <p>{!! str_replace(':', '<br>', $string_values) !!}</p>
                        @endif
                    @endif
                @else
                    @if(empty($selected_values))
                        <p>-</p>
                    @else
                        <ul>
                            @foreach($selected_values as $selected_value)
                                <li>{!! str_replace(':', '<br>', $selected_value) !!}</li>
                            @endforeach
                        </ul>
                    @endif
                @endif

            @else

                @if($options->table == 'media')
                    @php
                        $selected_values = $dataTypeContent->belongsToMany($options->model, $options->pivot_table, $options->foreign_pivot_key ?? null, $options->related_pivot_key ?? null, $options->parent_key ?? null, $options->key)->get()->map(function ($item, $key) use ($options) {
                        return $item->{$options->label};
                        })->all();
                        $content = json_encode($selected_values);
                        $content = '"'.str_replace('"', '\"', $content).'"';
                    @endphp
                    @include('voyager::formfields.media_picker')
                @else
                    <select
                        class="form-control @if(isset($options->taggable) && $options->taggable === 'on') select2-taggable @else select2-ajax @endif"
                        name="{{ $relationshipField }}[]" multiple
                        data-get-items-route="{{route('voyager.' . $dataType->slug.'.relation')}}"
                        data-get-items-field="{{$row->field}}"
                        @if(!is_null($dataTypeContent->getKey())) data-id="{{$dataTypeContent->getKey()}}" @endif
                        data-method="{{ !is_null($dataTypeContent->getKey()) ? 'edit' : 'add' }}"
                        @if(isset($options->taggable) && $options->taggable === 'on')
                            data-route="{{ route('voyager.'.\Illuminate\Support\Str::slug($options->table).'.store') }}"
                            data-label="{{$options->label}}"
                            data-error-message="{{__('voyager::bread.error_tagging')}}"
                        @endif
                    >
                        @php
                            $selected_values = isset($dataTypeContent) ? $dataTypeContent->belongsToMany($options->model, $options->pivot_table, $options->foreign_pivot_key ?? null, $options->related_pivot_key ?? null, $options->parent_key ?? null, $options->key)->get()->map(function ($item, $key) use ($options) {
                                return $item->{$options->key};
                            })->all() : array();
                            $relationshipOptions = app($options->model)->all();
                            $selected_values = old($relationshipField, $selected_values);
                        @endphp

                         @if(!$row->required)
                                <option value="">-- Select --</option>
                            @endif

                            @foreach($relationshipOptions as $relationshipOption)
                                <option value="{{ $relationshipOption->{$options->key} }}" @if(in_array($relationshipOption->{$options->key}, $selected_values)) selected="selected" @endif>{{ $relationshipOption->{$options->label} }}</option>
                            @endforeach

                    </select>
                @endif
            @endif

        @endif

    @else

        cannot make relationship because {{ $options->model }} does not exist.

    @endif

@endif
