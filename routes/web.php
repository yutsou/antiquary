<?php

use App\Http\Controllers\MartController;
use App\Http\Middleware\EnsureMemberIsValid;
use App\Http\Middleware\EnsureSellerIsValid;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ExpertController;
use App\Http\Controllers\AuctioneerController;
use App\Http\Middleware\EnsureIsAuctioneer;
use App\Http\Middleware\EnsureIsExpert;

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

Route::get('/', [MartController::class, 'showHomepage']);
Route::get('/auctions/{auctionId}', [MartController::class, 'showAuction'])->name('mart.auctions.show');
Route::get('/lots/{lotId}', [MartController::class, 'showLot'])->name('mart.lots.show');
Route::get('/warning', [MartController::class, 'showWarning'])->name('mart.warning.show');
Route::get('/search', [MartController::class, 'searchLots'])->name('mart.lots.search');
Route::get('/m-categories/{mCategoryId}', [MartController::class, 'showMCategory'])->name('mart.m_categories.show');
Route::get('/m-categories/{mCategoryId}/s-categories/{sCategoryId}', [MartController::class, 'showSCategory'])->name('mart.s_categories.show');

#Auth
Route::get('/register', [AuthController::class, 'showRegister']);
Route::get('/login/{redirectUrl?}', [AuthController::class, 'showLogin'])->name('login.show');
Route::post('/login/{redirectUrl?}', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');

#Auth Line
Route::get('/auth/line/login', [AuthController::class, 'redirectLineLogin'])->name('auth.line.login');
Route::get('/auth/line/callback', [AuthController::class, 'lineCallback'])->name('auth.line.callback');

Route::get('/password/forgot', [AuthController::class, 'showPasswordForgot'])->name('auth.password_forgot.show');
Route::post('/password-reset-confirm/send', [AuthController::class, 'sendPasswordResetConfirm'])->name('auth.password_reset_confirm.send');
Route::get('/password/reset/{token}', [AuthController::class, 'showPasswordReset'])->name('auth.password_reset.show');
Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('auth.password.reset');

#line bind
Route::get('/auth/line/generate-verify-code', [AuthController::class, 'generateLineVerifyCode'])->name('auth.line_verify_code.generate');
Route::get('/auth/line/verify-bind', [AuthController::class, 'showLineVerifyBind'])->name('auth.line.verify_bind');
Route::post('auth/line/bind', [AuthController::class, 'lineBind'])->name('auth.line.bind');
Route::get('/auth/line/confirm-bind', [AuthController::class, 'showLineBindConfirm'])->name('auth.line_bind.confirm');

#google bind
Route::get('/auth/google/handle', [AuthController::class, 'redirectGoogleHandle'])->name('auth.google.handle');
Route::get('/auth/google/callback', [AuthController::class, 'googleCallback']);

Route::post('/pay/ecpay/receive', [MartController::class, 'payEcpayReceive'])->name('shop.pay.ecpay.receive');
Route::post('/pay/ecpay/order-receive', [MartController::class, 'payEcpayOrderReceive'])->name('shop.pay.ecpay.order_receive');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'switchRole'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/orders/{orderId}/chatroom', [MartController::class, 'indexMessages'])->name('mart.chatroom.show');
    Route::post('/orders/{orderId}/messages', [MartController::class, 'sendMessage'])->name('mart.messages.send');
    Route::post('/messages/{messageId}/haveRead', [MartController::class, 'haveRead'])->name('mart.messages.haveRead');
});

############################################################# Auctioneer #############################################################
Route::prefix('auctioneer/dashboard')->middleware(EnsureIsAuctioneer::class)->group(function () {
    Route::get('/', [AuctioneerController::class, 'showDashboard'])->name('auctioneer.dashboard');
    Route::get('/experts/create', [AuctioneerController::class, 'createExpert'])->name('auctioneer.experts.create');
    Route::post('/experts', [AuctioneerController::class, 'storeExpert'])->name('auctioneer.experts.store');
    Route::get('/experts', [AuctioneerController::class, 'indexExperts'])->name('auctioneer.experts.index');
    Route::get('/experts/{userId}/edit', [AuctioneerController::class, 'editexpert'])->name('auctioneer.experts.edit');
    Route::post('/experts/{userId}', [AuctioneerController::class, 'updateUserWhoIsExpert'])->name('auctioneer.experts.update');
    Route::get('/main-categories/create', [AuctioneerController::class, 'createMainCategory'])->name('auctioneer.main_categories.create');
    Route::post('/main-categories', [AuctioneerController::class, 'storeMainCategory'])->name('auctioneer.main_categories.store');
    Route::get('/main-categories', [AuctioneerController::class, 'indexMainCategories'])->name('auctioneer.main_categories.index');
    Route::get('/main-categories/{mainCategoryId}/edit', [AuctioneerController::class, 'editMainCategory'])->name('auctioneer.main_categories.edit');
    Route::post('/main-categories/{mainCategoryId}', [AuctioneerController::class, 'updateMainCategory'])->name('auctioneer.main_categories.update');

    Route::get('/orders', [AuctioneerController::class, 'indexOrders'])->name('auctioneer.orders.index');
    Route::get('/orders/{orderId}', [AuctioneerController::class, 'showOrder'])->name('auctioneer.orders.show');
    Route::post('/orders/{orderId}/notice-shipping', [AuctioneerController::class, 'noticeShipping'])->name('auctioneer.orders.notice_shipping');
    Route::post('/orders/{orderId}/notice-arrival', [AuctioneerController::class, 'noticeArrival'])->name('auctioneer.orders.notice_arrival');
    Route::post('/orders/{orderId}/notice-remit', [AuctioneerController::class, 'noticeRemit'])->name('auctioneer.orders.notice_remit');

    Route::post('/orders/{orderId}/notice-confirm-atm-pay', [AuctioneerController::class, 'noticeConfirmAtmPay'])->name('auctioneer.orders.notice_confirm_atm_pay');

    Route::get('/orders/{orderId}/chatroom', [AuctioneerController::class, 'indexMessages'])->name('auctioneer.orders.chatroom_show');
    Route::get('/orders/{orderId}/member-chatroom', [AuctioneerController::class, 'indexMemberMessages'])->name('auctioneer.orders.member_chatroom_show');

    Route::get('/promotions', [AuctioneerController::class, 'indexPromotions'])->name('auctioneer.promotions.index');
    Route::post ('/promotions', [AuctioneerController::class, 'updatePromotion'])->name('auctioneer.promotions.update');
});
Route::prefix('auctioneer')->middleware(EnsureIsAuctioneer::class)->group(function () {
    Route::get('/ajax/experts', [AuctioneerController::class, 'ajaxExperts'])->name('ajax.experts.get');
    Route::get('/ajax/orders', [AuctioneerController::class, 'ajaxGetOrders'])->name('ajax.auctioneer.orders.get');
});
############################################################# Expert #############################################################
Route::prefix('expert/dashboard')->middleware(EnsureIsExpert::class)->group(function () {
    Route::get('/', [ExpertController::class, 'showDashboard'])->name('expert.dashboard');

    Route::get('/main-categories/{mainCategoryId}/sub-categories', [ExpertController::class, 'indexSubCategory'])->name('expert.sub_categories.index');
    Route::get('/main-categories/{mainCategoryId}/sub-categories/create', [ExpertController::class, 'createSubCategory'])->name('expert.sub_categories.create');
    Route::post('/main-categories/{mainCategoryId}/sub-categories', [ExpertController::class, 'storeSubCategory'])->name('expert.sub_categories.store');
    Route::get('/main-categories/{mainCategoryId}/sub-categories/{subCategoryId}', [ExpertController::class, 'editSubCategory'])->name('expert.sub_categories.edit');
    Route::post('/main-categories/{mainCategoryId}/sub-categories/{subCategoryId}', [ExpertController::class, 'updateSubCategory'])->name('expert.sub_categories.update');

    Route::get('/main-categories/{mainCategoryId}/default-specification-titles/manage', [ExpertController::class, 'manageDefaultSpecificationTitles'])->name('expert.default_specification_titles.manage');
    Route::post('/main-categories/{mainCategoryId}/default-specification-titles', [ExpertController::class, 'storeDefaultSpecificationTitles'])->name('expert.default_specification_titles.store');

    Route::get('/main-categories/{mainCategoryId}/lots', [ExpertController::class, 'indexLots'])->name('expert.lots.index');
    Route::get('/main-categories/{mainCategoryId}/lots/{lotId}/review', [ExpertController::class, 'reviewLot'])->name('expert.lots.review');
    Route::post('/main-categories/{mainCategoryId}/lots/{lotId}', [ExpertController::class, 'handleLot'])->name('expert.lots.handle');
    Route::post('/main-categories/{mainCategoryId}/lots/{lotId}/receive', [ExpertController::class, 'receiveLot'])->name('expert.lots.receive');
    Route::post('/main-categories/{mainCategoryId}/lots/{lotId}/take-down', [ExpertController::class, 'takeDownLot'])->name('expert.lots.take-down');

    Route::get('/main-categories/{mainCategoryId}/auctions', [ExpertController::class, 'showAuctions'])->name('expert.auctions.show');
    Route::get('/main-categories/{mainCategoryId}/auctions/create', [ExpertController::class, 'createAuction'])->name('expert.auctions.create');
    Route::post('/main-categories/{mainCategoryId}/auctions', [ExpertController::class, 'storeAuction'])->name('expert.auctions.store');

    Route::get('/main-categories/{mainCategoryId}/lots/{lotId}/returned-logistic-info', [ExpertController::class, 'editReturnedLogisticInfo'])->name('expert.returned_lot_logistic_info.edit');
    Route::post('/main-categories/{mainCategoryId}/lots/{lotId}/returned-logistic-info', [ExpertController::class, 'updateReturnedLogisticInfo'])->name('expert.returned_lot_logistic_info.update');


    Route::get('/main-categories/{mainCategoryId}/lots/{lotId}/logistic-info', [ExpertController::class, 'createUnsoldLotLogisticInfo'])->name('expert.unsold_lot_logistic_info.create');
    Route::post('/main-categories/{mainCategoryId}/lots/{lotId}/logistic-info', [ExpertController::class, 'storeUnsoldLotLogisticInfo'])->name('expert.unsold_lot_logistic_info.store');
});
Route::prefix('expert')->middleware(EnsureIsExpert::class)->group(function () {
    Route::get('/ajax/review/lots/{mainCategoryId}', [ExpertController::class, 'ajaxReviewGetLots']);
    Route::get('/ajax/create-auction/lots/{mainCategoryId}', [ExpertController::class, 'ajaxCreateAuctionGetLots'])->name('expert.ajax.auction-lot.get');
    Route::get('/ajax/auctions/{mainCategoryId}', [ExpertController::class, 'ajaxGetAuctions'])->name('expert.ajax.auction.get');
});

############################################################# Account #############################################################
Route::prefix('account')->middleware(['auth', EnsureMemberIsValid::class, EnsureSellerIsValid::class])->group(function () {
    #application
    Route::get('/applications/create', [MemberController::class, 'createLot'])->name('account.applications.create');
    Route::post('/applications', [MemberController::class, 'storeLot'])->name('account.applications.store');
    Route::get('/applications/{lotId}', [MemberController::class, 'editLot'])->name('account.applications.edit');
    Route::post('/applications/{lotId}', [MemberController::class, 'updateLot'])->name('account.applications.update');
    Route::get('/applications', [MemberController::class, 'indexApplications'])->name('account.applications.index');
    Route::get('/applications/{lotId}/logistic-info', [MemberController::class, 'createApplicationLogisticInfo'])->name('account.application_logistic_info.create');
    Route::post('/applications/{lotId}/application-logistic-info/store', [MemberController::class, 'storeApplicationLogisticInfo'])->name('account.application_logistic_info.store');

    #selling lots
    Route::get('/lots', [MemberController::class, 'indexSellingLots'])->name('account.selling_lots.index');
    Route::get('/unsold-lots/{lotId}', [MemberController::class, 'editUnsoldLot'])->name('account.unsold_lots.edit');
    Route::post('/unsold-lots/{lotId}', [MemberController::class, 'handleUnsoldLot'])->name('account.unsold_lots.handle');
    #finished lots
    Route::get('/finished-lots', [MemberController::class, 'indexFinishedLots'])->name('account.finished_lots.index');

    #returned lots
    Route::get('/returned-lots', [MemberController::class, 'indexReturnedLots'])->name('account.returned_lots.index');
    Route::get('/returned-lots/{lotId}', [MemberController::class, 'editReturnedLot'])->name('account.returned_lots.edit');
    Route::post('/returned-lots/{lotId}', [MemberController::class, 'updateReturnedLot'])->name('account.returned_lots.update');



});

Route::prefix('account')->middleware(['auth', EnsureMemberIsValid::class])->group(function () {
    Route::get('/', [MemberController::class, 'showDashboard'])->name('account');


    Route::get('/favorites', [MemberController::class, 'showFavorites'])->name('account.favorites.index');

    Route::get('/orders', [MemberController::class, 'indexOrders'])->name('account.orders.index');
    Route::get('/orders/{orderId}', [MemberController::class, 'showOrder'])->name('account.orders.show');
    Route::get('/orders/{orderId}/edit', [MemberController::class, 'editOrder'])->name('account.orders.edit');
    Route::post('/orders/{orderId}', [MemberController::class, 'updateOrder'])->name('account.orders.update');

    Route::post('/orders/{orderId}/confirm', [MemberController::class, 'confirmOrder'])->name('account.orders.confirm');
    Route::get('/orders/{orderId}/pay', [MemberController::class, 'pay'])->name('account.orders.pay');
    Route::get('/orders/{orderId}/atm-pay-info', [MemberController::class, 'showAtmPayInfo'])->name('account.atm_pay_info.show');
    Route::post('/orders/{orderId}/notice-atm-pay', [MemberController::class, 'noticeAtmPay'])->name('account.atm_pay.notice');
    Route::post('/orders/{orderId}/complete', [MemberController::class, 'completeOrder'])->name('account.orders.complete');

    Route::post('/orders/{orderId}/notice-shipping', [MemberController::class, 'noticeShipping'])->name('account.orders.notice_shipping');
    Route::get('/orders/{orderId}/shipping-info', [MemberController::class, 'showShippingInfo'])->name('account.orders.show_shipping_info');
    Route::post('/orders/{orderId}/notice-arrival', [MemberController::class, 'noticeArrival'])->name('account.orders.notice_arrival');

    Route::get('/biding-lots', [MemberController::class, 'indexBiddingLots'])->name('account.bidding_lots.index');

    Route::get('/ajax/main-categories/{mainCategoryId}/sub-categories', [MemberController::class, 'ajaxSubCategories'])->name('account.ajax.sub_categories.get');
    Route::post('/ajax/lots/{lotId}/favorite', [MemberController::class, 'ajaxHandleFavorite'])->name('account.ajax.favorite.handle');
    Route::post('/axios/lots/manual_bid', [MemberController::class, 'manualBid']);
    Route::post('/axios/lots/auto_bid', [MemberController::class, 'autoBid']);

    Route::get('/bind', [AuthController::class, 'showBind'])->name('account.bind.show');

    Route::get('/notices', [MemberController::class, 'indexNotices'])->name('account.notices.index');
    Route::get('/unread-notices', [MemberController::class, 'indexUnreadNotices'])->name('account.unread_notices.index');
});

Route::prefix('account')->middleware('auth')->group(function () {
    Route::get('/profile', [MemberController::class, 'editProfile'])->name('account.profile.edit');
    Route::post('/profile', [MemberController::class, 'updateProfile'])->name('account.profile.update');
    Route::get('/profile/email', [MemberController::class, 'editEmail'])->name('account.profile_email.edit');
    Route::post('/profile/send-verify-code', [AuthController::class, 'sendVerifyCode'])->name('account.verify_code.send');
    Route::get('/profile/phone', [MemberController::class, 'editPhone'])->name('account.profile_phone.edit');
    Route::post('/profile/verify-code', [AuthController::class, 'verifyCode'])->name('account.code.verify');
    Route::get('/change-password', [AuthController::class, 'changePassword'])->name('account.password.change');
    Route::post('/update-password', [AuthController::class, 'updatePassword'])->name('account.password.update');
    Route::get('/seller/edit', [MemberController::class, 'editSeller'])->name('account.seller.edit');
    Route::post('/seller/update', [MemberController::class, 'updateSeller'])->name('account.seller.update');
    Route::post('/ajax/notices/read', [MemberController::class, 'readNotices'])->name('account.notices.read');
});

Route::get('/test/delete/{lotId}', [MemberController::class, 'testDelete']);
Route::get('/test/schedule', [MemberController::class, 'testSchedule']);
Route::post('postTest', [MemberController::class, 'postTest']);
Route::get('/testOrder', [MartController::class, 'test']);
Route::get('/test', [MartController::class, 'test']);
