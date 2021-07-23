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

Route::get('/',HomeComponent::class);
Route::get('/shop',ShopComponent::class);
Route::get('/cart',CartComponent::class)->name('product.cart');
Route::get('/checkout',CheckoutComponent::class)->name('checkout');
Route::get('/product/{slug}',DetailsComponent::class)->name('product.details');
Route::get('/product-category/{category_slug}',CategoryComponent::class)->name('product.category');
Route::get('/search',SearchComponent::Class)->name('product.search');
Route::get('/Wishlist',WishlistComponent::Class)->name('product.wishlist');
Route::get('/thank-you',ThankyouComponent::Class)->name('thankyou');



// Route::middleware(['auth:sanctum','verified'])->get('/dashboard',function (){
//     return view('dashboard');
// })->name('dashboard');

//for customer 
Route::middleware(['auth:sanctum','verified',])->group(function(){
Route::get('/user/dashboard',UserDashboardComponent::class)->name('user.dashboard');
});

//for admin
Route::middleware(['auth:sanctum','verified','authadmin'])->group(function(){
Route::get('/admin/dashboard',AdminDashboardComponent::class)->name('admin.dashboard');
Route::get('/admin/categories',AdminCategoryComponent::class)->name('admin.categories');
Route::get('/admin/category/add',AdminAddCategoryComponent::class)->name('admin.addcategory');
Route::get('/admin/category/edit/{category_slug}',AdminEditCategoryComponent::class)->name('admin.Editcategory');
Route::get('/admin/products',AdminProductComponent::class)->name('admin.Products');
Route::get('/admin/product/add',AdminAddProductComponent::class)->name('admin.addproduct');
Route::get('/admin/product/edit/{product_slug}',AdminEditProductComponent::class)->name('admin.Editproduct');
Route::get('/admin/sale',AdminSaleComponent::class)->name('admin.sale');

Route::get('/admin/coupons',AdminCouponsComponent::class)->name('admin.coupons');
Route::get('/admin/coupon/edit/{coupon_id}',AdminEditCouponComponent::class)->name('admin.editcoupon');
Route::get('/admin/coupon/add',AdminAddCouponComponent::class)->name('admin.addcoupon');

Route::get('/admin/orders',AdminOrderComponent::class)->name('admin.orders');
Route::get('/admin/orders/{order_id}',AdminOrderDetailsComponent::class)->name('admin.orderdetails');


//homeslider 
Route::get('/admin/slider',AdminHomeSliderComponent::class)->name('admin.homeslider');
Route::get('/admin/slider/add',AdminAddHomeSliderComponent::class)->name('admin.addhomeslider');
Route::get('/admin/slider/edit/{slide_id}',AdminEditHomeSliderComponent::class)->name('admin.edithomeslider');
Route::get('/admin/home-categories',AdminHomeCategoryComponent::class)->name('admin.homecategories');


});
