@extends('web::layouts.grids.12')

@section('title', "Title")
@section('page_header', "Title")


@section('full')
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