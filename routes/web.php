<?php

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\OtpVerificationController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserSessionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('app.dashboard') : redirect()->route('login');
})->name('home');

Route::get('/sitemap.xml', function () {
    $baseUrl = url('/');
    $lastMod = now()->toAtomString();

    $urls = [
        ['loc' => $baseUrl, 'priority' => '1.0', 'changefreq' => 'weekly'],
        ['loc' => url('/login'), 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => url('/register'), 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => url('/forgot-password'), 'priority' => '0.5', 'changefreq' => 'monthly'],
    ];

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

    foreach ($urls as $u) {
        $xml .= '    <url>'."\n";
        $xml .= '        <loc>'.htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8').'</loc>'."\n";
        $xml .= '        <lastmod>'.$lastMod.'</lastmod>'."\n";
        $xml .= '        <changefreq>'.$u['changefreq'].'</changefreq>'."\n";
        $xml .= '        <priority>'.$u['priority'].'</priority>'."\n";
        $xml .= '    </url>'."\n";
    }

    $xml .= '</urlset>';

    return response($xml, 200, [
        'Content-Type' => 'application/xml',
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->name('sitemap');

Route::get('/robots.txt', function () {
    return response(file_get_contents(public_path('robots.txt')), 200, [
        'Content-Type' => 'text/plain',
    ]);
});

Route::get('/site.webmanifest', function () {
    return response(file_get_contents(public_path('site.webmanifest')), 200, [
        'Content-Type' => 'application/manifest+json',
    ]);
});

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

// OTP Email verification routes
Route::middleware(['auth', 'locale'])->group(function (): void {
    Route::get('/email/verify-otp', [OtpVerificationController::class, 'show'])
        ->name('verification.notice');

    Route::post('/email/verify-otp', [OtpVerificationController::class, 'verify'])
        ->name('verification.verify')
        ->middleware('throttle:6,1');

    Route::post('/email/verification-notification', [OtpVerificationController::class, 'resend'])
        ->name('verification.send')
        ->middleware('throttle:6,1');
});

// User area (requires authentication, verified email, and active status)
Route::middleware(['auth', 'locale', 'verified.otp', 'active'])->prefix('app')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('app.dashboard');

    // Wallets
    Route::get('/wallets', [WalletController::class, 'index'])->name('wallets.index');
    Route::post('/wallets', [WalletController::class, 'store'])->name('wallets.store');
    Route::put('/wallets/{wallet}', [WalletController::class, 'update'])->name('wallets.update');
    Route::post('/wallets/{wallet}/archive', [WalletController::class, 'toggleArchive'])->name('wallets.archive');
    Route::delete('/wallets/{wallet}', [WalletController::class, 'destroy'])->name('wallets.destroy');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::post('/categories/{category}/archive', [CategoryController::class, 'toggleArchive'])->name('categories.archive');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

    // Transfers
    Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index');
    Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');
    Route::delete('/transfers/{transfer}', [TransferController::class, 'destroy'])->name('transfers.destroy');

    // Budgets
    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::put('/budgets/{budget}', [BudgetController::class, 'update'])->name('budgets.update');
    Route::delete('/budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Profile, Security & Sessions
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/security', [ProfileController::class, 'security'])->name('profile.security');
    Route::put('/profile/security/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/profile/sessions', [UserSessionController::class, 'index'])->name('profile.sessions.index');
    Route::delete('/profile/sessions/{id}', [UserSessionController::class, 'destroy'])->name('profile.sessions.destroy');
    Route::post('/profile/sessions/logout-others', [UserSessionController::class, 'destroyOthers'])->name('profile.sessions.destroy-others');
    Route::post('/profile/sessions/logout-all', [UserSessionController::class, 'destroyAll'])->name('profile.sessions.destroy-all');
});

// Admin area (requires admin role)
Route::middleware(['auth', 'locale', 'verified.otp', 'active', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/suspension', [AdminUserController::class, 'toggleSuspension'])->name('users.suspension');
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::post('/categories/{category}/archive', [AdminCategoryController::class, 'toggleArchive'])->name('categories.archive');
    Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
    Route::put('/version', [AdminDashboardController::class, 'updateVersion'])->name('version.update');
});
