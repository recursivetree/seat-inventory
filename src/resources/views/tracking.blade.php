@extends('web::layouts.grids.12')

@section('title', "Title")
@section('page_header', "Title")


@section('full')
    @include("inventory::includes.status")

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Alliance</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route("inventory.addTrackingAlliance") }}">
                @csrf
                <div class="form-group">
                    <label for="addTrackingCorporationCorporationInput">Alliance</label>
                    <select
                            placeholder="enter an alliance name like 'The Pole Dancers' ..."
                            class="form-control basicAutoComplete" type="text"
                            autocomplete="off"
                            id="addTrackingCorporationCorporationInput"
                            data-url="{{ route("inventory.trackingAllianceSuggestions") }}"
                            name="id">
                    </select>
                </div>

                <div class="form-check">
                    <input
                            type="checkbox"
                            id="checkbox-alliance-autoadd"
                            class="form-check-input"
                            name="automate_corporations"
                    >
                    <label for="checkbox-alliance-autoadd">Automatically track member corporations</label>
                </div>

                <button class="btn btn-primary" type="submit">Add Alliance</button>
            </form>

        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Corporation</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route("inventory.addTrackingCorporation") }}">
                @csrf
                <div class="form-group">
                    <label for="addTrackingCorporationCorporationInput">Coporation</label>
                    <select
                            placeholder="enter a corp name or ticker like 'Terminus.' or 'TRM.' ..."
                            class="form-control basicAutoComplete" type="text"
                            autocomplete="off"
                            id="addTrackingCorporationCorporationInput"
                            data-url="{{ route("inventory.trackingCorporationSuggestions") }}"
                            name="id">
                    </select>
                </div>

                <button class="btn btn-primary" type="submit">Add Corporation</button>
            </form>

        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tracked Alliances</h3>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Alliance</th>
                        <th>Track member corporations</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($tracked_alliances as $alliance)
                    <tr>
                        <td>{{ $alliance->alliance->name }}</td>
                        <td>
                            @include("inventory::includes.tickcross",["value"=>$alliance->automate_corporations])
                        </td>
                        <td>
                            <form action="{{ route("inventory.deleteTrackingAlliance") }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ $alliance->id }}">
                                <button type="submit" class="btn btn-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                @if($tracked_alliances->isEmpty())
                    <tr>
                        <td colspan="3">This table is empty</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tracked Corporations</h3>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Corporation</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                    @foreach($tracked_corporations as $corporation)
                        <tr>
                            <td>{{ $corporation->corporation->name }}</td>
                            <td>
                                <form action="{{ route("inventory.deleteTrackingCorporation") }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $corporation->id }}">
                                    <button type="submit" class="btn btn-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if($tracked_corporations->isEmpty())
                        <tr>
                            <td colspan="2">This table is empty</td>
                        </tr>
                    @endif
                </tbody>
            </table>
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
            minLength:1,
        });
    </script>
@endpush