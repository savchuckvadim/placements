<?php


use App\Http\Controllers\LinkController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OfferMasterController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\UserController;
use App\Http\Resources\UserCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



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
use App\Models\User;
use Illuminate\Support\Facades\Auth;



Route::middleware(['auth:sanctum'])->group(function () {
  Route::get('/users', function (Request $request) {

    $itemsCount = $request->query('count');
    $paginate = User::paginate($itemsCount);
    $collection = new UserCollection($paginate);

    return $collection;
  });



  Route::delete('/users/{userId}', function ($userId) {
    return UserController::deleteUser($userId);
  });

  Route::post('/users/add', function (Request $request) {
    return UserController::addUser($request);
  });






  ///////////////OFFERS
  Route::post('/offer', function (Request $request) {
    return OfferController::newOffer($request);
  });

  Route::get('/offers', function (Request $request) {
    return OfferController::getOffers($request);
  });
  Route::get('offer/{offerId}', function ($offerId) {

    return OfferController::getOffer($offerId);
  });

  Route::delete('/offers/{offerId}', function ($offerId) {
    return OfferController::deleteOffer($offerId);
  });

  Route::post('/follow', function (Request $request) {


    return  OfferMasterController::follow($request);
  });
  Route::delete('/follow/{offerId}', function ($offerId) {
    return  OfferMasterController::unfollow($offerId);
  });

  Route::get('/link/{offerId}', function ($offerId) {


    return  LinkController::create($offerId);
  });



  ///////////////FINANCE
  Route::get('/finance/{date}', function ($date) {
    return  UserController::getFinance($date);
  });
});


//Users
Route::get('/user/auth', function () {
  return UserController::getAuthUser();
});



Route::get('garavatar/{userId}', function ($userId) {
  $user = User::find($userId)->first();
  return $user->getAvatarUrl();
});






//

Route::post('/sanctum/token', TokenController::class);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});

Route::post('/tokens/create', function (Request $request) {
  $token = $request->user()->createToken($request->token_name);

  return ['token' => $token->plainTextToken];
});
