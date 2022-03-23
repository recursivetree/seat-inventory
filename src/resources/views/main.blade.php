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

    @foreach($categories as $category)
        <div class="card">
            <div class="card-body">

                <div class="d-flex align-items-baseline">
                    <h5 class="card-title mr-auto">{{ $category->name }}</h5>
                    <button class="btn btn-primary" data-toggle="collapse"
                            data-target="#categoryContent{{ $category->id }}">
                        Expand
                    </button>
                </div>

                <div class="collapse" id="categoryContent{{ $category->id }}">
                    <hr>
                    <div class="d-flex flex-wrap">

                        @foreach($category->stocks as $stock)
                            @php($available = $stock->available_on_contracts + $stock->available_in_hangars)
                            @php($missing = $stock->amount - $available)

                            <div class="card m-1" style="width: 16rem;">

                                <div class="card-header d-flex align-items-baseline">
                                    <h5 class="card-title mr-auto">
                                        <a href="{{ route("inventory.viewStock",$stock->id) }}">{{ $stock->name }}</a>
                                    </h5>
                                    <button class="btn btn-secondary">Modify</button>
                                </div>

                                <img src="https://images.evetech.net/types/587/render" class="" alt="...">

                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Location


                                        <b class="float-right" data-toggle="tooltip" data-placement="top"
                                           title="{{ $stock->location->name }}">
                                            @if(strlen($stock->location->name) > 20)
                                                {{ substr($stock->location->name,0,20) }}...
                                            @else
                                                {{$stock->location->name}}
                                            @endif
                                        </b>
                                    </li>
                                    <li class="list-group-item">Planned <b class="float-right">{{ $stock->amount }}</b>
                                    </li>

                                    <li class="list-group-item">Priority <b
                                                class="float-right">@include("inventory::includes.priority",["priority"=>$stock->priority])</b>
                                    </li>

                                    @if($available === 0)
                                        <li class="list-group-item list-group-item-danger">Available <b
                                                    class="float-right">{{ $available }}</b></li>
                                    @elseif($available <= ceil($stock->amount / 10.0))
                                        <li class="list-group-item list-group-item-warning">Available <b
                                                    class="float-right">{{ $available }}</b></li>
                                    @else
                                        <li class="list-group-item">Available <b
                                                    class="float-right">{{ $available }}</b></li>
                                    @endif

                                    @if($missing > 0)
                                        <li class="list-group-item list-group-item-warning">Missing <b
                                                    class="float-right">{{ $missing }}</b></li>
                                    @else
                                        <li class="list-group-item">Missing <b class="float-right">{{ $missing }}</b>
                                        </li>
                                    @endif

                                    <li class="list-group-item">Contracts <b
                                                class="float-right">{{ $stock->available_on_contracts }}</b></li>
                                    <li class="list-group-item">Corporation Hangar <b
                                                class="float-right">{{ $stock->available_in_hangars }}</b></li>
                                </ul>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    @endforeach
@stop

@push("javascript")
    <script>
        $("#locationFilter").select2({
            placeholder: "All locations",
            allowClear: true,
            ajax: {
                url: "{{ route("inventory.locationSuggestions") }}",
                processResults: function (data) {
                    return {
                        results: data.map(function (entry) {
                            entry.id = entry.value
                            return entry
                        })
                    }
                }
            }
        })
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
@endpush