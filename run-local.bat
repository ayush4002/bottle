@echo off
echo ===================================================
echo Starting TrueNorth Group Local PHP Server
echo ===================================================
echo Opening: http://localhost:8080
start http://localhost:8080
"D:\xampp\php\php.exe" -S 127.0.0.1:8080 index.php
pause
