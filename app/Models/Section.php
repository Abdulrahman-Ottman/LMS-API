<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{

    protected $fillable =['title','course_id','total_duration'];
    public function lessons():HasMany
    {
        return $this->hasMany(Lesson::class);
    }
    public  function course(): BelongsTo{
        return $this->belongsTo(Course::class);
    }
}
