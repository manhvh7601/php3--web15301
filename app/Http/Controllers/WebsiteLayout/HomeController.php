<?php

namespace App\Http\Controllers\WebsiteLayout;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Product;
use Illuminate\Http\Request;

use function Ramsey\Uuid\v1;

class HomeController extends Controller
{
    public function index(){
        $hotProducts = Product::orderBy('id', 'desc')->take(8)->get();
        $hotProducts->load('category');
        return view('welcome', compact('hotProducts'));
    }
    public function shop(Request $request){
        $category = Category::all();
        $products = Product::latest()->paginate(9);

        $productQuery = Product::where('price','like',"%".$request->keyword."%");

        if($request->has('price') && $request->price>0){
            if($request->price == 1){
                $productQuery = $productQuery->orderBy('price');
            }else{
                $productQuery = $productQuery->orderByDesc('price');
            }
        }
        return view('shop', compact('products', 'category'));
    }
    public function detail(Request $request, $id){
        $hotProducts = Product::find($id);
        $hotProducts->load('category');

        $hotProducts = Product::where("id", "=", "$id")->get();
        $relatedProduct = Product::orderBy('id', 'desc')->take(4)->get();

        return view('detail', compact('hotProducts', 'relatedProduct'));
    }
    public function cart()
    {
        $cart = isset($_SESSION[CART]) == true ? $_SESSION[CART] : [];
        return view('cart', compact('cart'));
    }
    
    public function addCart($id){
        $cart = isset($_SESSION[CART]) == true ? $_SESSION[CART] : [];
        $product = Product::find($id);
        $product->load('category');
        $product = $product->toArray();
        
        $existedIndex = -1;
        for($i = 0; $i < count($cart); $i++){
            if($cart[$i]['id'] == $id){
                $existedIndex = $i;
                break;
            }
        }
        if($existedIndex == -1){
            $product['quantity'] = 1;
            array_push($cart, $product);
            return redirect()->back()->with('success', 'Bạn đã thêm giỏ hàng thành công!');
        }else{
            $cart[$existedIndex]['quantity'] += 1;

        }

        $_SESSION[CART] = $cart;
        // dd($cart);
        return view('cart');
        die;
    }
    public function clearCart(){
        if(isset($_SESSION[CART])){
            unset($_SESSION[CART]);
        }
        return view('cart');
        die;
    }

    public function checkout()
    {
        return view('checkout');
    }
    public function payment()
    {
        $model = $_POST;
        $invoice = new Invoice;
        $invoice->fill($model);

        if(isset($_SESSION[CART]) && $invoice->save()){
            $sessionCart = $_SESSION[CART];
            foreach($sessionCart as $item){
                $invoiceDetail = new InvoiceDetail();
                $invoiceDetail->fill([
                'product_id' => $item['id'], 
                'invoice_id' =>$invoice->id,
                'quantity'=>$item['quantity'], 
                'unit_price' => $item['price']]);
                $invoiceDetail->save();
                unset($_SESSION[CART]);
            }
            return view('checkout');
        }else {
            return view('cart');
        }
    }
    public function category($id){
        $category = Category::all();
        $products = Product::where('category_id', $id)->latest()->paginate(9);
        $category->load('products');
        return view('shop', compact('category', 'products'));
    }
}
