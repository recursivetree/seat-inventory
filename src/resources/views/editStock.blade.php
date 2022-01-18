@extends('web::layouts.grids.12')

@section('title', "Title")
@section('page_header', "Title")


@section('full')
    @include("inventory::includes.status")

    <div class="card">
        <div class="card-body">
            <h1>
                {{ $stock->name }}
                @if($stock->fitting_plugin_fitting_id != null)
                    <span class="badge badge-primary">Fitting Plugin</span>
                @endif
            </h1>

            <dl class="row">
                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">{{  $stock->name }}</dd>

                <dt class="col-sm-3">Location</dt>
                <dd class="col-sm-9">{{ $stock->location->name }}</dd>

                <dt class="col-sm-3">Minimum stock level</dt>
                <dd class="col-sm-9">{{ $stock->amount }}</dd>

                <dt class="col-sm-3">Check contracts</dt>
                <dd class="col-sm-9">
                    @if($stock->check_contracts)
                        <i class="fas fa-check" style="color: green;"></i>
                    @else
                        <i class="fas fa-times" style="color: red;"></i>
                    @endif
                </dd>

                <dt class="col-sm-3">Check corporation Hangar</dt>
                <dd class="col-sm-9">
                    @if($stock->check_corporation_hangars)
                        <i class="fas fa-check" style="color: green;"></i>
                    @else
                        <i class="fas fa-times" style="color: red;"></i>
                    @endif
                </dd>
            </dl>

            <h2>Items @include("inventory::includes.multibuy",["multibuy" => $multibuy])</h2>
            @if($stock->items->isEmpty())
                <div class="alert alert-warning">
                    There are no items in this fit
                </div>
            @else
                <ul class="list-group mb-4">
                    @foreach($stock->items as $item)
                        <li class="list-group-item">
                            <img src="https://images.evetech.net/types/{{ $item->type_id }}/icon" height="24">
                            <span>
                                {{ $item->amount }}x
                                {{ $item->type->typeName }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="d-flex">
                <a href="{{ route("inventory.stocks") }}" class="btn btn-primary m-1">Back</a>

                <form action="{{ route("inventory.deleteStock", $stock->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger m-1">Delete</button>
                </form>

                @include("inventory::includes.multibuy",["multibuy" => $multibuy])
            </div>
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

        $("#multiBuyModalCopyButton").click(function () {
            const textarea = $("#multibuyTextArea")
            textarea.focus();
            textarea.select();
            document.execCommand('copy');
        })
    </script>
@endpush