@if($sources->isEmpty())
    <p>There were no items found!</p>
@endif

@foreach($sources as $source)
    @php
        $items = (!isset($filter_item_type))? $source->items : $source->items->where("type_id",$filter_item_type);
    @endphp

    <div class="d-flex flex-row mb-0 mt-1 align-items-baseline rounded p-2" data-toggle="collapse"
        data-target="#{{ "inventorysourceid$source->id" }}" style="background-color: rgb(233,233,233);">

        <span class="mr-4">
            {{ $source->location->name }}
        </span>

        @if($source->source_type == "contract")
            <span class="mr-4">
                Contract
            </span>
        @endif

        <span class="mr-4">
            {{ $source->source_name }}
        </span>

        @if($source->last_updated)
            <span class="mr-4">
                {{ $source->last_updated }}
            </span>
        @endif

        <span class="ml-auto">
            <div class="btn-group" role="group">
                <button class="btn btn-secondary">Expand</button>

                @isset($source_option_includes)
                    @foreach($source_option_includes as $source_option_include)
                        @include($source_option_include,["source"=>$source])
                    @endforeach
                @endisset

                @include("inventory::includes.multibuy",["multibuy"=>\RecursiveTree\Seat\Inventory\Helpers\ItemHelper::itemListToMultiBuy(\RecursiveTree\Seat\Inventory\Helpers\ItemHelper::itemListFromQuery($items)),"title"=>"Multibuy"])
            </div>
        </span>
    </div>

    <ul class="list-group collapse" id="{{ "inventorysourceid$source->id" }}">
        @foreach( $items as $item)
            <li class="list-group-item">
                <img src="https://images.evetech.net/types/{{ $item->type_id }}/icon" height="24">
                <span>
                    {{ $item->amount }}x
                    {{ $item->type->typeName }}
                </span>
            </li>
        @endforeach
    </ul>
@endforeach