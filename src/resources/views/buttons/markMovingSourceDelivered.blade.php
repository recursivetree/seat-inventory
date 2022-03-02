<form class="btn btn-secondary" method="POST" action="{{ route("inventory.removeMovingItems") }}" onclick="this.submit()">
    Mark Delivered

    @csrf
    <input type="hidden" name="source_id" value="{{ $source->id }}">
</form>