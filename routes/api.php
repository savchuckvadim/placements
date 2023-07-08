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




//create client
Route::post('/client', function (Request $request) {

    $domain = $request->domain;
    $key =  $request->key;

    // Создаём директорию если она не существует
    $dir = $domain;
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);

        $gitRepo = 'https://github.com/savchuckvadim/placement.git';  // качаем с git
        shell_exec("git clone $gitRepo $dir");



        // Создаём файл с расширением php
        $filename = $dir . "/settings.php";
        $file = fopen($filename, "w");

        // Добавляем в него php код в котором содержатся данные из POST запроса

        $settings = 'define(\'C_REST_WEB_HOOK_URL\',\'https://' . $domain . '/rest/1/' . $key . '/\')'; //url on creat Webhook';
        $phpcode = "<?php\n" . $settings . ";\n";



        if (fwrite($file, $phpcode) === false) {
            $responseData = ['resultCode' => 0, 'message' => "Error: Unable to write to file $filename"];
        } else {
            $responseData = ['resultCode' => 1, 'message' => "Data successfully written to $filename", 'link' => getenv('APP_URL') . '/' . $domain . '/placement.php'];
        }
        fclose($file);
    } else {
        $responseData = ['resultCode' => 0, 'message' => 'placement app ' . $domain . ' is already exist!'];
    };

    return response($responseData);
});

Route::post('/file', function (Request $request) {


    if ($request->hasFile('file')) {
        $file = $request->file('file');

        // сохраняем файл на сервере
        $filename = $file->getClientOriginalName();
        $filePath = public_path('uploads/' . $filename);
        $file->move(public_path('uploads'), $filename);

        // возвращаем ссылку на файл клиенту
        $responseData = ['resultCode'=> 0, 'message' => 'hi friend', 'file' => url('uploads/' . $filename)];


        $response = response()->json($responseData);

        // ждем 5 секунд и удаляем файл
        sleep(100);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        return response()->json($responseData);
    }
});





//Users


Route::get('/user/auth', function () {
    return UserController::getAuthUser();
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
