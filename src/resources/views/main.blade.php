@extends('web::layouts.app')

@section('title', "Inventory Dashboard")
@section('page_header', "Inventory Dashboard")


@section('content')
    <div class="modal" id="editCategoryModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalTitle">Edit Category</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form action="{{ route("inventory.saveCategory") }}" method="POST" id="editCategoryModalSaveForm">
                        @csrf

                        <input type="hidden" name="id" value="" id="editCategoryModalCategoryId">

                        <div class="form-group">
                            <label for="editCategoryModalCategoryName">Category Name</label>

                            <div data-toggle="tooltip"
                                 data-placement="top"
                                 title=""
                                 id="editCategoryModalTooltip">

                                <input
                                        type="text"
                                        class="form-control"
                                        id="editCategoryModalCategoryName"
                                        placeholder="Enter category name..."
                                        name="name">
                            </div>
                        </div>
                    </form>

                    <form id="editCategoryModalDeleteForm" action="{{ route("inventory.deleteCategory") }}" method="POST">
                        @csrf
                        <input type="hidden" value="" name="id" id="editCategoryModalDeleteCategoryId">
                    </form>

                    <div class="d-flex">
                        <button type="submit" class="btn btn-danger" id="editCategoryModalDeleteButton" form="editCategoryModalDeleteForm">Delete Category</button>
                        <button type="button" class="btn btn-secondary ml-auto mr-1" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="editCategoryModalSubmitButton" form="editCategoryModalSaveForm">Create</button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="addStockModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Stock</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form action="{{ route("inventory.addStockToCategory") }}" method="POST">
                        @csrf

                        <input type="hidden" name="category" value="" id="addStockModalCategoryId">

                        <div class="form-group">
                            <label for="addStockModalStockSelect">Select Stock</label>
                            <select id="addStockModalStockSelect" name="stock"></select>
                        </div>

                        <div class="d-flex">
                            <button type="button" class="btn btn-secondary ml-auto mr-1" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="removeStockModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form action="{{ route("inventory.removeStockFromCategory") }}" method="POST">
                        @csrf

                        <input type="hidden" name="stock" value="" id="removeStockModalStockId">
                        <input type="hidden" name="category" value="" id="removeStockModalCategoryId">

                        <p>Do you really want to remove the stock <i id="removeStockModalStockName"></i>?</p>

                        <div class="d-flex">
                            <button type="button" class="btn btn-primary ml-auto mr-1" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">Remove</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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

    <div class="d-flex flex-row align-items-center mb-3">
        <button class="btn btn-primary ml-auto" id="createCategoryButton">
            <i class="fas fa-plus"></i> Create Category
        </button>
    </div>

    @foreach($categories as $category)
        <div class="card">
            <div class="card-body">

                <div class="d-flex align-items-baseline">
                    <h5 class="card-title flex-grow-1" data-toggle="collapse" data-target="#categoryContent{{ $category->id }}">
                        {{ $category->name }}
                    </h5>

                    @if($category->fitting_plugin_doctrine_id == null)
                        <button class="btn btn-success mr-1 addStockModalButton"
                                data-category-id="{{ $category->id }}">
                            <i class="fas fa-plus"></i>
                        </button>
                    @endif

                    <button class="btn btn-secondary mr-1 editCategoryButton"
                            data-category-id="{{ $category->id }}"
                            data-category-name="{{ $category->name }}"
                            data-category-has-doctrine="{{$category->fitting_plugin_doctrine_id != null}}">
                        <i class="fas fa-pen"></i>
                    </button>

                    <button class="btn btn-primary" data-toggle="collapse"
                            data-target="#categoryContent{{ $category->id }}">
                        Expand
                    </button>
                </div>

                <div class="collapse" id="categoryContent{{ $category->id }}">
                    <hr>
                    <div class="d-flex flex-wrap">

                        @if($category->stocks->isEmpty())
                            <p>There are no stocks in this category.</p>
                        @endif

                        @foreach($category->stocks as $stock)
                            @php($available = $stock->available_on_contracts + $stock->available_in_hangars)

                            <div class="card m-1" style="width: 16rem;">
                                {{--                            @if($location->id==$stock->location_id) background-color:red; @endif--}}

                                <div class="card-header d-flex align-items-baseline" style="padding-right: 0.75rem;">
                                    <h5 class="card-title mr-auto">
                                        <a href="{{ route("inventory.viewStock",$stock->id) }}">{{ $stock->name }}</a>
                                    </h5>

                                    <a href="{{ route("inventory.editStock",$stock->id) }}" class="mr-2">
                                        <i class="fas fa-pen"></i>
                                    </a>

                                    <i class="fas fa-unlink text-danger unlinkStockFromCategory"
                                            style="cursor: pointer;"
                                            data-stock-id="{{ $stock->id }}"
                                            data-category-id="{{ $category->id }}"
                                            data-stock-name="{{ $stock->name }}">
                                    </i>
                                </div>

                                <img src="{{ route("inventory.stockIcon",$stock->id) }}" alt="{{ $stock->name }} as image" loading="lazy">

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

                                    <li class="list-group-item">Priority <b
                                                class="float-right">@include("inventory::includes.priority",["priority"=>$stock->priority])</b>
                                    </li>

                                    <li class="list-group-item">
                                        Planned <b class="float-right">{{ $stock->amount }}</b>
                                    </li>

                                    <li class="list-group-item">
                                        Warning Threshold <b class="float-right">{{ $stock->warning_threshold }}</b>
                                    </li>

                                    @if($available === 0)
                                        <li class="list-group-item list-group-item-danger">
                                            Available <b class="float-right">{{ $available }}</b>
                                        </li>
                                    @elseif($available <= $stock->warning_threshold && $stock->warning_threshold !== $stock->amount)
                                        <li class="list-group-item list-group-item-warning">
                                            Available <b class="float-right">{{ $available }}</b>
                                        </li>
                                    @else
                                        <li class="list-group-item">
                                            Available <b class="float-right">{{ $available }}</b>
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

        $("#addStockModalStockSelect").select2({
            placeholder: "Select a stock..",
            ajax: {
                url: "{{ route("inventory.mainEditCategoryAddStockSuggestion") }}",
                data: function (params) {
                    return {
                        term: params.term,
                        category: $("#addStockModalCategoryId").val()
                    }
                }
            },
            width: '100%'
        })

        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })

        $('body').on('hidden.bs.modal', '.modal', function () {
            $('.btn').blur();
        });

        function editCategory(name, id, hasDoctrine, allowDelete) {

            $("#editCategoryModalTitle").text(name ? "Edit Category" : "Create Category")
            $("#editCategoryModalSubmitButton").text(name ? "Save" : "Create")
            $("#editCategoryModalCategoryId").val(id ? id : "")
            $("#editCategoryModalDeleteCategoryId").val(id ? id : "")

            const delete_button = $("#editCategoryModalDeleteButton")
            if(allowDelete){
                delete_button.removeClass("invisible")
            } else {
                delete_button.addClass("invisible")
            }

            const name_field = $("#editCategoryModalCategoryName")
            name_field.val(name ? name : "")
            name_field.attr("readonly", hasDoctrine)

            $("#editCategoryModalTooltip")
                .tooltip('hide')
                .attr('data-original-title', hasDoctrine ? "Can not change category name, it is imported from a doctrine." : "")

            $('#editCategoryModal').modal()
        }

        $("#createCategoryButton").click(function () {
            editCategory(null, null, false, false)
        })

        $(".editCategoryButton").click(function () {
            const btn = $(this)

            const hasDoctrine = btn.data("category-has-doctrine") !== ""

            editCategory(
                btn.data("category-name"),
                btn.data("category-id"),
                hasDoctrine,
                !hasDoctrine // only allow delete when not linked to a doctrine
            )
        })

        $(".addStockModalButton").click(function (){
            const btn = $(this)

            $("#addStockModalCategoryId").val(btn.data("category-id"))
            $('#addStockModal').modal()
        })

        $(".unlinkStockFromCategory").click(function () {
            const btn = $(this)

            $("#removeStockModalStockId").val(btn.data("stock-id"))
            $("#removeStockModalCategoryId").val(btn.data("category-id"))
            $("#removeStockModalStockName").text(btn.data("stock-name"))

            $('#removeStockModal').modal()
        })
    </script>
@endpush

@push("head")
    <style>
        .stock-list-entry:hover{
            background-color: #eee;
        }
    </style>
@endpush