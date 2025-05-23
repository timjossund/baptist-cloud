<div x-data="{
    following: {{ $user->isFollowedBy(auth()->user()) ? 'true' : 'false' }},
    followersCount: {{ $user->followers()->count() }},
    follow() {
        this.following = !this.following;
        axios.post('/follow/{{ $user->id }}')
            .then(res => {
                console.log(res);
                this.followersCount = res.data.followersCount;
            }).catch(err => {
                console.log(err);
            });

    }
}" class="flex flex-col gap-2 mt-6">
    {{ $slot }}
</div>
