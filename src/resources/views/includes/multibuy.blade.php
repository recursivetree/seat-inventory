@php
    $id = rand()
@endphp

<div class="modal fade text-muted" id="multibuyModal{{$id}}" tabindex="-1" style="font-size: 1rem;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Multibuy</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <textarea class="w-100" rows="15" id="multibuyTextArea{{$id}}" onclick="this.focus();this.select();document.execCommand('copy');" readonly="readonly">{{ $multibuy }}</textarea>
            </div>
            <div class="modal-footer">
                <button id="multibuyCopyButton{{$id}}" class="btn btn-primary">Copy</button>
                <button class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<button type="button" class="btn btn-secondary align-self-baseline" data-toggle="modal" data-target="#multibuyModal{{$id}}">
    @if(isset($title))
        {{ $title }}
    @else
        Multibuy
    @endif
</button>

@push('javascript')
    <script>
        $("#multibuyCopyButton{{$id}}").click(function (e) {
            e.stopPropagation();

            const textarea = $("#multibuyTextArea{{$id}}")
            textarea.focus();
            textarea.select();
            document.execCommand('copy');
        })
    </script>
@endpush