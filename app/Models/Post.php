<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Post extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title', 
        'content', 
        'user_id', 
        'slug', 
        'image', 
        'affiliate_link', 
        'category_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define la relación muchos a muchos con categorías.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_post');
    }

    /**
     * Define la relación muchos a muchos con etiquetas.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }

    /**
     * Configuración de colecciones de medios.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useFallbackUrl('/images/placeholder.jpg')
            ->useFallbackPath(public_path('/images/placeholder.jpg'));
    }

    /**
     * Configuración de conversiones de imágenes.
     */
    public function registerMediaConversions(Media $media = null): void
    {
        if (env('RESIZE_IMAGE') === true) {
            $this->addMediaConversion('resized-image')
                ->width(env('IMAGE_WIDTH', 300))
                ->height(env('IMAGE_HEIGHT', 300));
        }
    }

    /**
     * Relación con categorías (relación singular en caso de columna `category_id`).
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
