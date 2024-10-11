<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    return view('welcome');
});

Route::redirect('/l', '/app/login')->name('login');
Route::get('/login_from_error', function (Request $request) {
    Cookie::queue(Cookie::forget('agostini_session'));
    return redirect('app/login');
});
