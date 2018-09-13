<?php

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


$languages = array('ar', 'en', 'fr');
$defaultLanguage = 'ar';
if ($defaultLanguage) {
    $defaultLanguageCode = $defaultLanguage;
} else {
    $defaultLanguageCode = 'ar';
}

$currentLanguageCode = Request::segment(1, $defaultLanguageCode);
if (in_array($currentLanguageCode, $languages)) {
    Route::get('/', function () use($defaultLanguageCode) {
        return redirect()->to($defaultLanguageCode);
    });

 
    Route::group(['namespace' => 'Front', 'prefix' => $currentLanguageCode], function () use($currentLanguageCode) {
        app()->setLocale($currentLanguageCode);
        Route::get('/', 'HomeController@index')->name('home');
        Route::get('getRegionByCity/{id}', 'AjaxController@getRegionByCity');
        Route::get('getAddress/{id}', 'AjaxController@getAddress');
        Route::post('location-suggestions', 'AjaxController@search');
        Route::post('change-location', 'AjaxController@changeLocation');
        Route::get('meal-choices', 'AjaxController@getMealOrSizeChoices');
        Auth::routes();

        Route::get('user-activation-code', 'Auth\RegisterController@showActivationForm')->name('activation');
        Route::post('activateuser', 'Auth\RegisterController@activate_user')->name('activationuser');

        Route::get('edit-user-phone', 'Auth\RegisterController@showEditMobileForm')->name('edit-phone');
        Route::post('edituserphone', 'Auth\RegisterController@EditPhone')->name('editphone');

        Route::get('login/facebook', 'Auth\RegisterController@redirectToProvider')->name('login/facebook');
        Route::get('login/facebook/callback', 'Auth\RegisterController@handleProviderCallback');
        
        Route::get('complete-registeration', 'Auth\RegisterController@showCompleteRegistrationForm')->name('complete_register');
        

        Route::get('about-us', 'StaticController@about_us')->name('about_us');
        Route::get('usage-and-conditions', 'StaticController@usage_coditions')->name('usage_conditions');
        Route::get('terms-and-conditions', 'StaticController@terms_conditions')->name('terms_conditions');
        Route::get('contact-us', 'StaticController@contact_us')->name('contact_us');
        Route::get('offers', 'StaticController@offers')->name('offers');
        Route::post('contact_us', 'StaticController@sendContactMessage')->name('contact');
       
        Route::get('cart', 'CartController@index')->name('showcart');
        Route::get('cart/empty', 'CartController@emptyCart');
        Route::post('cart', 'CartController@store');
        Route::get('resend_order', 'CartController@resendOrder')->name('resend_order');
        Route::get('cart/{index}/remove', 'CartController@remove');
        Route::get('cart/update-quantity', 'CartController@update_quantity');
        Route::get('cart/coupon-check', 'CartController@coupon_check');
        Route::post('cart/new-order', 'CartController@new_order');
        Route::get('resturantes', 'ResturantesController@index')->name('show_resturantes');
        Route::get('near_me', 'ResturantesController@getMyLocation')->name('near_me');
        
        Route::get('resturant/{resturant}', 'ResturantesController@resturant');
        Route::get('resturant/{resturant}/info', 'ResturantesController@resturant_info')->name('resturant_info');
        Route::get('resturant/{resturant}/{menu_section}', 'ResturantesController@menu');
        Route::get('resturant/{resturant}/{menu_section}/{meal}', 'ResturantesController@meal');
        Route::get('resturantes/cuisines/{cuisine}', 'ResturantesController@getResturantesByCuisine');
        Route::post('resturantes/suggest', 'ResturantesController@suggest');
        /*************************** user ***************/
        Route::get('user-profile','UsersController@profile')->name('profile');
        Route::get('edit-user-profile','UsersController@editProfile')->name('edit_profile');
        Route::post('update-user','UsersController@updateProfile')->name('update_user');
        Route::get('add-favourite','UsersController@addDeleteFavourite')->name('add-favourite');
        Route::get('user-favourites','UsersController@favourites')->name('user-favourites');

        /*************************** addresses ************/
        Route::resource('user-addresses', 'AddressesController');
        
        Route::get('delete-addresses/{id}', 'AddressesController@destroy')->name('delete-address');

        /********************** orders *****************/
        Route::resource('user-orders', 'OrdersController');
        Route::post('rate-order', 'OrdersController@rate')->name('rate_order');
        Route::get('order-meal/update-quantity', 'OrdersController@updateOrderMealQuantity');
        Route::get('order-meal/remove', 'OrdersController@removeOrderMeal');
        
        

    });
} else {
    Route::get('/' . $currentLanguageCode, function () use($defaultLanguageCode) {
        return redirect()->to($defaultLanguageCode);
    });
}


//Route::group(['middleware'=>'auth:admin'], function () {
Route::group(['namespace' => 'Admin', 'prefix' => 'admin'], function () {
    Route::get('/', 'AdminController@index')->name('admin.dashboard');
    Route::get('/error', 'AdminController@error')->name('admin.error');
    Route::get('/change_lang', 'AjaxController@change_lang')->name('ajax.change_lang');
    Route::get('/getRegionByCity/{id}', 'AjaxController@getRegionByCity')->name('ajax.getRegionByCity');
    Route::get('/getResturantBranches/{id}', 'AjaxController@getResturantBranches')->name('ajax.getResturantBranches');
    Route::get('getMenueSectionsByResturant/{id}', 'AjaxController@getMenueSectionsByResturant');
    Route::post('notify', 'AjaxController@notify');
    Route::post('send_email', 'AjaxController@send_email');
    Route::get('getToppings', 'AjaxController@getToppings');
    Route::get('getSizes', 'AjaxController@getSizes');

     Route::get('profile', 'ProfileController@index');
    Route::patch('profile', 'ProfileController@update');



    Route::resource('groups', 'GroupsController');
    Route::resource('admins', 'AdminsController');
    Route::resource('users', 'UsersController');
    Route::resource('categories', 'CategoriesController');
    Route::resource('cities', 'CitiesController');
    Route::resource('sizes', 'SizesController');
    Route::resource('offers', 'OffersController');
    Route::resource('cuisines', 'CuisinesController');
    Route::resource('coupons', 'CouponsController');
    Route::resource('toppings', 'ToppingsController');
    Route::resource('choices', 'ChoicesController');
    Route::resource('sub_choices', 'SubChoicesController');
    Route::resource('meal_sizes', 'MealSizesController');
    Route::resource('resturantes', 'ResturantesController');
    Route::resource('resturant_branches', 'ResturantBranchesController');
    Route::resource('menu_sections', 'MenuSectionsController');
    Route::resource('slider', 'SliderController');
    Route::delete('menu_sections/{id}/toppings', 'MenuSectionsController@destroy_topping');
    Route::resource('meals', 'MealsController');
    Route::delete('meals/{id}/sizes', 'MealsController@destroy_size');
    Route::delete('meals/{id}/toppings', 'MealsController@destroy_topping');
    Route::resource('contact_messages', 'ContactMessagesController');
    Route::resource('recommendations', 'RecommendationsController');
    Route::resource('ads', 'AdsController');
    Route::resource('orders_reports', 'OrdersReportsController');
    Route::post('orders_reports/download', 'OrdersReportsController@download');
    Route::get('settings', 'SettingsController@index');
    Route::get('resturant_menu', 'ResturantMenuController@index');
    Route::get('resturant_meals', 'ResturantMealsController@index');
    Route::get('resturant_meals/{id}', 'ResturantMealsController@show');
    Route::get('resturant_meals/status/{id}', 'ResturantMealsController@status');
    Route::get('resturant_orders', 'ResturantOrdersController@index');
    Route::get('resturant_orders/{id}', 'ResturantOrdersController@show');
    Route::post('change_order_status', 'ResturantOrdersController@changeStatus')->name('order_status');





    Route::patch('settings/{id}', 'SettingsController@update');
    Route::get('notifications', 'NotificationsController@index');
    Route::post('notifications', 'NotificationsController@store');




    Route::post('groups/data', 'GroupsController@data');

    Route::post('admins/data', 'AdminsController@data');
    Route::post('users/data', 'UsersController@data');
    Route::get('users/status/{id}', 'UsersController@status');

    Route::post('contact_messages/data', 'ContactMessagesController@data');
    Route::post('ads/data', 'AdsController@data');
    Route::post('categories/data', 'CategoriesController@data');
    Route::post('cities/data', 'CitiesController@data');
    Route::post('sizes/data', 'SizesController@data');
    Route::post('toppings/data', 'ToppingsController@data');
    Route::post('choices/data', 'ChoicesController@data');
    Route::post('sub_choices/data', 'SubChoicesController@data');
    Route::post('cuisines/data', 'CuisinesController@data');
    Route::post('resturantes/data', 'ResturantesController@data');
    Route::post('resturant_branches/data', 'ResturantBranchesController@data');
    Route::post('menu_sections/data', 'MenuSectionsController@data');
    Route::post('resturant_menu/data', 'ResturantMenuController@data');
    Route::post('resturant_meals/data', 'ResturantMealsController@data');
    Route::post('resturant_orders/data', 'ResturantOrdersController@data');
    Route::post('meals/data', 'MealsController@data');
    Route::post('meal_sizes/data', 'MealSizesController@data');
    Route::post('offers/data', 'OffersController@data');
    Route::post('slider/data', 'SliderController@data');
    Route::post('recommendations/data', 'RecommendationsController@data');
    Route::post('coupons/data', 'CouponsController@data');



    $this->get('login', 'LoginController@showLoginForm')->name('admin.login');
    $this->post('login', 'LoginController@login')->name('admin.login.submit');
    $this->get('logout', 'LoginController@logout')->name('admin.logout');
});
//});

