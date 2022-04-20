@extends('web::layouts.grids.12')

@section('title', "Item Browser")
@section('page_header', "Item Browser")


@section('full')
    @include("inventory::includes.status")

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter</h3>
        </div>
        <div class="card-body">

            <p class="alert alert-warning">
                This part of the plugin is no longer maintained and only here because the new dashboard isn't fully functional yet.
                Please start to use the new dashboard
            </p>

            <form action="{{ route("inventory.itemBrowser") }}" method="GET">

                <div class="form-check">
                    <input
                            type="checkbox"
                            id="checkbox-corporation-hangars"
                            class="form-check-input"
                            name="checkbox_corporation_hangar"
                            @if($check_corporation_hangars)
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
                            @if($check_contracts)
                            checked
                            @endif>
                    <label for="checkbox-contracts">Contracts</label>
                </div>

                <div class="form-check">
                    <input
                            type="checkbox"
                            id="checkbox-in-transport"
                            class="form-check-input"
                            name="checkbox_in_transport"
                            @if($check_in_transport)
                            checked
                            @endif>
                    <label for="checkbox-in-transport">Items in transport</label>
                </div>

                <div class="form-check">
                    <input
                            type="checkbox"
                            id="checkbox-fitted-ships"
                            class="form-check-input"
                            name="checkbox_fitted_ships"
                            @if($check_fitted_ships)
                            checked
                            @endif>
                    <label for="checkbox-fitted-ships">Fitted ships (corporation hangar)</label>
                </div>

                <div class="form-group">
                    <label for="stock-location">Location</label>
                    <select
                            placeholder="enter the name of a location"
                            class="form-control basicAutoComplete"
                            autocomplete="off"
                            id="stock-location"
                            data-url="{{ route("inventory.legacyLocationSuggestions") }}"
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
        </div>
    </div>
    @if($show_results)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Items</h3>
            </div>
            <div class="card-body">
                @include("inventory::includes.inventorySourceList",["sources"=>$inventory_sources,"filter_item_type"=>$filter_item_type])
            </div>
        </div>
    @endif
@stop

@push('javascript')
    <script src="@inventoryVersionedAsset('inventory/js/bootstrap-autocomplete.js')"></script>

    <script>
        $('.basicAutoComplete').autoComplete({
            resolverSettings: {
                requestThrottling: 50
            },
            minLength: 0,
        });

        @isset($location_id)
            $('#stock-location').autoComplete('set', {
                value: "{{ $location_id }}",
                text: "{{ $location_id_text }}"
            });
        @endisset

        @isset($filter_item_type)
            $('#stock-item').autoComplete('set', {
                value: "{{ $filter_item_type }}",
                text: "{{ $filter_item_type_text }}"
            });
        @endisset

    </script>
@endpush