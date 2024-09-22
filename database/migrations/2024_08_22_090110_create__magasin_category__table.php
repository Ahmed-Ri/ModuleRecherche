<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMagasinCategoryTable extends Migration
{
    public function up()
    {
        Schema::create('magasin_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('magasin_id')->constrained('magasins')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('subcategory_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->foreignId('main_category_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->string('type')->default('category');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('magasin_category');
    }
}
