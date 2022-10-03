@extends('web::layouts.grids.12')

@section('title', "Title")
@section('page_header', "Title")


@section('full')
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

                <div class="form-group">
                    <label>Alliance Corporation Tracking</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="alliance_corporation_handling" id="alliance_corporation_handling1" value="manage" checked>
                        <label class="form-check-label" for="alliance_corporation_handling1">
                            Automatically update member corporations
                        </label>
                        <small class="form-text text-muted mt-0">
                            Adds all current members as well as adding and removing corporations that join or leave in the future.
                        </small>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="alliance_corporation_handling" id="alliance_corporation_handling2" value="add">
                        <label class="form-check-label" for="alliance_corporation_handling2">
                            Add all member corporations, but don't automatically update the list.
                        </label>
                        <small class="form-text text-muted mt-0">
                            Current members will be tracked, but newly joined corporations have to be added manually.
                        </small>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="alliance_corporation_handling" id="alliance_corporation_handling3" value="no">
                        <label class="form-check-label" for="alliance_corporation_handling3">
                            Ignore alliance members
                        </label>
                        <small class="form-text text-muted mt-0">
                            Check this option if you only want to track alliance contracts
                        </small>
                    </div>
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
                            @include("inventory::includes.tickcross",["value"=>$alliance->manage_members])
                        </td>
                        <td>
                            <form action="{{ route("inventory.deleteTrackingAlliance") }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ $alliance->alliance_id }}">
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
                    <th>Added by Alliance</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                    @foreach($tracked_corporations as $corporation)
                        <tr>
                            <td>{{ $corporation->corporation->name }}</td>
                            <td>
                                {{ $corporation->alliance->name }}
                            </td>
                            <td>
                                <form action="{{ route("inventory.deleteTrackingCorporation") }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $corporation->corporation_id }}">

                                    @if($corporation->managed_by)
                                        <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="top"
                                              title="This corporation was automatically added by the alliance '{{ $corporation->alliance->name }}' and cannot be manually removed.">
                                            <button type="submit" class="btn btn-danger" disabled style="pointer-events: none;">Remove</button>
                                        </span>
                                    @else
                                        <button type="submit" class="btn btn-danger">Remove</button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if($tracked_corporations->isEmpty())
                        <tr>
                            <td colspan="3">This table is empty</td>
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

        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
@endpush