<?php

namespace App\Http\Controllers\Apps;

use App\Models\Cart;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware(["permission:transactions.index"]);
    }

    public function index()
    {
        $carts = Cart::with("product")->where("cashier_id", auth()->user()->id)->latest()->get();

        $customers = Customer::latest()->get();

        return Inertia::render("Apps/Transactions/Index", [
            "carts" => $carts,
            "carts_total" => $carts->sum("price"),
            "customers" => $customers
        ]);
    }

    public function searchProduct(Request $request)
    {
        // find product by Barcode
        $product = Product::where("barcode", $request->barcode)->first();

        if ($product) {
            return response()->json([
                "success" => true,
                "data" => $product
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "data" => null
            ], 404);
        }
    }

    public function addToCart(Request $request)
    {
        // Check cart
        $cart = Cart::with("product")
            ->where("product_id", $request->product_id)
            ->where("cashier_id", auth()->user()->id)
            ->first();

        if ($cart) {
            // Check stock product
            if (Product::whereId($request->product_id)->first()->stock < ($request->qty + $cart->qty)) {
                return redirect()->back()->with("error", "Out of Stock Product!.");
            }

            // Increment qty
            $cart->increment("qty", $request->qty);

            // sum price * quantity
            $cart->price = $cart->product->sell_price * $cart->qty;

            $cart->save();
        } else {
            // Check stock product
            if (Product::whereId($request->product_id)->first()->stock < $request->qty) {
                return redirect()->back()->with("error", "Out of Stock Product!.");
            }

            Cart::create([
                "cashier_id" => auth()->user()->id,
                "product_id" => $request->product_id,
                "qty" => $request->qty,
                "price" => $request->sell_price * $request->qty
            ]);
        }

        return redirect()->route("apps.transactions.index")->with("success", "Product Added Successfully!.");
    }

    public function destroyCart(Request $request)
    {
        $cart = Cart::with("product")->whereId($request->id)->first();

        $cart->delete();

        return redirect()->back()->with('success', 'Product Removed Successfully!.');
    }

    public function store(Request $request)
    {
        /**
         * alghoritm to generate invoice
         */
        $length = 10;
        $random = "";
        for ($i = 0; $i < $length; $i++) {
            $random .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('a'), ord('z')));
        }

        $invoice = "TRX-" . Str::upper($random);

        // Check if invoice already exists
        if (Transaction::where("invoice", $invoice)->first()) {
            // return redirect()->back()->with("error", "Invoice Already Exists. Please Try Again!");
            return response()->json([
                "success" => false,
                "message" => "Invoice Already Exists. Please Try Again!"
            ], 500);
        }

        // Membuat data grand_total dari database, tidak dari frontend
        $grand_total = Cart::with("product")->where("cashier_id", auth()->user()->id)->latest()->sum("price");

        if ($request->cash + $request->discount < $grand_total) {
            // return redirect()->back()->with("error", "Underpayment: -Rp" . formatPrice(($grand_total - ($request->cash + $request->discount))));
            return response()->json([
                "success" => false,
                "message" => "Underpayment: -Rp" . formatPrice(($grand_total - ($request->cash + $request->discount)))
            ], 422);
        }

        $transaction = Transaction::create([
            "cashier_id" => auth()->user()->id,
            "customer_id" => $request->customer_id,
            "invoice" => $invoice,
            "cash" => $request->cash,
            "change" => $request->cash + $request->discount - $grand_total,
            "discount" => $request->discount,
            "grand_total" => $grand_total
        ]);

        $carts = Cart::where('cashier_id', auth()->user()->id)->get();

        //insert transaction detail
        foreach ($carts as $cart) {

            //insert transaction detail
            $transaction->details()->create([
                'transaction_id'    => $transaction->id,
                'product_id'        => $cart->product_id,
                'qty'               => $cart->qty,
                'price'             => $cart->price,
            ]);

            //get price
            $total_buy_price  = $cart->product->buy_price * $cart->qty;
            $total_sell_price = $cart->product->sell_price * $cart->qty;

            //get profits
            $profits = $total_sell_price - $total_buy_price;

            //insert provits
            $transaction->profits()->create([
                'transaction_id'    => $transaction->id,
                'total'             => $profits,
            ]);

            //update stock product
            $product = Product::find($cart->product_id);
            $product->stock = $product->stock - $cart->qty;
            $product->save();
        }

        //delete carts
        Cart::where('cashier_id', auth()->user()->id)->delete();

        return response()->json([
            'success' => true,
            'data'    => $transaction
        ]);
    }

    public function print(Request $request)
    {
        //get transaction
        $transaction = Transaction::with('details.product', 'cashier', 'customer')->where('invoice', $request->invoice)->firstOrFail();

        //return view
        return view('print.nota', compact('transaction'));
    }
}
