@extends('web::layouts.grids.12')

@section('title', "Title")
@section('page_header', "Title")


@section('full')
    @include("terminusinv::includes.status")

    <div class="card">
        <div class="card-body">
            <h5>
                {{ $stock->name }}
                @if($stock->fitting_plugin_fitting_id != null)
                    <span class="badge badge-primary">Fitting Plugin</span>
                @endif
            </h5>

            <p>{{ $stock->location->name }}</p>

            <p> You specified a minimal stock level of {{ $stock->amount }} "{{ $stock->name }}"s in  {{ $stock->location->name }}</p>

            <h6>Items</h6>
            @if($stock->items->isEmpty())
                <div class="alert alert-warning">
                    There are no items in this fit
                </div>
            @else
                <ul class="list-group mb-4">
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

            <div class="d-flex">
                <a href="{{ route("terminusinv.stocks") }}" class="btn btn-primary">Back</a>

                <form action="{{ route("terminusinv.deleteStock", $stock->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>

                @include("terminusinv::includes.multibuy",["multibuy" => $multibuy])
            </div>
        </div>
    </div>
@stop

@push('javascript')
    <script src="@terminusinvVersionedAsset('terminusinventory/js/bootstrap-autocomplete.js')"></script>

    <script>
        $('.basicAutoComplete').autoComplete({
            resolverSettings: {
                requestThrottling: 250
            },
            minLength: 0,
        });

        $("#multiBuyModalCopyButton").click(function () {
            const textarea = $("#multibuyTextArea")
            textarea.focus();
            textarea.select();
            document.execCommand('copy');
        })
    </script>
@endpush