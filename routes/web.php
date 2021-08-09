<?php
use App\Http\LiveWire\HomeComponent;
use App\Http\LiveWire\DetailsComponent;
use App\Http\LiveWire\ShopComponent;
use App\Http\LiveWire\User\UserDashboardComponent;
use App\Http\LiveWire\Admin\AdminDashboardComponent;
use App\Http\LiveWire\Admin\AdminCategoryComponent;
use App\Http\LiveWire\Admin\AdminAddCategoryComponent;
use App\Http\LiveWire\Admin\AdminEditCategoryComponent;
use App\Http\LiveWire\Admin\AdminProductComponent;
use App\Http\LiveWire\Admin\AdminAddProductComponent;
use App\Http\LiveWire\Admin\AdminEditProductComponent;
use App\Http\LiveWire\Admin\AdminHomeSliderComponent;
use App\Http\LiveWire\Admin\AdminAddHomeSliderComponent;
use App\Http\LiveWire\Admin\AdminEditHomeSliderComponent;
use App\Http\LiveWire\Admin\AdminHomeCategoryComponent;
use App\Http\LiveWire\Admin\AdminSaleComponent;
use App\Http\LiveWire\Admin\AdminCouponsComponent;
use App\Http\LiveWire\Admin\AdminAddCouponComponent;
use App\Http\LiveWire\Admin\AdminEditCouponComponent;
use App\Http\LiveWire\Admin\AdminOrderComponent;
use App\Http\LiveWire\Admin\AdminOrderDetailsComponent;
use App\Http\LiveWire\CartComponent;
use App\Http\LiveWire\CategoryComponent;
use App\Http\LiveWire\SearchComponent;
use App\Http\LiveWire\WishlistComponent;
use App\Http\LiveWire\CheckoutComponent;
use App\Http\LiveWire\ThankyouComponent;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/',App\Http\Livewire\HomeComponent::class);
Route::get('/shop',App\Http\Livewire\ShopComponent::class);
Route::get('/cart',App\Http\Livewire\CartComponent::class)->name('product.cart');
Route::get('/checkout',App\Http\Livewire\CheckoutComponent::class)->name('checkout');
Route::get('/product/{slug}',App\Http\Livewire\DetailsComponent::class)->name('product.details');
Route::get('/product-category/{category_slug}',App\Http\Livewire\CategoryComponent::class)->name('product.category');
Route::get('/search',App\Http\Livewire\SearchComponent::Class)->name('product.search');
Route::get('/Wishlist',App\Http\Livewire\WishlistComponent::Class)->name('product.wishlist');
Route::get('/thank-you',App\Http\Livewire\ThankyouComponent::Class)->name('thankyou');



// Route::middleware(['auth:sanctum','verified'])->get('/dashboard',function (){
//     return view('dashboard');
// })->name('dashboard');

//for customer 
Route::middleware(['auth:sanctum','verified',])->group(function(){
Route::get('/user/dashboard',App\Http\Livewire\User\UserDashboardComponent::class)->name('user.dashboard');
});

//for admin
Route::middleware(['auth:sanctum','verified','authadmin'])->group(function(){
Route::get('/admin/dashboard',App\Http\Livewire\Admin\AdminDashboardComponent::class)->name('admin.dashboard');
Route::get('/admin/categories',App\Http\Livewire\Admin\AdminCategoryComponent::class)->name('admin.categories');
Route::get('/admin/category/add',App\Http\Livewire\Admin\AdminAddCategoryComponent::class)->name('admin.addcategory');
Route::get('/admin/category/edit/{category_slug}',App\Http\Livewire\Admin\AdminEditCategoryComponent::class)->name('admin.Editcategory');
Route::get('/admin/products',App\Http\Livewire\Admin\AdminProductComponent::class)->name('admin.Products');
Route::get('/admin/product/add',App\Http\Livewire\Admin\AdminAddProductComponent::class)->name('admin.addproduct');
Route::get('/admin/product/edit/{product_slug}',App\Http\Livewire\Admin\AdminEditProductComponent::class)->name('admin.Editproduct');
Route::get('/admin/sale',App\Http\Livewire\Admin\AdminSaleComponent::class)->name('admin.sale');

Route::get('/admin/coupons',App\Http\Livewire\Admin\AdminCouponsComponent::class)->name('admin.coupons');
Route::get('/admin/coupon/edit/{coupon_id}',App\Http\Livewire\Admin\AdminEditCouponComponent::class)->name('admin.editcoupon');
Route::get('/admin/coupon/add',App\Http\Livewire\Admin\AdminAddCouponComponent::class)->name('admin.addcoupon');

Route::get('/admin/orders',App\Http\Livewire\Admin\AdminOrderComponent::class)->name('admin.orders');
Route::get('/admin/orders/{order_id}',App\Http\Livewire\Admin\AdminOrderDetailsComponent::class)->name('admin.orderdetails');


//homeslider 
Route::get('/admin/slider',App\Http\Livewire\Admin\AdminHomeSliderComponent::class)->name('admin.homeslider');
Route::get('/admin/slider/add',App\Http\Livewire\Admin\AdminAddHomeSliderComponent::class)->name('admin.addhomeslider');
Route::get('/admin/slider/edit/{slide_id}',App\Http\Livewire\Admin\AdminEditHomeSliderComponent::class)->name('admin.edithomeslider');
Route::get('/admin/home-categories',App\Http\Livewire\Admin\AdminHomeCategoryComponent::class)->name('admin.homecategories');


});
