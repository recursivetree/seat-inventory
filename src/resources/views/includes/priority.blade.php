@if ($priority == 5)
    <span class="badge badge-danger">Critical</span>
@elseif ($priority == 4)
    <span class="badge badge-warning">Important</span>
@elseif ($priority == 3)
    <span class="badge badge-primary">Preferred</span>
@elseif ($priority == 2)
    <span class="badge badge-primary">Normal</span>
@elseif ($priority == 1)
    <span class="badge badge-primary">Low</span>
@else
    <span class="badge badge-primary">Very Low</span>
@endif

Critical
Important
Preferred
Normal
Low
Very Low