<?php

use App\Http\Controllers\AccountInfoController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\InternalTransferController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\TradingController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Session as FacadesSession;
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
    $firstTimeLogin = FacadesSession::get('first_time_logged_in');

    return Inertia::render('Dashboard', [
        'firstTimeLogin' => $firstTimeLogin
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/update-session', function () {
    Session::put('first_time_logged_in', 0);
    return back();
})->middleware(['auth', 'verified']);

Route::post('ompay/depositResult', [PaymentController::class, 'depositResult']);
// Route::match(['get', 'post'], 'ompay/depositResult', [PaymentController::class, 'depositResult']);
Route::post('ompay/updateStatus', [PaymentController::class, 'updateResult']);
Route::middleware('auth')->group(function () {
    Route::get('/monthly-deposit', [GeneralController::class, 'monthly_deposit'])->name('monthly_deposit');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /**
     * ==============================
     *           Payments
     * ==============================
     */
    Route::post('/deposit', [PaymentController::class, 'deposit'])->middleware(HandlePrecognitiveRequests::class)->name('payment.deposit');
    Route::post('/requestWithdrawal', [PaymentController::class, 'requestWithdrawal'])->middleware(HandlePrecognitiveRequests::class)->name('payment.requestWithdrawal');
    Route::post('/applyRebate', [PaymentController::class, 'applyRebate'])->middleware('role:ib')->name('payment.applyRebate');

//    Route::post('/upload-crypto-files', [\App\Http\Controllers\SettingCryptoWalletController::class, 'store'])->name('upload-crypto-files');

    /**
     * ==============================
     *         Account Info
     * ==============================
     */

    Route::prefix('account_info')->group(function () {
        Route::get('/account_listing', [AccountInfoController::class, 'account_info'])->name('account_info.account_info');
        Route::post('/add-trading-account', [AccountInfoController::class, 'add_trading_account'])->name('account_info.add_trading_account');
        Route::post('change-leverage', [AccountInfoController::class, 'change_leverage'])->name('account_info.change_leverage');

        Route::get('/getTradingAccounts', [AccountInfoController::class, 'getTradingAccounts'])->name('account_info.getTradingAccounts');
    });

    /**
     * ==============================
     *           Trading
     * ==============================
     */

    Route::prefix('trading')->middleware('role:ib')->group(function () {
        Route::get('/rebate_summary', [TradingController::class, 'rebate_summary'])->name('trading.rebate_summary');
        Route::get('/getRebateSummary', [TradingController::class, 'getRebateSummary'])->name('account_info.getRebateSummary');
    });

    /**
     * ==============================
     *         Transaction
     * ==============================
     */
    Route::get('/transaction', [InternalTransferController::class, 'transaction'])->name('transaction');

    Route::post('/wallet_to_account', [InternalTransferController::class, 'wallet_to_account'])->name('wallet_to_account');
    Route::post('/account_to_wallet', [InternalTransferController::class, 'account_to_wallet'])->name('account_to_wallet');
    Route::post('/account_to_account', [InternalTransferController::class, 'account_to_account'])->name('account_to_account');

     /**
     * ==============================
     *          Network Tree
     * ==============================
     */
     Route::prefix('group_network')->group(function () {
         Route::get('/network_tree', [NetworkController::class, 'network'])->name('group_network.network_tree');
         Route::get('/rebate_allocation', [NetworkController::class, 'getRebateAllocation'])->middleware('role:ib')->name('group_network.rebate_allocation');
         Route::post('/rebate_allocation', [NetworkController::class, 'updateRebateAllocation'])->middleware('role:ib')->name('updateRebate.update');
     });

});

Route::get('/components/buttons', function () {
    return Inertia::render('Components/Buttons');
})->middleware(['auth', 'verified'])->name('components.buttons');

require __DIR__ . '/auth.php';
