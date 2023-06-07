<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        Storage::fake('products');
        $imageName = date('o-m-d') . '-category-' . Str::slug(fake()->unique()->name(), '-') . '.jpg';
        $image = UploadedFile::fake()->image($imageName, 100, 100)->size(2048);
        $category = Category::factory()->create();

        return [
            "category_id" => $category->id,
            "image" => $image,
            "barcode" => fake()->unique()->isbn13(),
            "title" => fake()->unique()->sentence(3),
            "description" => fake()->paragraph(3),
            "buy_price" => fake()->numberBetween(10000, 100000),
            "sell_price" => fake()->numberBetween(10000, 100000),
            "stock" => fake()->numberBetween(1, 100),
        ];
    }
}
