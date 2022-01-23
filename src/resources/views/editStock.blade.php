@extends('web::layouts.grids.12')

@section('title', "Title")
@section('page_header', "Title")


@section('full')
    @include("inventory::includes.status")

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $stock->name }}</h3>
        </div>
        <div class="card-body">

            <dl class="row">
                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">{{  $stock->name }}</dd>

                <dt class="col-sm-3">Location</dt>
                <dd class="col-sm-9">{{ $stock->location->name }}</dd>

                <dt class="col-sm-3">Minimum stock level</dt>
                <dd class="col-sm-9">{{ $stock->amount }}</dd>

                <dt class="col-sm-3">Minimum stock level fulfilled</dt>
                <dd class="col-sm-9">
                    @if($stock->available_on_contracts + $stock->available_in_hangars >= $stock->amount)
                        <i class="fas fa-check" style="color: green;"></i>
                    @else
                        <i class="fas fa-times" style="color: red;"></i>
                    @endif
                </dd>

                <dt class="col-sm-3">Priority</dt>
                <dd class="col-sm-9">@include("inventory::includes.priority",["priority"=>$stock->priority])</dd>

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

                <dt class="col-sm-3">Linked to a fitting</dt>
                <dd class="col-sm-9">
                    @if($stock->fitting_plugin_fitting_id)
                        <i class="fas fa-check" style="color: green;"></i>
                    @else
                        <i class="fas fa-times" style="color: red;"></i>
                    @endif
                </dd>

                @if($stock->fitting_plugin_fitting_id)
                    <dt class="col-sm-3">Name of linked fitting</dt>
                    <dd class="col-sm-9">
                        {{ \RecursiveTree\Seat\Inventory\Models\Stock::fittingName($stock) }}
                    </dd>
                @endif

                <dt class="col-sm-3">On Contracts</dt>
                <dd class="col-sm-9">
                    {{ $stock->available_on_contracts }}
                </dd>

                <dt class="col-sm-3">In Hangars</dt>
                <dd class="col-sm-9">
                    {{ $stock->available_in_hangars }}
                </dd>

                <dt class="col-sm-3">Amount missing</dt>
                <dd class="col-sm-9">
                    {{ $stock->amount - $stock->available_on_contracts - $stock->available_in_hangars }}
                </dd>
            </dl>

            <div class="btn-group">
                <a href="{{ route("inventory.stocks") }}" class="btn btn-primary">Back</a>

                <form id="delete-button" class="btn btn-danger" action="{{ route("inventory.deleteStock", $stock->id) }}" method="POST">
                    @csrf
                    <span type="submit">Delete</span>
                </form>

                @include("inventory::includes.multibuy",["multibuy" => $multibuy])

                @include("inventory::includes.multibuy",["multibuy" => $missing, "title"=>"Multibuy Missing Items"])
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Items</h3>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <p class="align-self-baseline">All items required for one stock.</p>
                @include("inventory::includes.multibuy",["multibuy" => $multibuy])
            </div>

            @if($stock->items->isEmpty())
                <div class="alert alert-warning">
                    There are no items in this fit
                </div>
            @else
                <ul class="list-group">
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
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Missing Items</h3>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <p class="align-self-baseline">All items missing to assemble the minimum stock level.</p>
                @include("inventory::includes.multibuy",["multibuy" => $missing, "title"=>"Multibuy Missing Items"])
            </div>
            @if($stock->items->isEmpty())
                <div class="alert alert-warning">
                    There are no items in this stock
                </div>
            @else
                <ul class="list-group">
                    @foreach($stock->items as $item)
                        <li class="list-group-item">
                            <img src="https://images.evetech.net/types/{{ $item->type_id }}/icon" height="24">
                            <span>
                                {{ $item->type->typeName }}
                                {{ $item->missing_items }}x missing
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
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

        $("#multiBuyModalCopyButton").click(function () {
            const textarea = $("#multibuyTextArea")
            textarea.focus();
            textarea.select();
            document.execCommand('copy');
        })

        $("#delete-button").click(function () {
            $("#delete-button").submit();
        })
    </script>
@endpush