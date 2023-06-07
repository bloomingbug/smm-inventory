<?php

namespace App\Http\Controllers\Apps;

use Inertia\Inertia;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware([
            "permission:categories.index|categories.create|categories.edit|categories.delete"
        ]);
    }

    public function index()
    {
        $categories = Category::when(request()->q, function ($categories) {
            $categories = $categories->where("name", "like", "%" . request()->q . "%");
        })->latest()->paginate(10);

        return Inertia::render("Apps/Categories/Index", [
            "categories" => $categories,
        ]);
    }

    public function create()
    {
        return Inertia::render("Apps/Categories/Create");
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            "name" => ["required", "unique:categories,name", "max:255"],
            "description" => ["required", "string"],
            "image" => ["required", "file", "image", "mimes:png,jpg,jpeg", "max:2048"],
        ]);

        $image = $request->file("image");

        $imageName = date("o-m-d") . "-category-" . Str::slug($request->name) . "." . $image->getClientOriginalExtension();

        $image->storeAs("public/categories", $imageName);

        Category::create([
            "name" => $request->name,
            "description" => $request->description,
            "image" => $imageName,
        ]);

        return redirect()->route("apps.categories.index");
    }

    public function edit(Category $category)
    {
        return Inertia::render("Apps/Categories/Edit", [
            "category" => $category,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $this->validate($request, [
            "name" => ["required", "string", "max:255", "unique:categories,name," . $category->id],
            "description" => ["required", "string"],
            "image" => ["nullable", "file", "image", "mimes:png,jpg,jpeg", "max:2048"],
        ]);

        if ($request->file("image")) {
            Storage::disk("local")->delete("public/categories/" . basename($category->image));

            $image = $request->file("image");

            $imageName = date("o-m-d") . "-category-" . Str::slug($request->name) . "." . $image->getClientOriginalExtension();

            $image->storeAs("public/categories", $imageName);

            $category->update([
                "name" => $request->name,
                "description" => $request->description,
                "image" => $imageName,
            ]);
        } else {
            $category->update([
                "name" => $request->name,
                "description" => $request->description,
            ]);
        }


        return redirect()->route("apps.categories.index");
    }

    public function destroy(Category $category)
    {
        Storage::disk("local")->delete("public/categories/" . basename($category->image));

        $category->delete();

        return redirect()->route("apps.categories.index");
    }
}
