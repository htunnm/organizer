@echo off
REM Fix for Curl Error 60 (SSL Certificate Problem) on Windows

echo Checking for SSL Certificate Bundle...
if not exist "%~dp0cacert.pem" (
    echo Downloading cacert.pem from curl.se...
    powershell -Command "Invoke-WebRequest -Uri https://curl.se/ca/cacert.pem -OutFile '%~dp0cacert.pem'"
)

REM Set environment variables to point to the local certificate file
set SSL_CERT_FILE=%~dp0cacert.pem
set CURL_CA_BUNDLE=%~dp0cacert.pem
set OPENSSL_CAFILE=%~dp0cacert.pem
set COMPOSER_CAFILE=%~dp0cacert.pem

echo Configuring Composer to use local CA bundle...
REM Convert backslashes to forward slashes for better compatibility
set "CA_PATH=%~dp0cacert.pem"
set "CA_PATH=%CA_PATH:\=/%"
call composer config --global cafile "%CA_PATH%"

echo Configuring Git and Composer to bypass SSL issues...
call git config --global http.sslVerify false
call composer config --global secure-http false
call composer clear-cache

echo Running Composer Install...
call composer install --prefer-dist