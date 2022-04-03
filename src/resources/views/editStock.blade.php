@extends('web::layouts.grids.12')

@section('title', "Title")
@section('page_header', "Title")


@section('full')
    @include("inventory::includes.status")

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Stock</h3>
        </div>
        <div class="card-body">

            {{-- Multibuy --}}
            <div class="tab-pane show active" id="multibuy-text-tab-content">
                <form action="{{ route("inventory.saveStock") }}" method="POST">
                    @csrf

                    <input type="hidden" name="stock_id" value="{{ $stock->id }}">

                    <div class="form-group">
                        <label for="stock-name">Name</label>
                        <input type="text" id="stock-name" class="form-control" name="name" placeholder="Enter a name..." value="{{ $stock->name }}">
                    </div>

                    <div class="form-group">
                        <label for="multibuy-text">Multibuy</label>
                        <textarea id="multibuy-text" class="form-control monospace-font text-sm" rows="10"
                                  name="multibuy_text" placeholder="">{{ $multibuy }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="stock-amount">Amount</label>
                        <input type="number" id="stock-amount" class="form-control" name="amount" value="{{ $stock->amount }}">
                    </div>

                    <div class="form-group">
                        <label for="warning-threshold">Warning Threshold</label>
                        <input type="number" id="warning-threshold" class="form-control" name="warning_threshold" value="{{ $stock->warning_threshold }}">
                        <small class="text-muted">When the stock level falls below this value, a warning is raised.</small>
                    </div>

                    <div class="form-group">
                        <label for="fit-location">Location</label>
                        <select
                                placeholder="enter the name of a location"
                                class="form-control basicAutoComplete" type="text"
                                autocomplete="off"
                                id="fit-location"
                                data-url="{{ route("inventory.locationSuggestions") }}"
                                name="location_id">
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="multibuy_priority">Priority</label>
                        <select name="priority" id="multibuy_priority" class="form-control">
                            <option value="0" @if($stock->priority==0) selected @endif>Very Low</option>
                            <option value="1" @if($stock->priority==1) selected @endif>Low</option>
                            <option value="2" @if($stock->priority==2) selected @endif>Normal</option>
                            <option value="3" @if($stock->priority==3) selected @endif>Preferred</option>
                            <option value="4" @if($stock->priority==4) selected @endif>Important</option>
                            <option value="5" @if($stock->priority==5) selected @endif>Critical</option>
                        </select>
                    </div>

                    <div class="form-check">
                        <input
                                type="checkbox"
                                id="multibuy_check-corporation-hangars"
                                class="form-check-input"
                                name="check_corporation_hangars"
                                @if($stock->check_corporation_hangars) checked @endif>
                        <label for="multibuy_check-corporation-hangars">Check in corporation hangars</label>
                    </div>

                    <div class="form-check">
                        <input
                                type="checkbox"
                                id="multibuy_check-contracts"
                                class="form-check-input"
                                name="check_contracts"
                                @if($stock->check_contracts) checked @endif>
                        <label for="multibuy_check-contracts">Check contracts</label>
                    </div>

                    <div class="d-flex">
                        <button type="submit" class="btn btn-primary m-1">Submit</button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary m-1">Back</a>
                    </div>

                </form>
            </div>


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
            minLength: 1,
        });

        $('#fit-location').autoComplete('set', {
            value: "{{ $stock->location->id }}",
            text: "{{ $stock->location->name }}"
        });
    </script>
@endpush