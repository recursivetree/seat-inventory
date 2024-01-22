@extends('web::layouts.grids.12')

@section('title', trans('inventory::common.about_title'))
@section('page_header', trans('inventory::common.about_title'))


@section('full')
    @include("treelib::giveaway")

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{trans('inventory::common.about_title')}}</h3>
        </div>
        <div class="card-body">
            <p>
               {!! trans('inventory::common.about_desc') !!}
            </p>
            <p>
                {{trans('inventory::common.about_third_party')}}:
                <ul>
                    <li>
                        <a href="https://fonts.google.com/specimen/Roboto#about">Roboto Font</a><a href="https://www.apache.org/licenses/LICENSE-2.0">Apache License, Version 2.0</a>
                    </li>
                </ul>
            </p>
        </div>
    </div>
@stop