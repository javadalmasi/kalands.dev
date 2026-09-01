<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AffiliateRedirectController;
use App\Http\Controllers\Auth\ForgetPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\TwoFAController as AuthTwoFAController;
use App\Http\Controllers\AutocompleteController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommentVoteController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\AdminSearchController;
use App\Http\Controllers\Dashboard\IndexNowController;
use App\Http\Controllers\Dashboard\UserTicketController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QueueProcessController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TwoFAController;
use App\Http\Controllers\VisitorInfoController;
use Illuminate\Support\Facades\Route;

Route::get('/api/info', [VisitorInfoController::class, 'index'])->name('visitor.info');
Route::get('/api/services/autocomplete/', [AutocompleteController::class, 'search'])->name('autocomplete.search');
Route::get('/api/queue/process', [QueueProcessController::class, 'process'])->name('api.queue.process');
Route::get('/go/{slug}', [AffiliateRedirectController::class, 'redirect'])->name('affiliate.go');
Route::get('/api/bslm/{productId}', [AffiliateRedirectController::class, 'fetchAndRedirect'])->name('affiliate.redirect');

Route::get('/', [HomeController::class, 'home'])->name('index');

// Dynamic XML sitemap (Yoast-style: index + per-shard product sitemaps).
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/product-sitemap{shard}.xml', [SitemapController::class, 'shard'])
    ->where('shard', '[1-9][0-9]*')
    ->name('sitemap.shard');

Route::get('/test-error/{code}', function ($code) {
    abort($code);
});

Route::get('/{key}.txt', function (string $key) {
    $path = public_path("{$key}.txt");
    if (! file_exists($path)) {
        abort(404);
    }

    return response()->file($path, ['Content-Type' => 'text/plain']);
})->where('key', '[a-z0-9]{32}');

Route::controller(ResultController::class)->group(function () {
    Route::get('api/result/infinite', 'infinite')->name('result.infinite');
    Route::get('product/brand/{brand_name}', 'brand');
    Route::get('result', 'query');
    Route::get('result/{category}', 'category');
    Route::get('result/{category}/{brand}', 'category_brand');
    Route::get('main/{category}', 'main_category');
    Route::get('seller/{seller_id}', 'seller');
});

Route::controller(ProductController::class)->prefix('product')->group(function () {
    Route::get('brand/{BrandName}', 'BrandParser');
    Route::get('{ProductID}', 'ProductRedirect');
    Route::get('{ProductID}/{ProductName}', 'ProductParser');
});

Route::controller(ContactController::class)->prefix('contact')->name('contact.')->group(function () {
    Route::get('/', 'show')->name('show');
    Route::post('/', 'store')->name('store');
});
Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');

Route::prefix('auth')
    ->name('auth.')
    ->middleware('guest:web,admin')
    ->group(function () {
        Route::get('/login', [LoginController::class, 'show'])->name('login');
        Route::post('/login', [LoginController::class, 'verify'])->name('login.verify');

        Route::get('/register/step-1', [RegisterController::class, 'showStep1'])->name('register.step1');
        Route::post('/register/step-1', [RegisterController::class, 'storeStep1'])->name('register.step1.store');
        Route::get('/register/step-2', [RegisterController::class, 'showStep2'])->name('register.step2');
        Route::post('/register/step-2', [RegisterController::class, 'storeStep2'])->name('register.step2.store');
        Route::post('/register/back-to-step-1', [RegisterController::class, 'backToStep1'])->name('register.back');

        Route::get('/forget', [ForgetPasswordController::class, 'show'])->name('forget');
        Route::post('/forget', [ForgetPasswordController::class, 'sendCode'])->name('forget.send');
    });

Route::get('auth/2fa', [AuthTwoFAController::class, 'show'])
    ->middleware(['auth:web,admin'])
    ->name('auth.2fa');
Route::post('auth/2fa', [AuthTwoFAController::class, 'verify'])
    ->middleware(['auth:web,admin'])
    ->name('auth.2fa.verify');
Route::post('auth/logout', [LoginController::class, 'logout'])->name('auth.logout');

Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');

Route::middleware(['auth:web', 'verified'])->group(function () {
    Route::post('/bookmark', [BookmarkController::class, 'store'])->name('bookmarks.store');
    Route::post('/like', [LikeController::class, 'store'])->name('likes.store');
    Route::post('/comments/{comment}/vote', [CommentVoteController::class, 'store'])->name('comments.vote');
});

Route::prefix('dash/user/{authkey}')
    ->name('dash.user.')
    ->middleware(['auth:web', 'dash.authkey:web', '2fa', 'verified'])
    ->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::get('/settings', [ProfileController::class, 'edit'])->name('settings');
        Route::post('/settings', [ProfileController::class, 'update'])->name('settings.update');
        Route::post('/settings/password', [ProfileController::class, 'updatePassword'])->name('settings.password');

        Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks');
        Route::post('/bookmarks/categories', [BookmarkController::class, 'storeCategory'])->name('bookmarks.categories.store');
        Route::patch('/bookmarks/{bookmark}/category', [BookmarkController::class, 'updateCategory'])->name('bookmarks.category.update');
        Route::get('/bookmarks/{bookmark}/delete', [BookmarkController::class, 'delete'])->name('bookmarks.delete');
        Route::get('/likes', [LikeController::class, 'index'])->name('likes');
        Route::get('/likes/{like}/delete', [LikeController::class, 'delete'])->name('likes.delete');

        Route::get('/comments', [CommentController::class, 'index'])->name('comments');
        Route::get('/comments/{comment}', [CommentController::class, 'show'])->name('comments.show');
        Route::patch('/comments', [CommentController::class, 'update'])->name('comments.update');

        Route::get('/tickets', [UserTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/create', [UserTicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [UserTicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{ticket}', [UserTicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/reply', [UserTicketController::class, 'reply'])->name('tickets.reply');
        Route::post('/tickets/{ticket}/close', [UserTicketController::class, 'close'])->name('tickets.close');

        Route::get('/2fa', [TwoFAController::class, 'show'])->name('2fa.show');
        Route::post('/2fa/enable', [TwoFAController::class, 'enable'])->name('2fa.enable');
        Route::post('/2fa/disable', [TwoFAController::class, 'disable'])->name('2fa.disable');
    });

Route::prefix('dash/admin/{authkey}')
    ->name('dash.admin.')
    ->middleware(['auth:admin', 'dash.authkey:admin', '2fa'])
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('index')->middleware('permission:dashboard.view');
        Route::get('/modules', [AdminDashboardController::class, 'modules'])->name('modules')->middleware('permission:dashboard.view');
        Route::get('/modules/{moduleKey}', [AdminDashboardController::class, 'moduleSettings'])
            ->whereIn('moduleKey', ['email_settings', 'sms_settings', 'contact', 'affiliate', 'file_manager', 'home_items_management', 'email_templates', 'queues', 'comments', 'tickets', 'faq', 'geoip', 'robots', 'search', 'megamenu', 'error_pages', 'cache_management', 'object_cache', 'artisan_commands', 'visitor_intelligence', 'categories', 'sitemap', 'indexnow'])
            ->name('modules.show');
        Route::post('/modules/home-slider', [AdminDashboardController::class, 'saveHomeSliderSettings'])->name('modules.home-slider.save')->middleware('permission:home_items.edit');
        Route::post('/modules/home-banners-categories', [AdminDashboardController::class, 'saveHomeBannerCategorySettings'])->name('modules.home-banners-categories.save')->middleware('permission:home_items.edit');
        Route::get('/modules/file-manager/explore', [AdminDashboardController::class, 'fileManagerExplore'])->name('modules.explore')->middleware('permission:file_manager.view');
        Route::post('/modules/file-manager/explore/upload', [AdminDashboardController::class, 'fileManagerUpload'])->name('modules.explore.upload')->middleware('permission:file_manager.full');
        Route::post('/modules/file-manager/explore/create-folder', [AdminDashboardController::class, 'fileManagerCreateFolder'])->name('modules.explore.create-folder')->middleware('permission:file_manager.full');
        Route::post('/modules/file-manager/explore/delete', [AdminDashboardController::class, 'fileManagerDelete'])->name('modules.explore.delete')->middleware('permission:file_manager.full');
        Route::post('/modules/file-manager/explore/rename', [AdminDashboardController::class, 'fileManagerRename'])->name('modules.explore.rename')->middleware('permission:file_manager.full');
        Route::post('/modules/file-manager/explore/move', [AdminDashboardController::class, 'fileManagerMove'])->name('modules.explore.move')->middleware('permission:file_manager.full');
        Route::post('/modules/file-manager/explore/copy', [AdminDashboardController::class, 'fileManagerCopy'])->name('modules.explore.copy')->middleware('permission:file_manager.full');
        Route::post('/modules/file-manager/settings/save', [AdminDashboardController::class, 'fileManagerSettingsSave'])->name('file-manager.settings.save')->middleware('permission:file_manager.full');

        Route::get('/users', [AdminDashboardController::class, 'users'])->name('users')->middleware('permission:users.view');
        Route::post('/users/bulk', [AdminDashboardController::class, 'bulkUserAction'])->name('users.bulk')->middleware('permission:users.edit');
        Route::get('/users/create', [AdminDashboardController::class, 'createUser'])->name('users.create')->middleware('permission:users.create');
        Route::post('/users/create', [AdminDashboardController::class, 'storeUser'])->name('users.store')->middleware('permission:users.create');
        Route::post('/users/{user}', [AdminDashboardController::class, 'updateUser'])->name('users.update')->middleware('permission:users.edit');
        Route::post('/users/{user}/password', [AdminDashboardController::class, 'updateUserPassword'])->name('users.password')->middleware('permission:users.edit');
        Route::delete('/users/{user}', [AdminDashboardController::class, 'deleteUser'])->name('users.delete')->middleware('permission:users.delete');

        Route::get('/admins', [AdminDashboardController::class, 'admins'])->name('admins')->middleware('permission:admins.view');
        Route::post('/admins', [AdminDashboardController::class, 'storeAdmin'])->name('admins.store')->middleware('permission:admins.create');
        Route::post('/admins/{admin}', [AdminDashboardController::class, 'updateAdmin'])->name('admins.update')->middleware('permission:admins.edit');
        Route::delete('/admins/{admin}', [AdminDashboardController::class, 'deleteAdmin'])->name('admins.delete')->middleware('permission:admins.delete');

        Route::get('/roles', [AdminDashboardController::class, 'roles'])->name('roles')->middleware('permission:roles.view');
        Route::get('/roles/create', [AdminDashboardController::class, 'createRole'])->name('roles.create')->middleware('permission:roles.create');
        Route::post('/roles/create', [AdminDashboardController::class, 'storeRole'])->name('roles.store')->middleware('permission:roles.create');
        Route::get('/roles/{role}/edit', [AdminDashboardController::class, 'editRole'])->name('roles.edit')->middleware('permission:roles.edit');
        Route::post('/roles/{role}/edit', [AdminDashboardController::class, 'updateRole'])->name('roles.update')->middleware('permission:roles.edit');
        Route::delete('/roles/{role}', [AdminDashboardController::class, 'deleteRole'])->name('roles.delete')->middleware('permission:roles.delete');

        Route::get('/queues', [AdminDashboardController::class, 'queues'])->name('queues')->middleware('permission:queues.view');
        Route::post('/queues', [AdminDashboardController::class, 'saveQueueSettings'])->name('queues.save')->middleware('permission:queues.full');
        Route::post('/queues/regenerate-token', [AdminDashboardController::class, 'regenerateQueueToken'])->name('queues.token.regenerate')->middleware('permission:queues.full');

        // ── Email Settings ─────────────────────────────────────────────────────────
        Route::post('/mail-config', [AdminDashboardController::class, 'saveMailConfig'])->name('mail.config.save')->middleware('permission:communication.edit');
        Route::post('/mail-config/test', [AdminDashboardController::class, 'sendMailTest'])->name('mail.config.test')->middleware('permission:communication.full');

        // ── SMS Settings ───────────────────────────────────────────────────────────
        Route::post('/sms-config', [AdminDashboardController::class, 'saveSmsConfig'])->name('sms.config.save')->middleware('permission:communication.edit');
        Route::post('/sms-config/test', [AdminDashboardController::class, 'sendSmsTest'])->name('sms.config.test')->middleware('permission:communication.full');
        Route::get('/email-templates', [AdminDashboardController::class, 'emailTemplates'])->name('email.templates')->middleware('permission:email_templates.view');
        Route::post('/email-templates/layout', [AdminDashboardController::class, 'saveEmailTemplateLayout'])->name('email.templates.layout.save')->middleware('permission:email_templates.edit');
        Route::post('/email-templates/{key}', [AdminDashboardController::class, 'saveEmailTemplate'])->name('email.templates.save')->middleware('permission:email_templates.edit');
        Route::get('/email-templates/{key}/preview', [AdminDashboardController::class, 'previewEmailTemplate'])->name('email.templates.preview')->middleware('permission:email_templates.view');

        Route::get('/comments', [AdminDashboardController::class, 'comments'])->name('comments')->middleware('permission:comments.view');
        Route::post('/comments/settings', [AdminDashboardController::class, 'saveCommentSettings'])->name('comments.settings.save')->middleware('permission:comments.full');
        Route::post('/comments/bulk', [AdminDashboardController::class, 'bulkCommentAction'])->name('comments.bulk')->middleware('permission:comments.edit');
        Route::post('/comments/{comment}/status/{status}', [AdminDashboardController::class, 'setCommentStatus'])->name('comments.status')->middleware('permission:comments.edit');
        Route::delete('/comments/{comment}', [AdminDashboardController::class, 'deleteComment'])->name('comments.delete')->middleware('permission:comments.delete');

        Route::get('/tickets', [AdminDashboardController::class, 'ticketsHub'])->name('tickets')->middleware('permission:tickets.view');
        Route::post('/tickets/bulk', [AdminDashboardController::class, 'bulkTicketAction'])->name('tickets.bulk')->middleware('permission:tickets.edit');
        Route::get('/tickets/{ticket}', [AdminDashboardController::class, 'showTicket'])->name('tickets.show')->middleware('permission:tickets.view');
        Route::post('/tickets/{ticket}/reply', [AdminDashboardController::class, 'replyTicket'])->name('tickets.reply')->middleware('permission:tickets.edit');
        Route::post('/tickets/{ticket}/status', [AdminDashboardController::class, 'updateTicketStatus'])->name('tickets.status')->middleware('permission:tickets.edit');
        Route::post('/tickets/settings', [AdminDashboardController::class, 'saveTicketSettings'])->name('tickets.settings.save')->middleware('permission:tickets.full');
        Route::post('/tickets/user-block', [AdminDashboardController::class, 'toggleTicketUserBlock'])->name('tickets.user.toggle-block')->middleware('permission:tickets.full');

        Route::get('/ticket-categories', [AdminDashboardController::class, 'ticketsHub'])->name('ticket.categories')->middleware('permission:tickets.full');
        Route::post('/ticket-categories', [AdminDashboardController::class, 'storeTicketCategory'])->name('ticket.categories.store')->middleware('permission:tickets.full');
        Route::post('/ticket-categories/{category}/toggle', [AdminDashboardController::class, 'toggleTicketCategory'])->name('ticket.categories.toggle')->middleware('permission:tickets.full');

        Route::get('/contact-us', [AdminDashboardController::class, 'contactMessages'])->name('contact.messages')->middleware('permission:contact.view');
        Route::post('/contact-us/bulk', [AdminDashboardController::class, 'bulkContactAction'])->name('contact.bulk')->middleware('permission:contact.edit');
        Route::get('/contact-us/{contactMessage}/edit', [AdminDashboardController::class, 'editContactMessage'])->name('contact.edit')->middleware('permission:contact.edit');
        Route::post('/contact-us/{contactMessage}', [AdminDashboardController::class, 'updateContactMessage'])->name('contact.update')->middleware('permission:contact.edit');
        Route::post('/contact-us/{contactMessage}/read', [AdminDashboardController::class, 'markContactRead'])->name('contact.read')->middleware('permission:contact.edit');
        Route::delete('/contact-us/{contactMessage}', [AdminDashboardController::class, 'deleteContact'])->name('contact.delete')->middleware('permission:contact.delete');
        Route::get('/contact-page-info', [AdminDashboardController::class, 'contactPageInfo'])->name('contact.page.info')->middleware('permission:contact.full');
        Route::post('/contact-page-info', [AdminDashboardController::class, 'saveContactPageInfo'])->name('contact.page.info.save')->middleware('permission:contact.full');
        Route::post('/faq', [AdminDashboardController::class, 'storeFaq'])->name('faq.store')->middleware('permission:faq.create');
        Route::post('/faq/reorder', [AdminDashboardController::class, 'reorderFaq'])->name('faq.reorder')->middleware('permission:faq.edit');
        Route::post('/faq/bulk', [AdminDashboardController::class, 'bulkFaqAction'])->name('faq.bulk')->middleware('permission:faq.edit');
        Route::post('/faq/{faq}', [AdminDashboardController::class, 'updateFaq'])->name('faq.update')->middleware('permission:faq.edit');
        Route::post('/faq/{faq}/toggle', [AdminDashboardController::class, 'toggleFaq'])->name('faq.toggle')->middleware('permission:faq.edit');
        Route::delete('/faq/{faq}', [AdminDashboardController::class, 'deleteFaq'])->name('faq.delete')->middleware('permission:faq.delete');

        Route::get('/affiliate-settings', [AdminDashboardController::class, 'affiliateSettings'])->name('affiliate.settings')->middleware('permission:affiliate.view');
        Route::post('/affiliate-settings', [AdminDashboardController::class, 'saveAffiliateSettings'])->name('affiliate.settings.save')->middleware('permission:affiliate.edit');
        Route::get('/affiliate/export/{type}', [AdminDashboardController::class, 'exportAffiliateSettings'])->name('affiliate.export')->middleware('permission:affiliate.full');
        Route::post('/affiliate/import/{type}', [AdminDashboardController::class, 'importAffiliateSettings'])->name('affiliate.import')->middleware('permission:affiliate.full');
        Route::delete('/affiliate/links/{link}', [AdminDashboardController::class, 'deleteAffiliateLink'])->name('affiliate.links.delete')->middleware('permission:affiliate.delete');
        Route::post('/affiliate/links/{link}/toggle', [AdminDashboardController::class, 'toggleAffiliateLinkStatus'])->name('affiliate.links.toggle')->middleware('permission:affiliate.edit');
        Route::get('/affiliate/links-export', [AdminDashboardController::class, 'exportAffiliateLinks'])->name('affiliate.links.export')->middleware('permission:affiliate.full');
        Route::post('/affiliate/links-import', [AdminDashboardController::class, 'importAffiliateLinks'])->name('affiliate.links.import')->middleware('permission:affiliate.full');

        Route::post('/modules/geoip/update', [AdminDashboardController::class, 'updateGeoIp'])->name('geoip.update')->middleware('permission:geoip.full');

        Route::post('/modules/robots/save', [AdminDashboardController::class, 'saveRobots'])->name('robots.save')->middleware('permission:robots.edit');
        Route::post('/modules/robots/test', [AdminDashboardController::class, 'testRobots'])->name('robots.test')->middleware('permission:robots.view');

        Route::get('/search', [AdminSearchController::class, 'search'])->name('search.ajax')->middleware('permission:search.view');
        Route::post('/search/settings', [AdminSearchController::class, 'saveSettings'])->name('search.settings.save')->middleware('permission:search.edit');

        Route::get('/products', [AdminDashboardController::class, 'products'])->name('products')->middleware('permission:products.view');
        Route::get('/products/checker', [AdminDashboardController::class, 'productsChecker'])->name('products.checker')->middleware('permission:products.check');
        Route::post('/products/bulk', [AdminDashboardController::class, 'bulkProductAction'])->name('products.bulk')->middleware('permission:products.edit');
        Route::post('/products/{product}/toggle', [AdminDashboardController::class, 'toggleProductStatus'])->name('products.toggle')->middleware('permission:products.edit');
        Route::get('/products/digikala-ids', [AdminDashboardController::class, 'getDigikalaIdsForCheck'])->name('products.digikala_ids')->middleware('permission:products.check');
        Route::post('/products/check-api', [AdminDashboardController::class, 'checkProductsApiStatus'])->name('products.check_api')->middleware('permission:products.check');

        Route::get('/product-mappings', [AdminDashboardController::class, 'productIdMappings'])->name('product_mappings')->middleware('permission:products.view');
        Route::post('/product-mappings', [AdminDashboardController::class, 'storeProductIdMapping'])->name('product_mappings.store')->middleware('permission:products.edit');
        Route::delete('/product-mappings/{mapping}', [AdminDashboardController::class, 'deleteProductIdMapping'])->name('product_mappings.delete')->middleware('permission:products.edit');

        Route::get('/megamenu', [AdminDashboardController::class, 'megamenuHub'])->name('megamenu')->middleware('permission:megamenu.view');
        Route::post('/megamenu/save', [AdminDashboardController::class, 'saveMegamenu'])->name('megamenu.save')->middleware('permission:megamenu.edit');
        Route::post('/megamenu/test-links', [AdminDashboardController::class, 'testMegamenuLinks'])->name('megamenu.test_links')->middleware('permission:megamenu.view');

        Route::get('/error-pages', [AdminDashboardController::class, 'errorPagesHub'])->name('error_pages')->middleware('permission:error_pages.view');
        Route::post('/error-pages/save', [AdminDashboardController::class, 'saveErrorPagesSettings'])->name('error_pages.save')->middleware('permission:error_pages.edit');

        Route::post('/object-cache/settings', [AdminDashboardController::class, 'saveObjectCacheSettings'])->name('object-cache.settings.save')->middleware('permission:object_cache.edit');
        Route::post('/object-cache/test', [AdminDashboardController::class, 'testObjectCacheConnection'])->name('object-cache.test')->middleware('permission:object_cache.view');
        Route::post('/object-cache/purge', [AdminDashboardController::class, 'purgeObjectCache'])->name('object-cache.purge')->middleware('permission:object_cache.full');
        Route::post('/object-cache/item-delete', [AdminDashboardController::class, 'deleteObjectCacheItem'])->name('object-cache.item.delete')->middleware('permission:object_cache.full');

        Route::post('/cache-management/settings', [AdminDashboardController::class, 'saveCacheManagementSettings'])->name('cache-management.settings.save')->middleware('permission:cache_management.edit');
        Route::post('/cache-management/htaccess', [AdminDashboardController::class, 'saveHtaccessSettings'])->name('cache-management.htaccess.save')->middleware('permission:cache_management.edit');
        Route::get('/cache-management/htaccess-backup', [AdminDashboardController::class, 'downloadHtaccessBackup'])->name('cache-management.htaccess.backup')->middleware('permission:cache_management.edit');

        Route::post('/artisan-commands/execute', [AdminDashboardController::class, 'executeArtisanCommand'])->name('artisan-commands.execute')->middleware('permission:dashboard.view');
        Route::post('/artisan-commands/verify-password', [AdminDashboardController::class, 'verifyArtisanPassword'])->name('artisan-commands.verify-password')->middleware('permission:dashboard.view');
        Route::post('/visitor-intelligence/save', [AdminDashboardController::class, 'saveVisitorIntelligence'])->name('visitor-intelligence.save')->middleware('permission:geoip.full');
        Route::post('/visitor-intelligence/test', [AdminDashboardController::class, 'testUserAgent'])->name('visitor-intelligence.test')->middleware('permission:geoip.full');
        Route::post('/visitor-intelligence/asn-fetch', [AdminDashboardController::class, 'fetchAsnData'])->name('visitor-intelligence.asn_fetch')->middleware('permission:geoip.full');
        Route::post('/visitor-intelligence/asn-bulk-update', [AdminDashboardController::class, 'bulkUpdateAsnData'])->name('visitor-intelligence.asn_bulk_update')->middleware('permission:geoip.full');

        Route::get('/categories/tree', [AdminDashboardController::class, 'getCategoryTree'])->name('categories.tree');
        Route::get('/categories/{category}/details', [AdminDashboardController::class, 'getCategoryDetails'])->name('categories.details');
        Route::post('/categories/map', [AdminDashboardController::class, 'saveCategoryMapping'])->name('categories.map');
        Route::post('/categories/{category}/update', [AdminDashboardController::class, 'updateCategory'])->name('categories.update');
        Route::post('/categories/settings', [AdminDashboardController::class, 'saveCategorySettings'])->name('categories.settings.save');
        Route::post('/categories/import-snapp', [AdminDashboardController::class, 'importSnappShopCategories'])->name('categories.import.snapp');
        Route::post('/categories/ai-test', [AdminDashboardController::class, 'testAiEmbedding'])->name('categories.ai_test');
        Route::post('/categories/sync-all', [AdminDashboardController::class, 'syncAllCategories'])->name('categories.sync_all');
        Route::get('/categories/linked', [AdminDashboardController::class, 'getLinkedCategories'])->name('categories.linked');

        Route::get('/modules/sitemap/status', [AdminDashboardController::class, 'sitemapStatus'])->name('sitemap.status')->middleware('permission:sitemap.view');
        Route::post('/modules/sitemap/settings', [AdminDashboardController::class, 'saveSitemapSettings'])->name('sitemap.settings')->middleware('permission:sitemap.view');
        Route::post('/modules/sitemap/rebuild', [AdminDashboardController::class, 'startSitemapRebuild'])->name('sitemap.rebuild')->middleware('permission:sitemap.view');
        Route::post('/modules/sitemap/stop', [AdminDashboardController::class, 'stopSitemap'])->name('sitemap.stop')->middleware('permission:sitemap.view');
        Route::post('/modules/sitemap/reset', [AdminDashboardController::class, 'resetSitemap'])->name('sitemap.reset')->middleware('permission:sitemap.view');
        Route::post('/modules/sitemap/clean-logs', [AdminDashboardController::class, 'cleanSitemapLogs'])->name('sitemap.clean-logs')->middleware('permission:sitemap.view');

        Route::post('/modules/indexnow/settings', [IndexNowController::class, 'saveSettings'])->name('indexnow.settings.save')->middleware('permission:indexnow.edit');
        Route::post('/modules/indexnow/regenerate-key', [IndexNowController::class, 'regenerateKey'])->name('indexnow.key.regenerate')->middleware('permission:indexnow.edit');
    });

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth:admin', '2fa'])
    ->controller(AdminController::class)
    ->group(function () {
        Route::get('/comments', 'comments')->name('comments.index');
        Route::get('/comments/{comment}/accept', 'acceptComment')->name('comments.accept');
        Route::get('/comments/{comment}/reject', 'rejectComment')->name('comments.reject');
        Route::get('/comments/{comment}/spam', 'spamComment')->name('comments.spam');
        Route::delete('/comments/{comment}/delete', 'deleteComment')->name('comments.delete');
    });
