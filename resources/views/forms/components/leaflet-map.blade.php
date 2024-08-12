@php
    $url = $this->data['localizacao_url'];
@endphp
<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div>
        <div class="grid">
            <div class="col-start-1 row-start-1">
                <img src="{{$url}}" style="height: 300px; background: #4b5563"/>
            </div>
        </div>
    </div>
</x-dynamic-component>
