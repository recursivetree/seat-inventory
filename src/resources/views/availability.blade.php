@extends('web::layouts.grids.12')

@section('title', "Title")
@section('page_header', "Title")


@section('full')
    @include("inventory::includes.status")

    <div class="card">
        <div class="card-body">
            <h1>
                Stock Availability
            </h1>

            <h2>Filter</h2>

            <form action="{{ route("inventory.stockAvailability") }}" method="GET">

                <input type="hidden" name="filter" value="true">

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
                    <label for="stock-location">Stock/Fit</label>
                    <select
                            placeholder="enter the name of a stock/fit"
                            class="form-control basicAutoComplete"
                            autocomplete="off"
                            id="stock-id"
                            data-url="{{ route("inventory.stockSuggestions") }}"
                            name="stock_id">
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route("inventory.stockAvailability") }}" class="btn btn-secondary" role="button">Clear Filters</a>
                </div>
            </form>

            @isset($stock_levels)
                @isset($request->stock_id)
                    <h2>{{ $request->stock_id_text }}</h2>
                    <p>
                        <span>
                            You have {{ $stock_levels["target_amount"] }}x <i>{{ $request->stock_id_text }}</i> available.
                        </span>
                        <small class="text-muted">This is the max number you can get, including items from other fits</small>
                    </p>

                    <h2>Missing for the specified stock @include("inventory::includes.multibuy",["multibuy" => \RecursiveTree\Seat\Inventory\Helpers\ItemHelper::itemListToMultiBuy($stock_levels["target_missing"])])</h2>

                    @if(count($stock_levels["target_missing"])<1)
                        <div class="alert alert-warning">
                            There are no items missing
                        </div>
                    @else
                        <ul class="list-group">
                            @foreach($stock_levels["target_missing"] as $item)
                                <li class="list-group-item">
                                    <img src="https://images.evetech.net/types/{{ $item->type_id }}/icon" height="24">
                                    <span>
                                    {{ $item->amount }}x
                                    {{ $item->name() }}
                                </span>
                                </li>
                            @endforeach
                        </ul>
                        <small class="text-muted">These items are required to reach the minimal specified quantity of this stock</small>
                    @endif
                @endisset

                <h2>Missing at this location @include("inventory::includes.multibuy",["multibuy" => \RecursiveTree\Seat\Inventory\Helpers\ItemHelper::itemListToMultiBuy($stock_levels["missing_items"])])</h2>

                @if(count($stock_levels["missing_items"])<1)
                    <div class="alert alert-warning">
                        There are no items missing
                    </div>
                @else
                    <ul class="list-group">
                        @foreach($stock_levels["missing_items"] as $item)
                            <li class="list-group-item">
                                <img src="https://images.evetech.net/types/{{ $item->type_id }}/icon" height="24">
                                <span>
                                {{ $item->amount }}x
                                {{ $item->name() }}
                            </span>
                            </li>
                        @endforeach
                    </ul>
                    <small class="text-muted">All missing items at the location of this stock</small>
                @endif
            @endisset
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
            minLength:0,
        });

        @if(isset($request->location_id))
            $('#stock-location').autoComplete('set', {
                value: "{{ $request->location_id }}",
                text: "{{ $request->location_id_text }}"
            });
        @elseif(isset($request->stock_id))
            $('#stock-id').autoComplete('set', {
                value: "{{ $request->stock_id }}",
                text: "{{ $request->stock_id_text }}"
            });
        @endif

        $('#stock-location').on("autocomplete.select", function (evt, item) {
            $('#stock-id').autoComplete('clear');
        })

        $('#stock-id').on("autocomplete.select", function (evt, item) {
            $('#stock-location').autoComplete('clear');
        })
    </script>
@endpush