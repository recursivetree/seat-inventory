<a href="{{ route("inventory.viewStock",$stock->id) }}"
   class="list-group-item list-group-item-action">
    <b>{{ $stock->name }}</b>
    {{ $stock->location->name }}

    @if($stock->available_on_contracts + $stock->available_in_hangars >= $stock->amount)
        <span class="badge badge-success">
            Availability: {{ $stock->available_on_contracts + $stock->available_in_hangars }}/{{ $stock->amount }}
        </span>
    @else
        <span class="badge badge-danger">
            Availability: {{ $stock->available_on_contracts + $stock->available_in_hangars }}/{{ $stock->amount }}
        </span>
    @endif

    @if($stock->fitting_plugin_fitting_id != null)
        <span class="badge badge-primary">Fitting Plugin</span>
    @endif
    @include("inventory::includes.priority",["priority"=>$stock->priority])

   @foreach($stock->categories as $category)
        <span class="badge badge-secondary">{{ $category->name }}</span>
    @endforeach
</a>