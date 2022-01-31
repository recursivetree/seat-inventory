@extends('web::layouts.grids.12')

@section('title', "Title")
@section('page_header', "Title")


@section('full')
    @include("inventory::includes.status")

    <div class="card">
        <div class="card-header">
            <h3 class="card-title w-100 d-flex justify-content-between align-items-baseline">
                Stocks
                <a href="{{ route("inventory.newStock") }}" class="btn btn-primary">New</a>
            </h3>
        </div>
        <div class="card-body">

            @if($fittings->isEmpty())
                <div class="alert alert-primary">
                    You haven't added any stocks to monitor yet.
                </div>
            @else
                <div class="list-group">
                    @foreach($fittings as $stock)
                        <a href="{{ route("inventory.viewStock",$stock->id) }}"
                           class="list-group-item list-group-item-action">
                            <b>{{ $stock->name }}</b>
                            {{ $stock->location->name }}
                            @if($stock->fitting_plugin_fitting_id != null)
                                <span class="badge badge-primary">Fitting Plugin</span>
                            @endif
                            @include("inventory::includes.priority",["priority"=>$stock->priority])
                        </a>
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