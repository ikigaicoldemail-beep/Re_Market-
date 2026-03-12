<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{

 public function store(Request $request)
    {

    $product = Product::create([
        'title' => $request->title,
        'description' => $request->description,
        'price' => $request->price,
        'location' => $request->location,
        'user_id' => 1,   // ✅ get logged-in user
        'schedule_at' => $request->schedule_at,
        'auto_post' => $request->auto_post
    ]);

    $message = "Product created successfully";

    if($request->auto_post == "facebook"){
    $message = "Product posted and shared to Facebook (simulated)";
    }

    if($request->auto_post == "tiktok"){
    $message = "Product posted and shared to TikTok (simulated)";
    }

    return response()->json([
    "status"=>"success",
    "message"=>$message,
    "product"=>$product
    ]);

}
    public function index(Request $request)
    {
        $query = Product::query();

        // 🔎 Search by title
        if ($request->search) {
            $query->where('title', 'LIKE', '%' . $request->search . '%');
        }

        // 📍 Filter by location
        if ($request->location) {
            $query->where('location', $request->location);
        }

        // 💰 Min price
        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        // 💰 Max price
        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        // ⏳ Only show published products
        $query->where(function ($q) {
            $q->whereNull('schedule_at')
            ->orWhere('schedule_at', '<=', now());
        });

        $products = $query->get();

        return response()->json([
            "status" => "success",
            "products" => $products
        ]);
    }
    public function show($id)
    {

    $product = Product::find($id);

    if(!$product){
    return response()->json([
    "status"=>"error",
    "message"=>"Product not found"
    ]);
    }

    return response()->json([
    "status"=>"success",
    "product"=>$product
    ]);

    }
    public function update(Request $request, $id)
    {

    $product = Product::find($id);

    if(!$product){
    return response()->json([
    "status"=>"error",
    "message"=>"Product not found"
    ]);
    }

    $product->update([

    "title"=>$request->title,
    "description"=>$request->description,
    "price"=>$request->price,
    "location"=>$request->location

    ]);

    return response()->json([
    "status"=>"success",
    "product"=>$product
    ]);

    }
    public function destroy($id)
    {

    $product = Product::find($id);

    if(!$product){
    return response()->json([
    "status"=>"error",
    "message"=>"Product not found"
    ]);
    }

    $product->delete();

    return response()->json([
    "status"=>"success",
    "message"=>"Product deleted"
    ]);

    }
    public function seller($id)
    {
        $products = Product::where('user_id',$id)->get();

        return response()->json([
            "status"=>"success",
            "seller_id"=>$id,
            "products"=>$products
        ]);
    }
    public function share($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                "status" => "error",
                "message" => "Product not found"
            ]);
        }

        $shareLink = url('/product/'.$product->id);

        return response()->json([
            "status" => "success",
            "share_link" => $shareLink,
            "product" => $product
        ]);
    }
    public function dashboard($seller_id)
    {
        $total = Product::where('user_id', $seller_id)->count();

        $published = Product::where('user_id', $seller_id)
            ->where(function ($q) {
                $q->whereNull('schedule_at')
                ->orWhere('schedule_at', '<=', now());
            })
            ->count();

        $scheduled = Product::where('user_id', $seller_id)
            ->where('schedule_at', '>', now())
            ->count();

        $latest = Product::where('user_id', $seller_id)
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            "status" => "success",
            "seller_id" => $seller_id,
            "total_products" => $total,
            "published_products" => $published,
            "scheduled_products" => $scheduled,
            "latest_products" => $latest
        ]);
    }

}