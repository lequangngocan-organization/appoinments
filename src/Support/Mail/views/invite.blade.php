<h2>{{ $a->title }}</h2>
@if ($a->description)
    <p>{{ $a->description }}</p>
@endif
@if ($openUrl)
    <p><a href="{{ $openUrl }}">Mở trên Google Calendar</a></p>
@elseif($addUrl)
    <p><a href="{{ $addUrl }}">Add to Google Calendar</a></p>
@endif
