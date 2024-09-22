<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Magasin extends Model
{
    protected $fillable = ['nom', 'adresse', 'tel', 'secteur', 'image', 'tag'];

    // Relation avec les catégories via la table pivot
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'magasin_category')
                    ->withPivot('type', 'subcategory_id', 'main_category_id')
                    ->withTimestamps();
    }

    // Méthode pour attacher une catégorie à un magasin en tenant compte de la hiérarchie
    public function attachCategory($category)
    {
        $subcategoryId = $category->parent ? $category->parent->id : null;
        $mainCategoryId = $category->parent && $category->parent->parent ? $category->parent->parent->id : null;

        $this->categories()->attach($category->id, [
            'type' => $this->determineCategoryType($category),
            'subcategory_id' => $subcategoryId,
            'main_category_id' => $mainCategoryId,
        ]);
    }

    // Méthode pour déterminer le type de catégorie (catégorie principale, sous-catégorie, ou sous-sous-catégorie)
    private function determineCategoryType($category)
    {
        if ($category->parent_id === null) {
            return 'category';
        } elseif ($category->parent && $category->parent->parent_id === null) {
            return 'subcategory';
        } else {
            return 'subsubcategory';
        }
    }
}

