<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasApiTokens, HasFactory, Notifiable;

    /**
     * An API token unused for longer than this stops working and is pruned (spec 0002, AC-8).
     */
    public const API_TOKEN_IDLE_DAYS = 90;

    protected static function booted(): void
    {
        // Tokens are a morph with no foreign key, so remove them in code (spec 0002, AC-9).
        static::deleting(function (User $user) {
            $user->tokens()->delete();
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'avatar',
        'email',
        'password',
        'bio',
        'username',
        'notify_follows',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notify_follows' => 'boolean',
        ];
    }

    public function avatar(): Attribute
    {
        return Attribute::make(get: function ($value) {
            return $value ? '/storage/avatars/'.$value : '/default-avatar.png';
        });
    }

    private ?string $planTier = null;

    /**
     * The plan tier, worked out once per request: admin, author, member, or free.
     */
    public function planTier(): string
    {
        return $this->planTier ??= $this->resolvePlanTier();
    }

    private function resolvePlanTier(): string
    {
        if ($this->is_admin) {
            return 'admin';
        }

        if ($this->is_author) {
            return 'author';
        }

        $subscription = $this->subscription('default');

        if (! $subscription || ! $subscription->valid()) {
            return 'free';
        }

        $price = $subscription->stripe_price;

        if (in_array($price, self::configuredPrices('member_price'), true)) {
            return 'member';
        }

        if (! in_array($price, [...self::configuredPrices('author_price'), ...self::configuredPrices('author_legacy_prices')], true)) {
            Log::warning('Subscription on an unknown Stripe price counted as Author.', ['user_id' => $this->id, 'price' => $price]);
        }

        return 'author';
    }

    /**
     * Price IDs from a comma separated config value, with blanks dropped so an unset env never matches.
     *
     * @return list<string>
     */
    private static function configuredPrices(string $key): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) config('services.stripe.'.$key)))));
    }

    /**
     * Whether this user can read and listen without the monthly free limit.
     */
    public function hasFullAccess(): bool
    {
        return $this->planTier() !== 'free';
    }

    /**
     * Whether this user's own ads may be shown instead of the platform's.
     */
    public function canRunAds(): bool
    {
        return in_array($this->planTier(), ['admin', 'author'], true);
    }

    public function planLabel(): string
    {
        return ucfirst($this->planTier());
    }

    public function contentViews(): HasMany
    {
        return $this->hasMany(ContentView::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }

    public function isFollowedBy(User $user)
    {
        return $this->followers()->where('follower_id', $user->id)->exists();
    }

    public function reports()
    {
        return $this->hasMany(Reporting::class);
    }

    public function hasLiked(Post $post)
    {
        return $post->likes()->where('user_id', $this->id)->exists();
    }

    public function sermons()
    {
        return $this->hasMany(Sermon::class);
    }
}
