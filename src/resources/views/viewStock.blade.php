@extends('web::layouts.grids.12')

@section('title', "Title")
@section('page_header', "Title")


@section('full')
    @include("inventory::includes.status")

    <div class="card">
        <div class="card-header">
            <h3 class="card-title w-100 d-flex justify-content-between align-items-baseline">
                {{ $stock->name }}
                <a href="{{ route("inventory.editStock",$stock->id) }}" class="btn btn-primary">Edit</a>
            </h3>
        </div>
        <div class="card-body">

            <img src="{{ $stock->getIcon() }}" class="mb-3" alt="{{ $stock->name }} as image" width="256px">

            <dl class="row">
                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">{{  $stock->name }}</dd>

                <dt class="col-sm-3">Location</dt>
                <dd class="col-sm-9"><a href="{{ route("inventory.stockAvailability",["location_id"=>$stock->location->id,"location_id_text"=>$stock->location->name]) }}"> {{ $stock->location->name }}</a></dd>

                <dt class="col-sm-3">Minimum stock targer</dt>
                <dd class="col-sm-9">{{ $stock->amount }}</dd>

                <dt class="col-sm-3">Stock level warning threshold</dt>
                <dd class="col-sm-9">{{ $stock->warning_threshold }}</dd>

                <dt class="col-sm-3">Minimum stock level fulfilled</dt>
                <dd class="col-sm-9">
                    @include("inventory::includes.tickcross",["value"=>$stock->available_on_contracts + $stock->available_in_hangars >= $stock->amount])
                </dd>

                <dt class="col-sm-3">Last stock level update</dt>
                <dd class="col-sm-9">
                    {{ $stock->last_updated }}
                </dd>

                <dt class="col-sm-3">Priority</dt>
                <dd class="col-sm-9">@include("inventory::includes.priority",["priority"=>$stock->priority])</dd>

                <dt class="col-sm-3">Check contracts</dt>
                <dd class="col-sm-9">
                    @include("inventory::includes.tickcross",["value"=>$stock->check_contracts])
                </dd>

                <dt class="col-sm-3">Check corporation Hangar</dt>
                <dd class="col-sm-9">
                    @include("inventory::includes.tickcross",["value"=>$stock->check_corporation_hangars])
                </dd>

                <dt class="col-sm-3">Linked to a fitting</dt>
                <dd class="col-sm-9">
                    @include("inventory::includes.tickcross",["value"=>$stock->fitting_plugin_fitting_id])
                </dd>

                @if($stock->fitting_plugin_fitting_id)
                    <dt class="col-sm-3">Name of linked fitting</dt>
                    <dd class="col-sm-9">
                        {{ \RecursiveTree\Seat\Inventory\Models\Stock::fittingName($stock) }}
                    </dd>
                @endif

                @if($stock->amount - $stock->available_on_contracts - $stock->available_in_hangars > 0)
                    <dt class="col-sm-3">On Contracts</dt>
                    <dd class="col-sm-9 text-warning">
                        {{ $stock->available_on_contracts }}
                    </dd>
                    <dt class="col-sm-3">In Hangars</dt>
                    <dd class="col-sm-9 text-warning">
                        {{ $stock->available_in_hangars }}
                    </dd>
                @else
                    <dt class="col-sm-3">On Contracts</dt>
                    <dd class="col-sm-9 text-success">
                        {{ $stock->available_on_contracts }}
                    </dd>
                    <dt class="col-sm-3">In Hangars</dt>
                    <dd class="col-sm-9 text-success">
                        {{ $stock->available_in_hangars }}
                    </dd>
                @endif

                @if($stock->amount - $stock->available_on_contracts - $stock->available_in_hangars > 0)
                    <dt class="col-sm-3">Amount missing</dt>
                    <dd class="col-sm-9 text-danger">
                        {{ $stock->amount - $stock->available_on_contracts - $stock->available_in_hangars }}
                    </dd>
                @else
                    <dt class="col-sm-3">Amount missing</dt>
                    <dd class="col-sm-9 text-green">
                        {{ $stock->amount - $stock->available_on_contracts - $stock->available_in_hangars }}
                    </dd>
                @endif

                <dt class="col-sm-3">Categories</dt>
                <dd class="col-sm-9">
                    @foreach($stock->categories as $category)
                        <span class="badge badge-primary">
                            {{ $category->name }}
                        </span>
                    @endforeach
                    @if($stock->categories->isEmpty())
                        There are no categories added to this stock.
                    @endif
                </dd>
            </dl>

            <div class="btn-group">
                <a href="{{ route("inventory.stocks") }}" class="btn btn-primary">Back</a>

                <form id="delete-button" class="btn btn-danger" action="{{ route("inventory.deleteStock", $stock->id) }}" method="POST">
                    @csrf
                    <span type="submit">Delete</span>
                </form>

                @include("inventory::includes.multibuy",["multibuy" => $multibuy])

                @include("inventory::includes.multibuy",["multibuy" => $missing_multibuy, "title"=>"Multibuy Missing Items"])
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

    @if(!$missing->isEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Missing Items</h3>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <p class="align-self-baseline">All items missing to assemble the minimum stock level.</p>
                    @include("inventory::includes.multibuy",["multibuy" => $missing_multibuy, "title"=>"Multibuy Missing Items"])
                </div>

                <ul class="list-group">
                    @foreach($missing as $item)
                        <li class="list-group-item">
                            <img src="https://images.evetech.net/types/{{ $item->type_id }}/icon" height="24">
                            <span>
                                {{ $item->name() }}
                                {{ $item->amount }}x missing
                            </span>
                        </li>
                    @endforeach
                </ul>

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