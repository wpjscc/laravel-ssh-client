<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use BeyondCode\LaravelWebSockets\Facades\WebSocketsRouter;

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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/ssh', function () {
    return Inertia::render('Ssh', [
        'idconnection'=>bin2hex(openssl_random_pseudo_bytes(12)),
        'ws'=> env('WS','ws://127.0.0.1:6001/ssh-websocket')
    ]);
})->middleware(['auth', 'verified'])->name('ssh');

Route::get('/share-ssh', function () {
    return Inertia::render('ShareSsh', [
        'ws'=> env('WS','ws://127.0.0.1:6001/ssh-websocket')
    ]);
})->middleware(['auth', 'verified'])->name('share-ssh');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';

Route::get('idconnection', function (){
    return response()->json([
       'idconnection'=>bin2hex(openssl_random_pseudo_bytes(12))
    ]);
})->name('idconnection')->middleware(['auth','verified']);

WebSocketsRouter::webSocket('/ssh-websocket', \App\Websocket\SshWebSocketHandler::class);
