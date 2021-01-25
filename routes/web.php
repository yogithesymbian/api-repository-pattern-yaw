<?php
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

/**
* client -users  [0]
* server - admin [1]
* [0]
*/
Route::get('tes', function () {
    echo $apiToken = "".rand(1000,9999)."";
});
Route::get('flush', function () {
    Cache::flush();
});

//  authentication
Route::group([
    'prefix' => 'auth'
], function ($router) {

    $router->post('/register', 'UserController@register');
    $router->post('/login', 'UserController@login');

    $router->post('/update', 'AuthController@update');
    $router->post('/delete', 'AuthController@delete');

    $router->post('/reset/sent/email', 'UserControllerOld@sendResetToken');
    $router->post('/reset/password', 'UserControllerOld@verifyResetPassword');

    // use secure aes-256-cbc
    $router->post('/encrypt', 'UserControllerOld@encrypt');
    $router->post('/decrypt', 'UserControllerOld@decrypt');

    Route::post('refresh', 'UserController@refresh');
    $router->get('show', 'UserController@show');
    Route::post('me', 'UserController@me');

    $router->get('/redis', function () use ($router) {
        return $p = Redis::incr('p');
    });

    // redis
    $router->post('/auth/redis', 'UserControllerOld@setRedis');
    // Route::get('/role', function () {
        // $user = User::find(2);
        // Jadikan user ini sebagai admin
        // $user->assignRole('moderator');
        // Keluarkan user ini dari admin
        // $user->revokeRole('admin');
    // });

});

/**
IMAGE
**/
$router->post('/act-post-img', 'ImageController@fileUpload');
$router->post('/act-post-img-del', 'ImageController@delFoto');


$router->get('/show-user', 'AdminController@showUser');
$router->post('/user-active', 'AdminController@userActive');

$router->post('/active-time', 'AdminController@ttlActive');

/*
*GET barang
*/
$router->get('/barang/monitor', 'MonitorController@monitorV2');

// MASUK
$router->get('/barang/masuk', 'MasukController@show');
$router->post('/barang/masuk/input', 'MasukController@input');
$router->put('/barang/masuk/update', 'MasukController@update');
$router->delete('/barang/masuk/delete', 'MasukController@delete');

$router->post('/barang/masuk/find', 'MasukController@find');

// KELUAR
$router->get('/barang/keluar', 'KeluarController@show');
$router->post('/barang/keluar/input', 'KeluarController@input');
$router->post('/barang/keluar/update', 'KeluarController@update');
$router->post('/barang/keluar/delete', 'KeluarController@delete');

$router->get('/barang/all', 'BarangController@brgAll');

$router->get('/barang/list', 'BarangController@show');
$router->post('/barang/all/input', 'BarangController@input');
$router->post('/barang/all/update', 'BarangController@update');
$router->post('/barang/all/delete', 'BarangController@delete');

// $router->get('/barang/masuk', 'BarangController@brgMsk');
// $router->post('/barang/masuk/read', 'BarangController@brgMskRead');

// $router->get('/barang/keluar', 'BarangController@brgKlr');
// $router->post('/barang/keluar/read', 'BarangController@brgKlrRead');

// $router->post('/beli/barang', 'BarangController@beli');
// $router->post('/jual/barang', 'BarangController@jual');

// https://medium.com/laravel-web-id/bagaimana-mengubah-format-tanggal-di-laravel-d5203acc4bf4
Route::get('now', function () {
    // $now = Carbon::now()->addHours(8)->format('Y-m-d H:i:s');
    // $now = \Carbon\Carbon::now();
    echo env('APP_TIMEZONE') . "\n";
    echo Carbon::now();
    dd(date_default_timezone_set(env('APP_TIMEZONE')));
    // echo $now;
    // With locale
    // echo Carbon\Carbon::parse($now)->format('d F Y'); //Output: "01 Maret 2019"
    // echo "<br>";
    // echo $now->format('d, m Y H:i'); // 05, 03 2017 06:59
    // echo $now->format('d, m Y H:i'); //
    // echo "<br>";
    // echo $now->addMonths(1)->format('l, d M Y'); // 05, Apr 2017
    // echo DATE('Y-m-d H:i:s');
});

// $router->group(['prefix' => 'api'], function () use ($router) {
//   $router->get('authors',  ['uses' => 'AuthorController@showAllAuthors']);

//   $router->get('authors/{id}', ['uses' => 'AuthorController@showOneAuthor']);

//   $router->post('authors', ['uses' => 'AuthorController@create']);

//   $router->delete('authors/{id}', ['uses' => 'AuthorController@delete']);

//   $router->put('authors/{id}', ['uses' => 'AuthorController@update']);
// });