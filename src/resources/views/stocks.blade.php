@extends('web::layouts.grids.12')

@section('title', "Title")
@section('page_header', "Title")


@section('full')
    @include("inventory::includes.status")

    <div class="card">
        <div class="card-body">
            <h5>
                Inventory Tracking
            </h5>

            <h6>
                Stocks
            </h6>

            @if($fittings->isEmpty())
                <div class="alert alert-primary">
                    You haven't added any fits to monitor yet.
                </div>
            @else
                <div class="list-group">
                    @foreach($fittings as $stock)
                        <a href="{{ route("inventory.editStock",$stock->id) }}" class="list-group-item list-group-item-action">
                            <b>{{ $stock->name }}</b>
                            {{ $stock->location->name }}
                            @if($stock->fitting_plugin_fitting_id != null)
                                <span class="badge badge-primary">Fitting Plugin</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif

            <h6 class="mt-4">Add Fit</h6>

            <ul class="nav nav-tabs" id="fitTypeTab" data-tabs="tabs">
                <li class="nav-item">
                    <button class="nav-link active" data-toggle="tab" href="#fit-text-tab-content" type="button">Fits
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-toggle="tab" href="#multibuy-text-tab-content" type="button">
                        Multibuy
                    </button>
                </li>
                @if($has_fitting_plugin)
                    <li class="nav-item">
                        <button class="nav-link" id="fit-plugin-tab" data-toggle="tab" href="#fit-plugin-tab-content"
                                type="button">Fitting Plugin
                        </button>
                    </li>
                @endif
            </ul>

            <div class="tab-content mt-4" id="fitTypeTabContent">

                {{-- EFT Fits --}}
                <div class="tab-pane show active" id="fit-text-tab-content">
                    <form action="{{ route("inventory.addStock") }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="fit-text">Fit</label>
                            <textarea id="fit-text" class="form-control monospace-font text-sm" rows="10"
                                      name="fit_text"
                                      placeholder="{{ "[Pacifier, 2022 Scanner]\n\nCo-Processor II\nCo-Processor II\nType-D Restrained Inertial Stabilizers\nInertial Stabilizers II" }}"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="fit-amount">Amount</label>
                            <input type="number" id="fit-amount" class="form-control" name="amount" value="1">
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

                        <button type="submit" class="btn btn-primary">Submit</button>

                    </form>
                </div>

                {{-- Multibuy --}}
                <div class="tab-pane" id="multibuy-text-tab-content">
                    <form action="{{ route("inventory.addStock") }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="stock-name">Name</label>
                            <input type="text" id="stock-name" class="form-control" name="name"
                                   placeholder="Enter a name...">
                        </div>

                        <div class="form-group">
                            <label for="multibuy-text">Multibuy</label>
                            <textarea id="multibuy-text" class="form-control monospace-font text-sm" rows="10"
                                      name="multibuy_text" placeholder=""></textarea>
                        </div>

                        <div class="form-group">
                            <label for="stock-amount">Amount</label>
                            <input type="number" id="stock-amount" class="form-control" name="amount" value="1">
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

                        <button type="submit" class="btn btn-primary">Submit</button>

                    </form>
                </div>

                {{-- Plugin --}}
                @if($has_fitting_plugin)
                    <div class="tab-pane" id="fit-plugin-tab-content">
                        <form action="{{ route("inventory.addStock") }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label for="fit-plugin-fit">Fit</label>
                                <select
                                        placeholder="enter the name of the fit"
                                        class="form-control basicAutoComplete" type="text"
                                        autocomplete="off"
                                        id="fit-plugin-fit"
                                        data-url="{{ route("inventory.fittingPluginFittingsSuggestions") }}"
                                        name="fit_plugin_id">
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="fit-amount">Amount</label>
                                <input type="number" id="fit-amount" class="form-control" name="amount" value="1">
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

                            <button type="submit" class="btn btn-primary">Submit</button>

                        </form>
                    </div>
                @endif


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
    </script>
@endpush