<?php

use App\Http\Controllers\Account\OrderController as AccountOrderController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\WebsiteSettingController as AdminWebsiteSettingController;
use App\Http\Controllers\Auth\OtpLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\ProductDetailController;
use App\Http\Controllers\Webhooks\DelhiveryWebhookController;
use App\Http\Controllers\Webhooks\RazorpayWebhookController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/shop');

Route::get('/shop', HomeController::class)->name('shop.home');
Route::get('/shop/catalog', [CatalogController::class, 'index'])->name('shop.catalog');
Route::get('/shop/products/{slug}', ProductDetailController::class)->name('shop.product');

Route::get('/shop/cart', [CartController::class, 'index'])->name('shop.cart');
Route::post('/shop/cart', [CartController::class, 'add'])->name('shop.cart.add');
Route::patch('/shop/cart/{cartItem}', [CartController::class, 'update'])->name('shop.cart.update');
Route::delete('/shop/cart/{cartItem}', [CartController::class, 'remove'])->name('shop.cart.remove');

Route::middleware('auth')->group(function () {
    Route::get('/shop/checkout', [CheckoutController::class, 'create'])->name('shop.checkout');
    Route::post('/shop/checkout', [CheckoutController::class, 'store'])->name('shop.checkout.store');

    Route::get('/account/orders', [AccountOrderController::class, 'index'])->name('account.orders.index');
    Route::get('/account/orders/{order}', [AccountOrderController::class, 'show'])->name('account.orders.show');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('orders/{order}/delay', [AdminOrderController::class, 'delay'])->name('orders.delay');
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/export', [AdminUserController::class, 'export'])->name('users.export');
    Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('settings', [AdminWebsiteSettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [AdminWebsiteSettingController::class, 'update'])->name('settings.update');
    Route::post('settings/reset', [AdminWebsiteSettingController::class, 'reset'])->name('settings.reset');
});

Route::post('/webhooks/razorpay', RazorpayWebhookController::class)->name('webhooks.razorpay');
Route::post('/webhooks/delhivery', DelhiveryWebhookController::class)->name('webhooks.delhivery');

Route::get('/dashboard', function () {
    if (auth()->user()?->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
