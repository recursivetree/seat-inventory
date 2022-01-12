@extends('web::layouts.grids.12')

@section('title', "Title")
@section('page_header', "Title")


@section('full')
    @include("terminusinv::includes.status")

    <div class="card">
        <div class="card-body">
            <h5>
                Stock Availability
            </h5>

            <h6>Filter</h6>

            <form action="{{ route("terminusinv.stockAvailability") }}" method="GET">

                <input type="hidden" name="filter" value="true">

                <div class="form-group">
                    <label for="stock-location">Location</label>
                    <select
                            placeholder="enter the name of a location"
                            class="form-control basicAutoComplete"
                            autocomplete="off"
                            id="stock-location"
                            data-url="{{ route("terminusinv.locationSuggestions") }}"
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
                            data-url="{{ route("terminusinv.stockSuggestions") }}"
                            name="stock_id">
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route("terminusinv.stockAvailability") }}" class="btn btn-secondary" role="button">Clear Filters</a>
                </div>
            </form>

{{--            @foreach($stocks as $stock)--}}
{{--                <p>{{ $stock->name }}</p>--}}
{{--            @endforeach--}}
        </div>
    </div>
@stop

@push('javascript')
    <script src="@terminusinvVersionedAsset('terminusinventory/js/bootstrap-autocomplete.js')"></script>

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