@extends('web::layouts.grids.12')

@section('title', "Title")
@section('page_header', "Title")


@section('full')
    @include("inventory::includes.status")

    <div class="card">
        <div class="card-body">
            <h5>
                Item Browser
            </h5>

            <h6>Filter</h6>

            <form action="{{ route("inventory.itemBrowser") }}" method="GET">

                <input type="hidden" name="filter" value="true">

                <div class="form-check">
                    <input
                            type="checkbox"
                            id="checkbox-corporation-hangars"
                            class="form-check-input"
                            name="checkbox_corporation_hangar"
                            @if($request->checkbox_corporation_hangar!=null or !isset($request->filter))
                            checked
                            @endif>
                    <label for="checkbox-corporation-hangars">Corporation Hangars</label>
                </div>

                <div class="form-check">
                    <input
                            type="checkbox"
                            id="checkbox-contracts"
                            class="form-check-input"
                            name="checkbox_contracts"
                            @if($request->checkbox_contracts!=null or !isset($request->filter))
                            checked
                            @endif>
                    <label for="checkbox-contracts">Contracts</label>
                </div>

                <div class="form-group">
                    <label for="stock-location">Location</label>
                    <select
                            placeholder="enter the name of a location"
                            class="form-control basicAutoComplete"
                            autocomplete="off"
                            id="stock-location"
                            data-url="{{ route("inventory.locationSuggestions") }}"
                            name="location_id">
                    </select>
                </div>

                <div class="form-group">
                    <label for="stock-item">Item</label>
                    <select
                            placeholder="enter the name of a item"
                            class="form-control basicAutoComplete"
                            autocomplete="off"
                            id="stock-item"
                            data-url="{{ route("inventory.itemTypeSuggestions") }}"
                            name="item_id">
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route("inventory.itemBrowser") }}" class="btn btn-secondary" role="button">Clear Filters</a>
                </div>
            </form>

            <h6>Items</h6>
            @if($inventory_sources->isEmpty())
                <p>There were no items found!</p>
            @endif

            @foreach($inventory_sources as $source)

                <ol class="breadcrumb mb-0 mt-1" data-toggle="collapse"
                    data-target="#{{ "inventorysourceid$source->id" }}">
                    <li class="breadcrumb-item">{{ $source->location->name }}</li>
                    <li class="breadcrumb-item">
                        @if($source->source_type == "corporation_hangar")
                            Corporation Hangar
                        @elseif($source->source_type == "contract")
                            Contract
                        @else
                            $source->source_type
                        @endif
                    </li>
                    <li class="breadcrumb-item">{{ $source->source_name }}</li>
                </ol>

                <ul class="list-group collapse" id="{{ "inventorysourceid$source->id" }}">
                    @foreach( ($filter_item_type==null)? $source->items : $source->items->where("type_id",$filter_item_type) as $item)
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
        </div>
    </div>
@stop

@push('javascript')
    <script src="@inventoryVersionedAsset('inventory/js/bootstrap-autocomplete.js')"></script>

    <script>
        $('.basicAutoComplete').autoComplete({
            resolverSettings: {
                requestThrottling: 250
            },
            minLength: 0,
        });

        @isset($request->location_id)
            $('#stock-location').autoComplete('set', {
                value: "{{ $request->location_id }}",
                text: "{{ $request->location_id_text }}"
            });
        @endisset

        @isset($request->item_id)
            $('#stock-item').autoComplete('set', {
                value: "{{ $request->item_id }}",
                text: "{{ $request->item_id_text }}"
            });
        @endisset

    </script>
@endpush