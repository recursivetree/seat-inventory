@extends('web::layouts.grids.12')

@section('title', "Inventory Dashboard")
@section('page_header', "Inventory Dashboard")


@section('full')
    <div class="card">
        <div class="card-body">
            <form action="" method="GET" id="filterForm">
                <label for="locationFilter">Location</label>
                <select class="form-control" id="locationFilter" name="location_filter">
                    <option selected value="{{$location->id}}">{{ $location->name }}</option>
                </select>
                <small class="text-muted">Only show categories containing stocks at a specific location.</small>
            </form>
        </div>
    </div>

    @foreach($categories as $category)
        <div class="card">
            <div class="card-body">

                <div class="d-flex align-items-baseline">
                    <h5 class="card-title mr-auto" data-toggle="collapse" data-target="#categoryContent{{ $category->id }}">{{ $category->name }}</h5>
                    <button class="btn btn-primary" data-toggle="collapse" data-target="#categoryContent{{ $category->id }}">
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
{{--                            @if($location->id==$stock->location_id) background-color:red; @endif--}}

                                <div class="card-header d-flex align-items-baseline">
                                    <h5 class="card-title mr-auto">
                                        <a href="{{ route("inventory.viewStock",$stock->id) }}">{{ $stock->name }}</a>
                                    </h5>
                                    <a class="btn btn-secondary" href="{{ route("inventory.viewStock",$stock->id) }}">Modify</a>
                                </div>

                                <img src="{{ $stock->getIcon() }}" class="" alt="{{ $stock->name }} as image">

                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item @if($location->id==$stock->location_id) list-group-item-success @endif">
                                        Location
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
        const location_filter = $("#locationFilter")

        location_filter.select2({
            placeholder: "All locations",
            ajax: {
                url: "{{ route("inventory.mainFilterLocationSuggestions") }}"
            }
        })

        location_filter.on('select2:select', function (e) {
            $("#filterForm").submit()
        });


        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
@endpush