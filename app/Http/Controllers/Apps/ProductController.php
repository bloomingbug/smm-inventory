<?php

namespace App\Http\Controllers\Apps;

use Inertia\Inertia;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware([
            "permission:products.index|products.create|products.edit|products.delete"
        ]);
    }

    public function index()
    {
        $products = Product::when(request()->q, function ($products) {
            $products = $products->where("title", "like", "%" . request()->q . "%");
        })->latest()->paginate(5);

        return Inertia::render("Apps/Products/Index", [
            "products" => $products
        ]);
    }

    public function create()
    {
        $categories = Category::all();

        return Inertia::render("Apps/Products/Create", [
            "categories" => $categories
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            "image" => ["required", "file", "image", "mimes:png,jpg,jpeg", "max:2048"],
            "barcode" => ["required", "unique:products,barcode"],
            "title" => ["required", "string", "max:255"],
            "description" => ["required", "string"],
            "category_id" => ["required", "numeric", "exists:categories,id"],
            'buy_price' => ["required", "numeric"],
            'sell_price' => ["required", "numeric"],
            'stock' => ["required", "numeric"],
        ]);

        $image = $request->file("image");
        $imageName = date("o-m-d") . "-product-" . Str::slug($request->title) . "." . $image->getClientOriginalExtension();

        $image->storeAs("public/products", $imageName);

        Product::create([
            'image'         => $imageName,
            'barcode'       => $request->barcode,
            'title'         => $request->title,
            'description'   => $request->description,
            'category_id'   => $request->category_id,
            'buy_price'     => $request->buy_price,
            'sell_price'    => $request->sell_price,
            'stock'         => $request->stock,
        ]);

        return redirect()->route("apps.products.index");
    }

    public function edit(Product $product)
    {
        $categories = Category::all();

        return Inertia::render("Apps/Products/Edit", ["product" => $product, "categories" => $categories]);
    }

    public function update(Product $product, Request $request)
    {
        $this->validate($request, [
            "image" => ["nullable", "file", "image", "mimes:png,jpg,jpeg", "max:2048"],
            "barcode" => ["required", "unique:products,barcode," . $product->id],
            "title" => ["required", "string", "max:255"],
            "description" => ["required", "string"],
            "category_id" => ["required", "numeric", "exists:categories,id"],
            'buy_price' => ["required", "numeric"],
            'sell_price' => ["required", "numeric"],
            'stock' => ["required", "numeric"],
        ]);

        if ($request->file("image")) {
            Storage::disk("local")->delete("public/products/" . basename($product->image));

            $image = $request->file("image");
            $imageName = date("o-m-d") . "-product-" . Str::slug($request->title) . "." . $image->getClientOriginalExtension();

            $image->storeAs("public/products", $imageName);

            $product->update([
                'image'         => $imageName,
                'barcode'       => $request->barcode,
                'title'         => $request->title,
                'description'   => $request->description,
                'category_id'   => $request->category_id,
                'buy_price'     => $request->buy_price,
                'sell_price'    => $request->sell_price,
                'stock'         => $request->stock,
            ]);
        } else {
            $product->update([
                'barcode'       => $request->barcode,
                'title'         => $request->title,
                'description'   => $request->description,
                'category_id'   => $request->category_id,
                'buy_price'     => $request->buy_price,
                'sell_price'    => $request->sell_price,
                'stock'         => $request->stock,
            ]);
        }

        return redirect()->route("apps.products.index");
    }

    public function destroy(Product $product)
    {
        Storage::disk("local")->delete("public/products/" . basename($product->image));

        $product->delete();

        return redirect()->route("apps.products.index");
    }
}
