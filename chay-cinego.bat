@echo off
title Cinego Laravel Server
cd /d D:\xampp2\htdocs\cinego-laravel

:: Tự bật MySQL của XAMPP nếu cổng 3306 chưa chạy
netstat -ano | findstr :3306 >nul
if errorlevel 1 (
    echo Dang khoi dong MySQL...
    start /min "" "D:\xampp2\mysql_start.bat"
    timeout /t 3 >nul
)

:: Mở trình duyệt mặc định và chạy server Laravel
start http://127.0.0.1:8000
D:\xampp2\php\php.exe artisan serve