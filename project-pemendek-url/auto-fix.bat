@echo off
echo ========================================
echo   AUTO FIX - PDO MySQL Driver
echo   URL Shortener Setup
echo ========================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] This script must be run as Administrator!
    echo.
    echo Right-click this file and select "Run as administrator"
    echo.
    pause
    exit /b 1
)

echo [INFO] Running as Administrator... OK
echo.

REM Find XAMPP installation
set XAMPP_PATH=C:\xampp
if not exist "%XAMPP_PATH%" (
    set XAMPP_PATH=C:\XAMPP
)
if not exist "%XAMPP_PATH%" (
    set XAMPP_PATH=D:\xampp
)
if not exist "%XAMPP_PATH%" (
    echo [ERROR] XAMPP not found in common locations!
    echo Please edit this script and set XAMPP_PATH manually.
    pause
    exit /b 1
)

echo [INFO] XAMPP found at: %XAMPP_PATH%
echo.

REM Backup php.ini
set PHP_INI=%XAMPP_PATH%\php\php.ini
if not exist "%PHP_INI%" (
    echo [ERROR] php.ini not found at: %PHP_INI%
    pause
    exit /b 1
)

echo [STEP 1] Creating backup of php.ini...
copy "%PHP_INI%" "%PHP_INI%.backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%" >nul
echo [OK] Backup created
echo.

REM Enable PDO MySQL
echo [STEP 2] Enabling PDO MySQL extension...
powershell -Command "(Get-Content '%PHP_INI%') -replace ';extension=pdo_mysql', 'extension=pdo_mysql' | Set-Content '%PHP_INI%'"
echo [OK] PDO MySQL enabled
echo.

REM Enable MySQLi
echo [STEP 3] Enabling MySQLi extension...
powershell -Command "(Get-Content '%PHP_INI%') -replace ';extension=mysqli', 'extension=mysqli' | Set-Content '%PHP_INI%'"
echo [OK] MySQLi enabled
echo.

REM Enable Mbstring
echo [STEP 4] Enabling Mbstring extension...
powershell -Command "(Get-Content '%PHP_INI%') -replace ';extension=mbstring', 'extension=mbstring' | Set-Content '%PHP_INI%'"
echo [OK] Mbstring enabled
echo.

REM Stop Apache if running
echo [STEP 5] Restarting Apache...
echo Stopping Apache...
taskkill /F /IM httpd.exe >nul 2>&1
timeout /t 2 >nul

REM Start Apache
echo Starting Apache...
start "" "%XAMPP_PATH%\apache\bin\httpd.exe"
timeout /t 3 >nul
echo [OK] Apache restarted
echo.

REM Check if MySQL is running
echo [STEP 6] Checking MySQL...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] MySQL is running
) else (
    echo [WARNING] MySQL is not running!
    echo Starting MySQL...
    start "" "%XAMPP_PATH%\mysql\bin\mysqld.exe"
    timeout /t 3 >nul
)
echo.

REM Verify
echo [STEP 7] Verifying installation...
php -m | findstr /C:"pdo_mysql" >nul
if %errorLevel% equ 0 (
    echo [SUCCESS] PDO MySQL is now enabled!
) else (
    echo [WARNING] PDO MySQL might not be enabled yet.
    echo Please restart your computer and try again.
)
echo.

echo ========================================
echo   SETUP COMPLETE!
echo ========================================
echo.
echo Next steps:
echo 1. Open browser and go to:
echo    http://localhost/project-pemendek-url/check.php
echo.
echo 2. Verify all checks are green
echo.
echo 3. If still error, restart your computer
echo.
echo 4. Access the application at:
echo    http://localhost/project-pemendek-url/
echo.
echo ========================================
pause
