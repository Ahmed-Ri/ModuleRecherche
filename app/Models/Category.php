<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'image', 'parent_id'];

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function magasins()
    {
        return $this->belongsToMany(Magasin::class, 'magasin_category')
                    ->withPivot('type', 'subcategory_id', 'main_category_id')
                    ->withTimestamps();
    }
}
