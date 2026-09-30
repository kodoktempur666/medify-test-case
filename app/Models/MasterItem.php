<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_items', 'master_item_id', 'category_id')->withTimestamps();
    }
}
