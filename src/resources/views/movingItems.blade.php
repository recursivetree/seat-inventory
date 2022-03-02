@extends('web::layouts.grids.12')

@section('title', "Title")
@section('page_header', "Title")


@section('full')
    @include("inventory::includes.status")

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Add Items in Delivery
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route("inventory.addMovingItems") }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="multibuy-text">Multibuy of items waiting to be delivered:</label>
                    <textarea id="multibuy-text" class="form-control monospace-font text-sm" rows="8" name="multibuy_text" placeholder="{{ "Vargur 1\nGolem 1\nKronos 1\nPaladin 1" }}"></textarea>
                </div>

                <div class="form-group">
                    <label for="location">Location</label>
                    <select
                            placeholder="enter the name of a location"
                            class="form-control basicAutoComplete" type="text"
                            autocomplete="off"
                            id="location"
                            data-url="{{ route("inventory.locationSuggestions") }}"
                            name="location_id">
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Add</button>
            </form>
        </div>
    </div>

    @if(!$sources->isEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Items waiting to be delivered
                </h3>
            </div>
            <div class="card-body">
                @include("inventory::includes.inventorySourceList",["sources"=>$sources,"source_option_includes"=>["inventory::buttons.markMovingSourceDelivered"]])
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
            minLength:1,
        });
    </script>
@endpush