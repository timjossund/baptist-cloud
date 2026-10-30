<x-mail::message>
# New from people you follow

@foreach ($groups as $group)
**{{ $group['author'] }}**

@foreach ($group['items'] as $item)
- [{{ $item['title'] }}]({{ $item['url'] }}) ({{ $item['kind'] }})
@endforeach

@endforeach

<x-mail::button :url="$unsubscribeUrl">
Stop these emails
</x-mail::button>

You can also turn this off from your profile.
</x-mail::message>
