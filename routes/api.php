<?php

use Illuminate\Http\Request;

/*
  |--------------------------------------------------------------------------
  | API Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register API routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | is assigned the "api" middleware group. Enjoy building your API!
  |
 */

Route::get('user', function (Request $request) {
    return _api_json(false, 'user');
})->middleware('jwt.auth');
Route::group(['namespace' => 'Api'], function () {
    


    Route::get('/token', 'BasicController@getToken');
    Route::post('/check_email', 'BasicController@check_email');
    Route::get('/settings', 'BasicController@getSettings');
    Route::post('/send_contact_message', 'BasicController@sendContactMessage');
    Route::post('/login', 'LoginController@login');
    Route::post('/register', 'RegisterController@register');
    Route::post('/resend_activation_code', 'RegisterController@resendActivationCode');
    Route::post('/password/reset', 'PasswordController@reset');
    Route::post('/password/verify', 'PasswordController@verify');
    Route::get('/search', 'SearchController@index');
    Route::get('/search', 'SearchController@index');
    
    Route::get('offers', 'BasicController@getOffers');
    Route::post('check_coupon', 'BasicController@checkCoupon');
    Route::get('getAds', 'BasicController@getAds');
    Route::post('get_my_loation','BasicController@getMyCityRegion');
   
    Route::get('cities', 'BasicController@getCities');
    Route::get('categories', 'BasicController@getCategories');
    Route::get('cuisines', 'BasicController@getCuisines');
    Route::post('getResturantes', 'ResturantesController@serchForResturantes');
    Route::post('getResturant', 'ResturantesController@show');
    Route::resource('meals','MealsController');


    Route::get('setting', 'BasicController@getSettings');

    Route::group(['middleware' => 'jwt.auth'], function () {

      Route::post('send_recommendation', 'BasicController@sendRecommendation');


        /* ----------------users------------------------ */
        Route::get('/user', 'UserController@show');
        Route::post('/user/update', 'UserController@update');
        Route::get('/logout', 'UserController@logout');
        Route::post('myorders', 'UserController@myOrders');
        

         /* ----------------Addresses------------------------ */
        Route::resource('addresses', 'AddressesController');
        
         /* ----------------favourites------------------------ */
        Route::get('meal_choices', 'MealsController@choices');
        Route::post('favourites', 'MealsController@addDeleteFavourite');
        Route::get('myfavourites', 'UserController@favourites');

        /* ----------------orders------------------------ */
        Route::resource('orders', 'OrdersController');
        Route::get('cart/{id}', 'OrdersController@cart');
        Route::post('rate', 'OrdersController@rate');
        Route::post('resend_order', 'OrdersController@resendOrder');
        Route::get('edit_order/{id}', 'OrdersController@orderUnderModification');
        

        
    });
});
