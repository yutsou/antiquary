<?php

use App\Http\Controllers\MartController;
use App\Http\Middleware\ChatOwnership;
use App\Http\Middleware\EnsureMemberIsValid;
use App\Http\Middleware\EnsureSellerIsValid;
use App\Http\Middleware\LotOwnership;
use App\Http\Middleware\OrderOwnership;
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
Route::get('/products/{lotId}', [MartController::class, 'showProduct'])->name('mart.products.show');

#Auth
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register.show');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login.show');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});

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
Route::get('/auth/google/callback', [AuthController::class, 'googleCallback'])->name('auth.google.callback');

#ecpay
Route::post('/pay/ecpay/receive', [MartController::class, 'payEcpayReceive'])->name('shop.pay.ecpay.receive');
Route::post('/pay/ecpay/order-receive', [MartController::class, 'payEcpayOrderReceive'])->name('shop.pay.ecpay.order_receive');

#gomypay
Route::get('/pay/gomypay/return', [MartController::class, 'payGomypayReturn'])->name('shop.pay.gomypay.return');
Route::post('/pay/gomypay/callback', [MartController::class, 'payGomypayCallback'])->name('shop.pay.gomypay.callback');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'switchRole'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
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
    Route::post('/orders/{orderId}/confirm-paid', [AuctioneerController::class, 'confirmPaid'])->name('auctioneer.orders.confirm_paid');
    Route::post('/orders/{orderId}/confirm-refill-transfer-info', [AuctioneerController::class, 'confirmRefillTransferInfo'])->name('auctioneer.orders.confirm_refill_transfer_info');
    Route::post('/orders/{orderId}/set-withdrawal-bid', [AuctioneerController::class, 'setWithdrawalBid'])->name('auctioneer.orders.set_withdrawal_bid');


    Route::get('/orders/{orderId}/chatroom', [AuctioneerController::class, 'indexMessages'])->name('auctioneer.orders.chatroom_show');
    Route::get('/orders/{orderId}/member-chatroom', [AuctioneerController::class, 'indexMemberMessages'])->name('auctioneer.orders.member_chatroom_show');


    Route::get('/promotions', [AuctioneerController::class, 'indexPromotions'])->name('auctioneer.promotions.index');
    Route::post ('/promotions', [AuctioneerController::class, 'updatePromotion'])->name('auctioneer.promotions.update');

    Route::get('/banners', [AuctioneerController::class, 'indexBanners'])->name('auctioneer.banners.index');
    Route::post('/banners', [AuctioneerController::class, 'createBanner'])->name('auctioneer.banners.create');
    Route::post('/banners/indexes', [AuctioneerController::class, 'updateBannerIndexes'])->name('auctioneer.banner_indexes.update');
    Route::post('/banners/{id}', [AuctioneerController::class, 'deleteBanner'])->name('auctioneer.banner.delete');

    Route::get('/members', [AuctioneerController::class, 'indexMembers'])->name('auctioneer.members.index');
    Route::get('/members/{userId}', [AuctioneerController::class, 'showMember'])->name('auctioneer.members.show');

    Route::get('/products', [AuctioneerController::class, 'showProducts'])->name('auctioneer.products.index');
    Route::get('/products/create', [AuctioneerController::class, 'createProduct'])->name('auctioneer.products.create');
    Route::post('/products', [AuctioneerController::class, 'storeLot'])->name('auctioneer.products.store');
    Route::get('/products/{lotId}', [AuctioneerController::class, 'editProduct'])->name('auctioneer.products.edit');
    Route::post('/products/{lotId}', [AuctioneerController::class, 'updateProduct'])->name('auctioneer.products.update');
    Route::post('/products/{lotId}/publish', [AuctioneerController::class, 'publishProduct'])->name('auctioneer.products.publish');
    Route::post('/products/{lotId}/unpublish', [AuctioneerController::class, 'unpublishProduct'])->name('auctioneer.products.unpublish');

    // 合併運費請求管理
    Route::get('/merge-shipping-requests', [AuctioneerController::class, 'indexMergeShippingRequests'])->name('auctioneer.merge_shipping_requests.index');
    Route::get('/merge-shipping-requests/{requestId}', [AuctioneerController::class, 'showMergeShippingRequest'])->name('auctioneer.merge_shipping_requests.show');
    Route::post('/merge-shipping-requests/{requestId}', [AuctioneerController::class, 'updateMergeShippingRequest'])->name('auctioneer.merge_shipping_requests.update');
});
Route::prefix('auctioneer')->middleware(EnsureIsAuctioneer::class)->group(function () {
    Route::get('/ajax/experts', [AuctioneerController::class, 'ajaxExperts'])->name('ajax.experts.get');
    Route::get('/ajax/orders', [AuctioneerController::class, 'ajaxGetOrders'])->name('ajax.auctioneer.orders.get');
    Route::get('/ajax/members', [AuctioneerController::class, 'ajaxMembers'])->name('ajax.members.get');
    Route::post('/ajax/members/{id}/role-upgrade', [AuctioneerController::class, 'ajaxRoleUpgradeMember'])->name('ajax.members.role-upgrade');
    Route::post('/ajax/members/{id}/role-downgrade', [AuctioneerController::class, 'ajaxRoleDowngradeMember'])->name('ajax.members.role-downgrade');
    Route::post('/ajax/members/{id}/block', [AuctioneerController::class, 'ajaxBlockMember'])->name('ajax.members.block');
    Route::post('/ajax/members/{id}/unblock', [AuctioneerController::class, 'ajaxUnblockMember'])->name('ajax.members.unblock');
    Route::get('/ajax/main-categories/{mainCategoryId}/default-specification-titles', [AuctioneerController::class, 'ajaxDefaultSpecificationTitles']);
    Route::get('/ajax/main-categories/{mainCategoryId}/sub-categories', [AuctioneerController::class, 'ajaxSubCategories']);
    Route::get('/ajax/products', [AuctioneerController::class, 'ajaxGetProducts']);


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
    Route::get('/applications', [MemberController::class, 'indexApplications'])->name('account.applications.index');
    #selling lots
    Route::get('/lots', [MemberController::class, 'indexSellingLots'])->name('account.selling_lots.index');
    #finished lots
    Route::get('/finished-lots', [MemberController::class, 'indexFinishedLots'])->name('account.finished_lots.index');
    #returned lots
    Route::get('/returned-lots', [MemberController::class, 'indexReturnedLots'])->name('account.returned_lots.index');
});
Route::prefix('account')->middleware(['auth', EnsureMemberIsValid::class])->group(function () {
#Route::prefix('account')->group(function () {
    Route::get('/', [MemberController::class, 'showDashboard'])->name('account');
    Route::get('/favorites', [MemberController::class, 'showFavorites'])->name('account.favorites.index');
    Route::get('/orders', [MemberController::class, 'indexOrders'])->name('account.orders.index');
    Route::get('/biding-lots', [MemberController::class, 'indexBiddingLots'])->name('account.bidding_lots.index');
    Route::get('/ajax/main-categories/{mainCategoryId}/sub-categories', [MemberController::class, 'ajaxSubCategories'])->name('account.ajax.sub_categories.get');
    Route::post('/ajax/lots/{lotId}/favorite', [MemberController::class, 'ajaxHandleFavorite'])->name('account.ajax.favorite.handle');
    Route::post('/axios/lots/manual_bid', [MemberController::class, 'manualBid']);
    Route::post('/axios/lots/auto_bid', [MemberController::class, 'autoBid']);
    Route::get('/bind', [AuthController::class, 'showBind'])->name('account.bind.show');
    Route::get('/notices', [MemberController::class, 'indexNotices'])->name('account.notices.index');
    Route::get('/unread-notices', [MemberController::class, 'indexUnreadNotices'])->name('account.unread_notices.index');
    Route::get('/cart', [MemberController::class, 'showCart'])->name('account.cart.show');
    Route::post('/cart', [MemberController::class, 'storeCart'])->name('account.cart.store');
    Route::post('/cart/update', [MemberController::class, 'updateCart'])->name('account.cart.update');
    Route::post('/cart/remove', [MemberController::class, 'removeCart'])->name('account.cart.remove');
    Route::post('/cart/payment-method-choice', [MemberController::class, 'cartPaymentMethodChoice'])->name('account.cart.payment_method_choice');
    Route::post('/cart/delivery-method-choice', [MemberController::class, 'cartDeliveryMethodChoice'])->name('account.cart.delivery_method_choice');
    Route::post('/cart/check', [MemberController::class, 'cartCheck'])->name('account.cart.check');
    Route::post('/cart/confirm', [MemberController::class, 'cartConfirm'])->name('account.cart.confirm');

    Route::post('/cart/merge-shipping-request', [MemberController::class, 'createMergeShippingRequest'])->name('account.cart.merge_shipping_request');
    Route::get('/cart/merge-shipping-delivery-method/{requestId}', [MemberController::class, 'mergeShippingDeliveryMethodEdit'])->name('account.cart.merge_shipping.delivery_method.edit');
    Route::post('/cart/merge-shipping-delivery/{requestId}', [MemberController::class, 'mergeShippingDeliveryUpdate'])->name('account.cart.merge_shipping_delivery.update');
    Route::post('/cart/merge-shipping-check/{requestId}', [MemberController::class, 'mergeShippingCheck'])->name('account.cart.merge_shipping.check');
    Route::post('/cart/merge-shipping-confirm/{requestId}', [MemberController::class, 'mergeShippingConfirm'])->name('account.cart.merge_shipping.confirm');
    Route::post('/cart/merge-shipping-request/{requestId}/remove', [MemberController::class, 'removeMergeShippingRequest'])->name('account.cart.merge_shipping_request.remove');
    Route::post('/products/{lotId}/confirm', [MemberController::class, 'confirmProduct'])->name('account.products.confirm');
});

Route::prefix('account')->middleware(['auth', LotOwnership::class])->group(function () {
    Route::get('/applications/{lotId}', [MemberController::class, 'editLot'])->name('account.applications.edit');
    Route::post('/applications/{lotId}', [MemberController::class, 'updateLot'])->name('account.applications.update');
    Route::get('/applications/{lotId}/logistic-info', [MemberController::class, 'createApplicationLogisticInfo'])->name('account.application_logistic_info.create');
    Route::post('/applications/{lotId}/application-logistic-info/store', [MemberController::class, 'storeApplicationLogisticInfo'])->name('account.application_logistic_info.store');
    Route::get('/unsold-lots/{lotId}', [MemberController::class, 'editUnsoldLot'])->name('account.unsold_lots.edit');
    Route::post('/unsold-lots/{lotId}', [MemberController::class, 'handleUnsoldLot'])->name('account.unsold_lots.handle');
    Route::get('/returned-lots/{lotId}', [MemberController::class, 'editReturnedLot'])->name('account.returned_lots.edit');
    Route::post('/returned-lots/{lotId}', [MemberController::class, 'updateReturnedLot'])->name('account.returned_lots.update');
});

Route::prefix('account')->middleware(['auth', OrderOwnership::class])->group(function () {
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
    Route::get('/orders/{orderId}/credit-card-info/check', [MartController::class, 'creditCardInfoCheck'])->name('account.credit_card_info.check');
});

Route::prefix('account')->middleware(['auth', ChatOwnership::class])->group(function () {
    Route::get('/orders/{orderId}/chatroom', [MartController::class, 'indexMessages'])->name('mart.chatroom.show');
});
Route::post('/orders/{orderId}/messages', [MartController::class, 'sendMessage'])->name('mart.messages.send');

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

Route::get('/about-antiquary', [MartController::class, 'showAbout'])->name('mart.about_antiquary.show');
Route::get('/antiquary-guaranty', [MartController::class, 'showGuaranty'])->name('mart.antiquary_guaranty.show');
Route::get('/consignment-auction-notes', [MartController::class, 'showConsignmentAuctionNotes'])->name('mart.consignment_auction_notes.show');
Route::get('/consignment-auction-terms', [MartController::class, 'showConsignmentAuctionTerms'])->name('mart.consignment_auction_terms.show');
Route::get('/bidding-notes', [MartController::class, 'showBiddingNotes'])->name('mart.bidding-notes.show');
Route::get('/privacy-policy', [MartController::class, 'showPrivacyPolicy'])->name('mart.privacy-policy.show');
Route::get('/terms', [MartController::class, 'showTerms'])->name('mart.terms.show');
Route::get('/bidding-rules', [MartController::class, 'showBiddingRules'])->name('mart.bidding-rules.show');
Route::get('/return-and-exchange-policy', [MartController::class, 'showReturnAndExchangePolicy'])->name('mart.return-and-exchange-policy.show');

