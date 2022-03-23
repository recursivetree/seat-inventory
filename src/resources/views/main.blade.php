@extends('web::layouts.grids.12')

@section('title', "Inventory Dashboard")
@section('page_header', "Inventory Dashboard")


@section('full')
    <div class="card">
        <div class="card-body">
            <label for="locationFilter">Location</label>
            <select class="form-control" id="locationFilter">
            </select>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-baseline">
                <h3 class="card-title mr-auto">Doctrine: Retributions</h3>
                <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#categoryContent">
                    Expand
                </button>
            </div>
            <div class="collapse" id="categoryContent">
                <hr>

                <div class="d-flex flex-wrap">

                    <div class="card m-1" style="width: 18rem;">
                        <div class="card-body d-flex align-items-baseline">
                            <h5 class="card-title mr-auto">Beam Retribution</h5>
                            <a href="" class="btn btn-primary">Details</a>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Location <b class="float-right">NEH-CS - Final Countdown</b></li>
                            <li class="list-group-item">Planned <b class="float-right">20</b></li>
                            <li class="list-group-item">Contracts <b class="float-right">10</b></li>
                            <li class="list-group-item">Corporation Hangar <b class="float-right">10</b></li>
                        </ul>
                    </div>

                    <div class="card m-1" style="width: 18rem;">
                        <div class="card-body d-flex align-items-baseline">
                            <h5 class="card-title mr-auto">Beam Retribution</h5>
                            <a href="" class="btn btn-primary">Details</a>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Location <b class="float-right">NEH-CS - Final Countdown</b></li>
                            <li class="list-group-item">Planned <b class="float-right">20</b></li>
                            <li class="list-group-item">Contracts <b class="float-right">10</b></li>
                            <li class="list-group-item">Corporation Hangar <b class="float-right">10</b></li>
                        </ul>
                    </div>

                    <div class="card m-1" style="width: 18rem;">
                        <div class="card-body d-flex align-items-baseline">
                            <h5 class="card-title mr-auto">Beam Retribution</h5>
                            <a href="" class="btn btn-primary">Details</a>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Location <b class="float-right">NEH-CS - Final Countdown</b></li>
                            <li class="list-group-item">Planned <b class="float-right">20</b></li>
                            <li class="list-group-item">Contracts <b class="float-right">10</b></li>
                            <li class="list-group-item">Corporation Hangar <b class="float-right">10</b></li>
                        </ul>
                    </div>

                    <div class="card m-1" style="width: 18rem;">
                        <div class="card-body d-flex align-items-baseline">
                            <h5 class="card-title mr-auto">Beam Retribution</h5>
                            <a href="" class="btn btn-primary">Details</a>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Location <b class="float-right">NEH-CS - Final Countdown</b></li>
                            <li class="list-group-item">Planned <b class="float-right">20</b></li>
                            <li class="list-group-item">Contracts <b class="float-right">10</b></li>
                            <li class="list-group-item">Corporation Hangar <b class="float-right">10</b></li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
@stop

@push("javascript")
    <script>
        $("#locationFilter").select2({
            placeholder:"All locations",
            allowClear: true,
            ajax: {
                url: "{{ route("inventory.locationSuggestions") }}",
                processResults: function (data){
                    return {
                        results: data.map(function (entry) {
                            entry.id = entry.value
                            return entry
                        })
                    }
                }
            }
        })
    </script>
@endpush