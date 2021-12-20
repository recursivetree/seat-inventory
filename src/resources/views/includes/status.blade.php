@if (session()->has('message'))
    @if(session()->get('message')["type"]=="success")
        <div class="alert alert-success">
            <p class="card-text">{{ session()->get('message')['message'] }}</p>
        </div>
    @elseif(session()->get('message')["type"]=="warning")
        <div class="alert alert-warning">
            <p class="card-text">{{ session()->get('message')['message'] }}</p>
        </div>
    @elseif(session()->get('message')["type"]=="error")
        <div class="alert alert-danger">
            <p class="card-text">{{ session()->get('message')['message'] }}</p>
        </div>
    @endif
@endif
