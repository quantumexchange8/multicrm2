<?php

use App\Http\Controllers\AccountInfoController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\PaymentController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProfileController;

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

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('ompay/depositResult', [PaymentController::class, 'depositResult']);
Route::post('ompay/updateStatus', [PaymentController::class, 'updateResult']);
Route::middleware('auth')->group(function () {
    Route::get('/monthly-deposit', [GeneralController::class, 'monthly_deposit'])->name('monthly_deposit');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /**
     * ==============================
     *           Payments
     * ==============================
     */
    Route::get('/get_trading_account', [PaymentController::class, 'get_trading_account'])->name('get_trading_account');

    Route::post('/deposit', [PaymentController::class, 'deposit'])->middleware(HandlePrecognitiveRequests::class)->name('payment.deposit');
    Route::post('/requestWithdrawal', [PaymentController::class, 'requestWithdrawal'])->middleware(HandlePrecognitiveRequests::class)->name('payment.requestWithdrawal');

    Route::post('/upload-crypto-files', [\App\Http\Controllers\SettingCryptoWalletController::class, 'store'])->name('upload-crypto-files');

    /**
     * ==============================
     *         Account Info
     * ==============================
     */
    Route::get('/account_info', [AccountInfoController::class, 'account_info'])->name('account_info');
    Route::post('/add-trading-account', [AccountInfoController::class, 'add_trading_account'])->name('add_trading_account');
    Route::post('change-leverage', [AccountInfoController::class, 'change_leverage'])->name('change_leverage');

});

Route::get('/components/buttons', function () {
    return Inertia::render('Components/Buttons');
})->middleware(['auth', 'verified'])->name('components.buttons');

require __DIR__ . '/auth.php';
