<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instructor extends Model
{
    protected $fillable = [
        'verified',
        'full_name',
        'views',
        'bio',
        'rating'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function categories():BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
    public function courses():HasMany
    {
        return $this->hasMany(Course::class);
    }
    public function ratings():HasMany
    {
      return $this->hasMany(InstructorRating::class);
    }
}
