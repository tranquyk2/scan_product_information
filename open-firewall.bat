@echo off
echo Opening Firewall for Laravel Server (Port 8000)...
echo.

netsh advfirewall firewall add rule name="Laravel Dev Server Port 8000" dir=in action=allow protocol=TCP localport=8000

if %errorlevel% equ 0 (
    echo.
    echo [SUCCESS] Firewall rule added successfully!
    echo.
    echo You can now access the server from other devices at:
    echo http://192.168.68.121:8000
    echo.
) else (
    echo.
    echo [ERROR] Failed to add firewall rule.
    echo Please run this script as Administrator.
    echo.
    echo Right-click this file and select "Run as administrator"
    echo.
)

pause
