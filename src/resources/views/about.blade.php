@extends('web::layouts.grids.12')

@section('title', "About")
@section('page_header', "About")


@section('full')
    @include("treelib::giveaway")

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">About</h3>
        </div>
        <div class="card-body">
            <p>
                I hope you enjoy working with seat-inventory. To support the development, have you considered donating
                something? Donations are always welcome and motivate me to put more effort into this project, although
                they are by no means required. If you end up using this module a lot, I'd appreciate a donation.
                You can give ISK, PLEX or Ships to 'recursivetree'.
            </p>
            <p>
                This plugin uses the following third-party components:
                <ul>
                    <li>
                        <a href="https://fonts.google.com/specimen/Roboto#about">Roboto Font</a><a href="https://www.apache.org/licenses/LICENSE-2.0">Apache License, Version 2.0</a>
                    </li>
                </ul>
            </p>
        </div>
    </div>
@stop