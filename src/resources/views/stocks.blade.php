@extends('web::layouts.grids.12')

@section('title', "Title")
@section('page_header', "Title")


@section('full')
    @include("inventory::includes.status")

    <div class="card">
        <div class="card-header">
            <h3 class="card-title w-100 d-flex justify-content-between align-items-baseline">
                Stocks
                <a href="{{ route("inventory.dashboard") }}" class="btn btn-primary">Dashboard</a>
            </h3>
        </div>
        <div class="card-body">
            <p class="alert alert-warning">
                This part of the plugin is no longer maintained and only here because the new dashboard isn't fully functional yet.
                Please start to use the new dashboard
            </p>

            @if($fittings->isEmpty())
                <div class="alert alert-primary">
                    You haven't added any stocks to monitor yet.
                </div>
            @else
                <div class="list-group">
                    @foreach($fittings as $stock)
                        @include("inventory::includes.stocklink",["stock"=>$stock])
                    @endforeach
                </div>
            @endif
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
    </script>
@endpush