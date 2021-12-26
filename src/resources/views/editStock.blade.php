@extends('web::layouts.grids.12')

@section('title', "Title")
@section('page_header', "Title")


@section('full')
    <div class="modal fade" id="multibuyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mulibuy</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <textarea class="w-100" rows="15" id="multibuyTextArea" onclick="this.focus();this.select();document.execCommand('copy');" readonly="readonly">{{ $multibuy }}</textarea>
                </div>
                <div class="modal-footer">
                    <button id="multiBuyModalCopyButton" class="btn btn-primary">Copy</button>
                    <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

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

                <button class="btn btn-secondary" data-toggle="modal" data-target="#multibuyModal">Multibuy</button>
            </div>
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