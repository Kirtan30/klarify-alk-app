<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Osiset\ShopifyApp\Contracts\ShopModel as IShopModel;
use Osiset\ShopifyApp\Traits\ShopModel;
use App\Models\Language;

class User extends Authenticatable implements IShopModel
{
    use HasApiTokens, HasFactory, Notifiable;
    use ShopModel;

    const ALK_NL_STORE = 'allesoverallergie.myshopify.com';
    const ALK_NO_STORE = 'pollenkontroll.myshopify.com';
    const ALK_DE_STORE = 'allergiecheck.myshopify.com';
    const ALK_GRASTEK_STORE = 'grastek.myshopify.com';
    const ALK_RAGWITEK_STORE = 'ragwitek.myshopify.com';
    const KLARIFY_US_STORE = 'klarifymeus.myshopify.com';
    const KLARIFY_AT_STORE = 'at-klarify.myshopify.com';
    const KLARIFY_CZ_STORE = 'cz-klarify.myshopify.com';
    const KLARIFY_SK_STORE = 'klarifymesk.myshopify.com';
    const KLARIFY_CH_STORE = 'ch-klarify.myshopify.com';
    const KLARIFY_CA_STORE = 'ca-klarify.myshopify.com';
    const ALK_ODACTRA_STORE = 'odactra.myshopify.com';
    const ALK_DK_STORE = 'klarifymedk.myshopify.com';
    const DEMO_STORE = 'demo-kirtan-agrawal.myshopify.com';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'country',
        'public_domain',
        'shopify_api_key',
        'shopify_api_secret'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function languages()
    {
        return $this->belongsToMany(Language::class, 'user_language', 'user_id', 'language_id')
            ->withPivot([
                'id',
                'fad_page', 'fad_static_page', 'fad_region_page', 'fad_region_static_page',
                'pollen_page', 'pollen_static_page', 'pollen_region_page', 'pollen_region_static_page',
                'clinic_page', 'clinic_index_page',
                'lexicon_page', 'fad_iframe_page', 'default'
            ])
            ->withTimestamps();
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function country()
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }

    public function clinics()
    {
        return $this->hasMany(Clinic::class);
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function fadCities()
    {
        return $this->hasMany(FadCity::class);
    }

    public function fadRegions()
    {
        return $this->hasMany(FadRegion::class);
    }

    public function fadStates()
    {
        return $this->hasMany(FadState::class);
    }

    public function pollenCities()
    {
        return $this->hasMany(PollenCity::class);
    }

    public function pollenRegions()
    {
        return $this->hasMany(PollenRegion::class);
    }

    public function pollenStates()
    {
        return $this->hasMany(PollenState::class);
    }

    public function lexicons() {
        return $this->hasMany(Lexicon::class);
    }
}
