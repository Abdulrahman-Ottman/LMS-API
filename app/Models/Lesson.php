<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable =['title','section_id','duration','script','video_url'];

    public  function section(): BelongsTo{
        return $this->belongsTo(Section::class);
    }
    public function students() : BelongsToMany
    {
        return $this->belongsToMany(Student::class);
    }
}
