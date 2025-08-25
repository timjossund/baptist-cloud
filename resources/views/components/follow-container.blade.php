<div x-data="{
    following: {{ $user->isFollowedBy(auth()->user()) ? 'true' : 'false' }},
    follow() {
        this.following = !this.following;
        axios.post('/follow/{{ $user->id }}')
            .then(res => {
                console.log(res);
            }).catch(err => {
                console.log(err);
            });

    }
}" class="flex flex-col gap-2 mt-6">
    {{ $slot }}
</div>
