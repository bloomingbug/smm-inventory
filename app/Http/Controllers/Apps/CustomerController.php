<?php

namespace App\Http\Controllers\Apps;

use Inertia\Inertia;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware(["permission:customers.index|customers.create|customers.edit|customers.delete"]);
    }

    public function index(Request $request)
    {
        $customers = Customer::query()->when(request()->q, function ($customers) {
            $customers = $customers->where("name", "like", "%" . request()->q . "%");
        })->latest()->paginate(5);

        return Inertia::render("Apps/Customers/Index", [
            "customers" => $customers
        ]);
    }

    public function create()
    {
        return Inertia::render("Apps/Customers/Create");
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            "name" => ["required", "string", "max:255"],
            "no_telp" => ["required", "numeric", "digits_between:11,14", "starts_with:628", "unique:customers,no_telp"],
            "address" => ["required", "string"]
        ]);

        Customer::create([
            "name" => ucwords($request->name),
            "no_telp" => $request->no_telp,
            "address" => $request->address
        ]);

        return redirect()->route("apps.customers.index");
    }

    public function edit(Customer $customer)
    {
        return Inertia::render("Apps/Customers/Edit", [
            "customer" => $customer
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $this->validate($request, [
            "name" => ["required", "string", "max:255"],
            "no_telp" => ["required", "numeric", "digits_between:12,14", "starts_with:628", "unique:customers,no_telp," . $customer->id],
            "address" => ["required", "string"]
        ]);

        $customer->update([
            "name" => ucwords($request->name),
            "no_telp" => $request->no_telp,
            "address" => $request->address
        ]);

        return redirect()->route("apps.customers.index");
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route("apps.customers.index");
    }
}
