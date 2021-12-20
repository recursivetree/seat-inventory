@extends('web::layouts.grids.12')

@section('title', "Title")
@section('page_header', "Title")


@section('full')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">About</h5>
            <p class="card-text">
                This plugin uses the following libaries:
                <ul>
                    <li>
                        <a href="https://github.com/xcash/bootstrap-autocomplete">https://github.com/xcash/bootstrap-autocomplete (MIT License)</a>
                    </li>
                </ul>
            </p>
        </div>
    </div>
@stop