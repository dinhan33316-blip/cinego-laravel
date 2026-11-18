<?php

use App\Http\Controllers\CinegoController;
use Illuminate\Support\Facades\Route;

// Trang chủ Cinego
Route::get('/', [CinegoController::class, 'home'])->name('home');

// Quản trị Admin
Route::match(['get', 'post'], '/admin', [CinegoController::class, 'admin'])->name('admin');

// API chung (ghế đã đặt, tra cứu vé, hủy vé, đánh giá phim, v.v.)
Route::match(['get', 'post'], '/api', [CinegoController::class, 'api'])->name('cinego.api');

// Trợ lý ảo Chatbot AI Gemini
Route::match(['get', 'post'], '/chat-ai', [CinegoController::class, 'chatAi'])->name('cinego.chat');

// Đặt vé xem phim và gửi vé điện tử
Route::match(['get', 'post'], '/send-ticket', [CinegoController::class, 'storeTicket'])->name('cinego.tickets.store');

// Legacy aliases tương thích hoàn toàn với các file gốc PHP thuần
Route::get('/index.php', [CinegoController::class, 'home']);
Route::match(['get', 'post'], '/admin.php', [CinegoController::class, 'admin']);
Route::match(['get', 'post'], '/api.php', [CinegoController::class, 'api']);
Route::match(['get', 'post'], '/chat-ai.php', [CinegoController::class, 'chatAi']);
Route::match(['get', 'post'], '/send-ticket.php', [CinegoController::class, 'storeTicket']);
