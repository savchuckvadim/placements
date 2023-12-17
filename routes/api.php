<?php

use App\Http\Controllers\FileController;
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
use Illuminate\Support\Facades\Log;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/users', function (Request $request) {

        $itemsCount = $request->query('count');
        $paginate = User::paginate($itemsCount);
        $collection = new UserCollection($paginate);

        return $collection;
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



Route::post('/refresh', function (Request $request) {
    Log::info('LOG', $request->all());
    $type = $request->type; // 'client' | 'test' | 'dev'
    $dir = "./";
    Log::info('TYPE', ['type' => $type]);
    // Получаем список всех файлов и папок в данной директории
    $folders = scandir($dir);
    $resultFolders = [];
    $results = [];
    $count = 0;
    $fldrsPaths = [];

    foreach ($folders as $folder) {
        // Полный путь к папке
        $full_path = "./" . $folder;
        $count++;
        array_push($fldrsPaths, $full_path);
        // Проверяем, является ли элемент папкой и не является ли он служебной папкой . или ..
        if ($type == 'client' && ($folder == 'client' || $folder == 'public')) {
         
            $output = shell_exec("git -C {$full_path} pull");
            array_push($results, $output);
            array_push($resultFolders, $folder);


        } else if ($type == 'test' && $folder == 'test') {
            
            $output = shell_exec("git -C {$full_path} pull");
            array_push($results, $output);
            array_push($resultFolders, $folder);
        } else if ($type == 'dev' && $folder == 'dev') {
          
            $output = shell_exec("git -C {$full_path} pull");
            array_push($results, $output);
            array_push($resultFolders, $folder);
        }
    }
    $responseData = [
        'resultCode' => 0,
        'message' => 'new updating',
        'updatedFolders' => $resultFolders,
        'outputs' => $results,

        'allFolders' => $folders,
        'count' =>  $count,
        'fldrsPaths' => $fldrsPaths

    ];



    return response($responseData);
});





// Route::post('/file/write', function (Request $request) {

//     if ($request->hasFile('file')) {
//         $file = $request->file('file');

//         // Проверка на расширение .doc или .docx
//         if ($file->getClientOriginalExtension() === 'doc' || $file->getClientOriginalExtension() === 'docx') {

//             // Сохранение файла на сервере
//             $path = $file->store('public');

//             // Загрузка файла в PHPWord
//             $phpWord = new \PhpOffice\PhpWord\PhpWord()

//             // Редактирование файла
//             // $sections = $phpWord->getSections();
//             // $sectionы = $phpWord->addSection(array('pageNumberingStart' => 1));
//             $data = [
//                 ['name' => 'supply', 'bitrixId' => 'UF_CRM_15168672545'],
//                 // Другие объекты...
//             ];

//             foreach($sections as $section) {
//                 $elements = $section->getElements();

//                 foreach($elements as $element) {
//                     if(method_exists($element, 'getText')) {
//                         $text = $element->getText();

//                         foreach($data as $replace) {
//                             if ($text === $replace['name']) {
//                                 $element->setText($replace['bitrixId']);
//                             }
//                         }
//                     }
//                 }
//             }

//             // Сохранение отредактированного файла
//             $writer = IOFactory::createWriter($phpWord, 'Word2007');
//             $newPath = 'public/edited_' . $file->getClientOriginalName();
//             $writer->save(Storage::path($newPath));

//             // Удаление исходного файла
//             Storage::delete($path);

//             // Отправка ссылки на файл обратно клиенту
//             $response = [
//                 'resultCode'=> 0, 
//                 'message' => 'File edited successfully', 
//                 'file' => Storage::url($newPath)
//             ];

//             return response($response);
//         } 
//     }

//     return response(['resultCode' => 1, 'message' => 'No file uploaded or wrong file type']);
// });

Route::post('createAprilTemplate', function (Request $request) {
    return FileController::processFields($request);
});




Route::post('/file', function (Request $request) {


    if ($request->hasFile('file')) {
        $file = $request->file('file');
        // $domain = $request->file('file');

        // сохраняем файл на сервере
        $filename = $file->getClientOriginalName();
        // $filePath = public_path('uploads/' . $filename);
        // $file->move(public_path('uploads'), $filename);

        // возвращаем ссылку на файл клиенту
        // $responseData = ['resultCode' => 0, 'message' => 'hi friend', 'file' => url('uploads/' . $filename)];
        // $path = $request->file('file')->store('test');


        // Storage::disk('public')->put($filename, 'test');
        $path = $file->storeAs('public', $filename);


        $responseData = ['resultCode' => 0, 'message' => 'hi friend', 'fileName' => $filename];
        // $response = response()->json($responseData);

        // ждем 5 секунд и удаляем файл
        // sleep(100);
        // if (file_exists($filePath)) {
        //     unlink($filePath);
        // }

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


// export enum ResultCodesEnum {
//     Error = 1,
//     Success = 0
// }
