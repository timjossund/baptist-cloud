<x-app-layout>
    <h1>Single Post</h1>
    <p>{{ $post->title }}</p>
    <p>{{ $post->content }}</p>
    <p>Posted by: {{ $post->user->name }}</p>
    <p>Posted at: {{ $post->created_at->format('d M Y') }}</p>
</x-app-layout>
