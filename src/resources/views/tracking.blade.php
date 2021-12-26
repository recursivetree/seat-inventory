@extends('web::layouts.grids.12')

@section('title', "Title")
@section('page_header', "Title")


@section('full')
    @include("terminusinv::includes.status")

    <div class="card">
        <div class="card-body">
            <h5>
                Inventory Tracking
            </h5>

            <h6 class="mt-4">
                Corporations
            </h6>

            <table class="table table-striped mb-4">
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
                                <form action="{{ route("terminusinv.deleteTrackingCorporation") }}" method="POST">
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

            <form method="POST" action="{{ route("terminusinv.addTrackingCorporation") }}" class="border rounded p-4">
                <h6>Add Corporation</h6>
                @csrf
                <div class="form-group">
                    <label for="addTrackingCorporationCorporationInput">Location</label>
                    <select
                            placeholder="enter a corp name or ticker like 'Terminus.' or 'TRM.' ..."
                            class="form-control basicAutoComplete" type="text"
                            autocomplete="off"
                            id="addTrackingCorporationCorporationInput"
                            data-url="{{ route("terminusinv.trackingCorporationSuggestions") }}"
                            name="id">
                    </select>
                </div>

                <button class="btn btn-primary" type="submit">Add Corporation</button>
            </form>

        </div>
    </div>
@stop

@push('javascript')
    <script src="@versionedAsset('terminusinventory/js/bootstrap-autocomplete.js')"></script>

    <script>
        $('.basicAutoComplete').autoComplete({
            resolverSettings: {
                requestThrottling: 250
            },
            minLength:0,
        });
    </script>
@endpush