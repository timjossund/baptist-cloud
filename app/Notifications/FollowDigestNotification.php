<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\Sermon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

class FollowDigestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  EloquentCollection<int, Post>  $posts
     * @param  EloquentCollection<int, Sermon>  $sermons
     */
    public function __construct(
        public EloquentCollection $posts,
        public EloquentCollection $sermons,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return $notifiable->fresh()?->notify_follows === true;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->posts->loadMissing('user');
        $this->sermons->loadMissing('user');

        $count = $this->posts->count() + $this->sermons->count();
        $subject = $count === 1
            ? 'New from someone you follow'
            : $count.' new pieces from people you follow';

        return (new MailMessage)
            ->subject($subject)
            ->markdown('mail.follow-digest', [
                'groups' => $this->groups(),
                'unsubscribeUrl' => URL::signedRoute('follow-digest.unsubscribe', $notifiable),
            ]);
    }

    /**
     * @return Collection<int, array{author: string, items: Collection<int, array{title: string, url: string, kind: string}>}>
     */
    public function groups(): Collection
    {
        $articles = $this->posts->map(fn (Post $post) => [
            'author_id' => $post->user_id,
            'author' => $post->user->name,
            'title' => $post->title,
            'url' => route('single-post', ['username' => $post->user->username, 'post' => $post->slug]),
            'kind' => 'Article',
        ]);

        $sermons = $this->sermons->map(fn (Sermon $sermon) => [
            'author_id' => $sermon->user_id,
            'author' => $sermon->user->name,
            'title' => $sermon->title,
            'url' => route('sermons.show', $sermon),
            'kind' => 'Sermon',
        ]);

        return $articles->concat($sermons)
            ->groupBy('author_id')
            ->map(fn (Collection $items) => [
                'author' => $items->first()['author'],
                'items' => $items->map(fn (array $item) => [
                    'title' => $item['title'],
                    'url' => $item['url'],
                    'kind' => $item['kind'],
                ])->values(),
            ])
            ->values();
    }
}
