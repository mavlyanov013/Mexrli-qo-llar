<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CaseController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Api\HelpRequestController;
use App\Http\Controllers\Api\VolunteerApplicationController;
use App\Http\Controllers\Api\FinancialReportController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\BlogPostController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\ContactMessageController;

use App\Http\Controllers\Api\Billing\PaycomController;
use App\Http\Controllers\Api\Billing\ClickController;
use App\Http\Controllers\Api\Billing\PaynetController;
use App\Http\Controllers\Api\Billing\UzumBankController;
use App\Http\Controllers\Api\Billing\CheckoutController;

use App\Http\Controllers\Api\Admin\PaymentController as AdminPaymentController;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | 💳 BILLING (PAYMENT PROVIDERS)
    |--------------------------------------------------------------------------
    */

    Route::post('/billing/paycom', [PaycomController::class, 'handle']);
    Route::post('/billing/click', [ClickController::class, 'handle']);

    Route::match(['GET', 'POST'], '/billing/paynet', [PaynetController::class, 'handle']);
    Route::get('/billing/paynet/status/{payment}', [PaynetController::class, 'status']);

    Route::match(['GET', 'POST'], '/billing/uzumbank', [UzumBankController::class, 'handle']);
    Route::get('/billing/uzumbank/status/{payment}', [UzumBankController::class, 'status']);

    Route::post('/billing/checkout/init', [CheckoutController::class, 'init']);


    /*
    |--------------------------------------------------------------------------
    | 💰 DONATIONS (PUBLIC)
    |--------------------------------------------------------------------------
    */

    // 🔥 FAqat completed donationlar (frontend ishlatadi)
    Route::get('/donations', [DonationController::class, 'publicIndex']);

    // 🔥 Live stream
    Route::get('/donations/live', [DonationController::class, 'live']);

    // 🔥 Donation yaratish
    Route::post('/donations', [DonationController::class, 'store']);


    /*
    |--------------------------------------------------------------------------
    | 📊 REPORTS
    |--------------------------------------------------------------------------
    */

    Route::get('/financial-reports', [FinancialReportController::class, 'index']);
    Route::get('/financial-reports/latest', [FinancialReportController::class, 'latest']);
    Route::get('/financial-reports/{id}', [FinancialReportController::class, 'show']);


    /*
    |--------------------------------------------------------------------------
    | 📄 CONTENT (PUBLIC)
    |--------------------------------------------------------------------------
    */

    Route::get('/partners', [PartnerController::class, 'index']);

    Route::get('/blog-posts', [BlogPostController::class, 'index']);
    Route::get('/blog-posts/{id}', [BlogPostController::class, 'show']);

    Route::get('/cases', [CaseController::class, 'index']);
    Route::get('/cases/{id}', [CaseController::class, 'show']);

    Route::get('/home', [PageController::class, 'home']);
    Route::get('/pages/{slug}', [PageController::class, 'show']);


    /*
    |--------------------------------------------------------------------------
    | 📨 FORMS
    |--------------------------------------------------------------------------
    */

    Route::post('/help-requests', [HelpRequestController::class, 'store']);
    Route::post('/volunteer-applications', [VolunteerApplicationController::class, 'store']);
    Route::post('/contact-messages', [ContactMessageController::class, 'store']);


    /*
    |--------------------------------------------------------------------------
    | 🔐 AUTH
    |--------------------------------------------------------------------------
    */

    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:api')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });


    /*
    |--------------------------------------------------------------------------
    | 🛠 ADMIN PANEL
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth:api', 'admin'])->prefix('admin')->group(function () {

        // 📦 Cases
        Route::get('/cases', [CaseController::class, 'index']);
        Route::post('/cases', [CaseController::class, 'store']);
        Route::get('/cases/{id}', [CaseController::class, 'show']);
        Route::put('/cases/{id}', [CaseController::class, 'update']);
        Route::delete('/cases/{id}', [CaseController::class, 'destroy']);

        // 💰 Donations (ALL, admin uchun)
        Route::get('/donations', [DonationController::class, 'index']);

        // 💳 Payments
        Route::get('/payments', [AdminPaymentController::class, 'index']);
        Route::get('/payments/{id}', [AdminPaymentController::class, 'show']);

        // 📩 Help Requests
        Route::get('/help-requests', [HelpRequestController::class, 'index']);
        Route::get('/help-requests/{id}', [HelpRequestController::class, 'show']);
        Route::put('/help-requests/{id}', [HelpRequestController::class, 'update']);

        // 🙋 Volunteers
        Route::get('/volunteer-applications', [VolunteerApplicationController::class, 'index']);
        Route::get('/volunteer-applications/{id}', [VolunteerApplicationController::class, 'show']);
        Route::put('/volunteer-applications/{id}', [VolunteerApplicationController::class, 'update']);

        // 📬 Contact Messages
        Route::get('/contact-messages', [ContactMessageController::class, 'index']);
        Route::get('/contact-messages/{id}', [ContactMessageController::class, 'show']);
        Route::put('/contact-messages/{id}', [ContactMessageController::class, 'update']);

        // 📰 Blog
        Route::get('/blog-posts', [BlogPostController::class, 'index']);
        Route::post('/blog-posts', [BlogPostController::class, 'store']);
        Route::get('/blog-posts/{id}', [BlogPostController::class, 'show']);
        Route::put('/blog-posts/{id}', [BlogPostController::class, 'update']);
        Route::delete('/blog-posts/{id}', [BlogPostController::class, 'destroy']);
    });
});
