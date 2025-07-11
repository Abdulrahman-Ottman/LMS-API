<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Student extends Model
{

    protected $fillable =[
      'user_id',
      'full_name'
    ];
    public function categories() : BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

}
