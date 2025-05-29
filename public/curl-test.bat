@echo off
echo Test de l'API AdminLicence avec cURL
echo ===================================
echo.

set API_KEY=sk_VZwQ9VzvuRwt1nsrIbkKPXgNicuR0Dx1
set SERIAL_KEY=9QXH-YDNF-WBFL-XFTU
set DOMAIN=exemple.com
set IP_ADDRESS=127.0.0.1
set API_URL=http://127.0.0.1:8000/api/v1/check-serial

echo Configuration:
echo - Clé API: %API_KEY%
echo - Clé de licence: %SERIAL_KEY%
echo - Domaine: %DOMAIN%
echo - Adresse IP: %IP_ADDRESS%
echo - URL de l'API: %API_URL%
echo.

echo Exécution du test...
echo.

curl -X POST %API_URL% ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -H "X-API-KEY: %API_KEY%" ^
  -d "{\"serial_key\":\"%SERIAL_KEY%\",\"domain\":\"%DOMAIN%\",\"ip_address\":\"%IP_ADDRESS%\"}" ^
  -v

echo.
echo Test terminé.
pause
