<?php

use Illuminate\Support\Facades\Route;

/* ───────────────────── Controllers ───────────────────── */
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StadiumController;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\WalletTopupController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\NotificationReceiptController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\PlayerNotificationController;
use App\Http\Controllers\MatchDetailsController;
use App\Models\Setting;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PlayerRatingController;
use App\Http\Controllers\HomeCourtsSFController;
// Correctly imported controllers for password reset
use App\Http\Controllers\Auth\Owner\ForgotPasswordController;
use App\Http\Controllers\Auth\Owner\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| Consolidated Routes
|--------------------------------------------------------------------------
| يجمع مسارات الـ API للهاتف ومسارات الـ Web للإدارة.
*/

/*======================================================================
| 📲  API ROUTES  (prefix = /api/v1)
======================================================================*/
Route::prefix('api/v1')->middleware('api')->group(function () {

    /*────────── 🔓 Public Endpoints ──────────*/
    Route::post('/register',   [AuthController::class, 'register']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/login',      [AuthController::class, 'login']);
    Route::post('password/request-reset', [AuthController::class, 'requestPasswordReset']);
    Route::post('password/confirm-reset', [AuthController::class, 'confirmPasswordReset']);

    // PUBLIC browse + details (do not duplicate inside auth group)
    Route::get('/stadiums',               [StadiumController::class, 'index']);
    Route::get('/stadiums/{stadium}',     [StadiumController::class, 'show']);

    Route::get('/rankings',               [RankingController::class, 'index']);
    Route::get('/matches',                [MatchController::class, 'index']);
    Route::get('/matches/{reservation}',  [MatchController::class, 'show'])->whereNumber('reservation');

    /*─── Payment Callbacks ───*/
    Route::post('payment/tlync/callback',     [WalletTopupController::class, 'handleTlyncCallback'])->name('payment.tlync.callback');
    Route::get ('payment/tlync/return',       [WalletTopupController::class, 'handleTlyncReturn'])->name('payment.tlync.return');
    Route::get ('payment/local-card/return',  [WalletTopupController::class, 'handleLocalCardReturn'])->name('payment.local-card.return');

    // (Keep if still used elsewhere)
    Route::get('/home-courts-sf', [HomeCourtsSFController::class, 'index']);

    /*────────── 🔐 Sanctum-Protected Routes ──────────*/
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout',       [AuthController::class, 'logout']);
        Route::post('/device-token', DeviceTokenController::class);

        /* 💰 Wallet */
        Route::get ('/wallet/transactions',          [WalletTopupController::class, 'getTransactions']);
        Route::post('/wallet/topup/local-card',      [WalletTopupController::class, 'localCard']);
        Route::post('/wallet/topup/tlync/pay',       [WalletTopupController::class, 'tlyncPay']);
        Route::post('/wallet/topup/adfali/verify',   [WalletTopupController::class, 'verifyAdfali']);
        Route::post('/wallet/topup/adfali/confirm',  [WalletTopupController::class, 'confirmAdfali']);
        Route::post('/wallet/topup/sadad/verify',    [WalletTopupController::class, 'verifySadad']);
        Route::post('/wallet/topup/sadad/confirm',   [WalletTopupController::class, 'confirmSadad']);

        /* 🏟 Stadiums (protected actions only) */
        Route::post('/stadiums/{stadium}/toggle-favourite', [StadiumController::class, 'toggleFavourite']);
        Route::get ('/stadiums/{stadium}/available-hours',  [StadiumController::class, 'getAvailableHours']);

        /* 📅 Bookings */
        Route::post   ('/bookings',                       [BookingController::class, 'store']);
        Route::get    ('/bookings',                       [BookingController::class, 'index']);
        Route::post   ('/bookings/{reservation}/accept',  [BookingController::class, 'accept']);
        Route::delete ('/bookings/{reservation}',         [BookingController::class, 'cancel']);
        Route::post('bookings/{reservation}/confirm-preliminary', [BookingController::class, 'confirmPreliminary']);

        /* ✍️ Complaints */
        Route::post('/complaints', [ComplaintController::class, 'store']);

        /* ⚽ Match Details (Host/Player Actions) */
        Route::prefix('matches/{reservation}')->whereNumber('reservation')->group(function () {
            Route::post   ('/join',               [MatchDetailsController::class, 'requestToJoin']);
            Route::get    ('/squad',              [MatchDetailsController::class, 'getSquadStatus']);
            Route::put    ('/players/{player}',   [MatchDetailsController::class, 'managePlayerStatus']);
            Route::delete ('/players/{player}',   [MatchDetailsController::class, 'kickPlayer']);
            Route::post   ('/leave',              [MatchDetailsController::class, 'leaveMatch']);
            Route::post   ('/add-players',        [MatchDetailsController::class, 'addPlayers']);
            Route::post   ('/kick/{player}',      [MatchDetailsController::class, 'kickPlayer']);
        });

        /* 🧑‍🤝‍🧑 Players */
        Route::get ('/players/recommendations',        [PlayerController::class, 'recommendations']);
        Route::get ('/players/search',                 [PlayerController::class, 'search']);
        Route::get ('/players/favourites',             [PlayerController::class, 'getFavourites']);
        Route::post('/players/favourites/{player}',    [PlayerController::class, 'toggleFavourite']);

        /* ✉️ Invitations */
        Route::get ('/invitations',                        [InvitationController::class, 'index']);
        Route::post('/invitations/{invite}/accept',        [InvitationController::class, 'accept']);
        Route::post('/invitations/{invite}/decline',       [InvitationController::class, 'decline']);
        Route::get ('/invitations/pending-count',          [InvitationController::class, 'pendingCount']);

        /* 🔔 Notifications */
        Route::post  ('/notifications/receipt',               NotificationReceiptController::class);
        Route::get   ('/player-notifications',                [PlayerNotificationController::class, 'index']);
        Route::get   ('/player-notifications/unread-count',   [PlayerNotificationController::class, 'unreadCount']);
        Route::post  ('/player-notifications/mark-as-read',   [PlayerNotificationController::class, 'markAsRead']);
        Route::delete('/player-notifications/clear-all',      [PlayerNotificationController::class, 'clearAll']);

        /* 👤 Profile */
        Route::get ('/user',                   [ProfileController::class, 'show']);
        Route::post('/user/update',            [ProfileController::class, 'update']);
        Route::post('/user/password/change',   [ProfileController::class, 'changePassword']);

        /* ⭐ Favourites */
        Route::get ('/favourites/players',               [FavouriteController::class, 'players']);
        Route::post('/players/{player}/favourite',       [FavouriteController::class, 'togglePlayer']);
        Route::get ('/favourites/stadiums',              [FavouriteController::class, 'stadiums']);
        Route::post('/stadiums/{stadium}/favourite',     [FavouriteController::class, 'toggleStadium']);

        // --- RATING & REVIEW ROUTES ---
        Route::get('/pending-reviews',        [ReviewController::class, 'getPendingReviews']);
        Route::get('/pending-player-ratings', [PlayerRatingController::class, 'getPending']);
        Route::post('/reviews',               [ReviewController::class, 'store']);
        Route::post('/player-ratings',        [PlayerRatingController::class, 'store']);
    });
});

/*======================================================================
| 🌐  WEB ROUTES  (لوحة التحكُّم والمتصفح)
======================================================================*/
Route::get('/', function () {
    $keys = ['site_name','email','phone','address','about_text'];
    $settings = Setting::whereIn('key', $keys)->pluck('value','key');
    return view('homepage.home', compact('settings'));
})->name('homepage.home');

/* صفحات ثابتة */
Route::view('/home',        'homepage.home');
Route::view('/about',       'homepage.about')->name('homepage.about');
Route::view('/contact',     'homepage.contact')->name('homepage.contact');
Route::view('/ourservices', 'homepage.ourservices')->name('homepage.ourservices');

/* مصادقة الويب */
Route::get ('/login',  [AuthenticatedSessionController::class,'showLoginForm'])->name('login');
Route::post('/login',  [AuthenticatedSessionController::class,'login'])->name('login.post');
Route::post('/logout', [AuthenticatedSessionController::class,'logout'])->name('logout');

// ============================================================================
// ✅ Correct Owner Password Reset Routes (outside auth groups)
// ============================================================================
Route::get('owner/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('owner.password.request');
Route::post('owner/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('owner.password.email');
Route::get('owner/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('owner.password.reset');
Route::post('owner/reset-password', [ResetPasswordController::class, 'reset'])->name('owner.password.update');

/* لوحات الإدارة */
Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::match(['get','post','delete'], '/players',   [AdminController::class,'players'])->name('players');
    Route::match(['get','post','delete'], '/owners',    [AdminController::class,'owners'])->name('owners');
    Route::match(['get','post'], '/stadiums',           [AdminController::class,'stadiums'])->name('stadiums');
    Route::match(['get','post','delete'], '/services',  [AdminController::class,'services'])->name('services');
    Route::get('/profile',      [AdminController::class,'profile'])->name('profile');
    Route::post('/profile',     [AdminController::class,'updateProfile'])->name('profile.update');
    Route::get('/support',      [AdminController::class,'support'])->name('support');
    Route::put('/support/{id}', [AdminController::class,'updateSupport'])->name('support.update');
    Route::delete('/support/{id}', [AdminController::class,'destroySupport'])->name('support.destroy');
    Route::view('/reports', 'admin.reports.reports')->name('reports');
    Route::match(['get','post'], '/settings',      [AdminController::class,'settings'])->name('settings');
    Route::match(['get','post'], '/notifications', [AdminController::class,'notifications'])->name('notifications');
    Route::get('/reports',       [AdminController::class,'reports'])->name('reports');
    Route::get('/reports/data',  [AdminController::class,'reportsData'])->name('reports.data');
});

Route::prefix('provider')->middleware('auth:owner')->name('provider.')->group(function () {
    Route::get('/dashboard',  [ProviderController::class,'dashboard'])->name('dashboard');
    Route::get('/dashboard2', [ProviderController::class,'dashboard2'])->name('dashboard2');
    Route::get('/stadiums',             [ProviderController::class,'stadiums'])->name('stadiums.index');
    Route::patch('/stadiums/{stadium}', [ProviderController::class,'toggle'])->name('stadiums.toggle');
    Route::get('/bookings',              [ProviderController::class,'bookings'])->name('bookings.index');
    Route::post('/bookings',             [ProviderController::class,'bookings'])->name('bookings.store');
    Route::post('/bookings/{id}/archive',[ProviderController::class,'archive'])->name('bookings.archive');
    Route::get('/available-slots',       [ProviderController::class,'availableSlots'])->name('slots');
    Route::get('/reviews', [ProviderController::class,'reviews'])->name('reviews.index');
    Route::post('/complaints', [ProviderController::class, 'storeComplaint'])->name('complaints.store');

    Route::get('/reports', [ProviderController::class,'reports'])->name('reports.index');
    Route::get('/profile',  [ProviderController::class,'profile'])->name('profile');
    Route::post('/profile', [ProviderController::class,'updateProfile'])->name('profile.update');
    Route::post('/notifications/mark-as-read', [ProviderController::class,'markNotificationsRead'])->name('notifications.read');
    Route::get ('/notifications/unread-count', [ProviderController::class,'unreadCountJson'])->name('notifications.unread_count');

        Route::get('/reports/analytics', [ProviderController::class, 'reportsIndex'])->name('reports.index');
    Route::get('/reports/analytics/data', [ProviderController::class, 'reportsData'])->name('reports.data');

});

/* لوحة مختصرة للمالك أو المستخدم */
Route::get('/dashboard2', [ProviderController::class,'dashboard2'])
    ->middleware('auth:web,owner')
    ->name('user.dashboard');
