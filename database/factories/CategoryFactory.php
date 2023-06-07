<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        Storage::fake('categories');
        $imageName = date('o-m-d') . '-category-' . Str::slug(fake()->unique()->name(), '-') . '.jpg';
        $image = UploadedFile::fake()->image($imageName, 100, 100)->size(2048);

        return [
            'image' => $image,
            'name' => fake()->unique()->name(),
            'description' => fake()->text(),
        ];
    }
}
