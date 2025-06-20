@echo off
echo Iniciando servicio Node.js SIFEN...
cd /d "c:\laragon\www\facelec\node-service"
start "SIFEN NodeJS Service" node index.js
timeout /t 3
echo Servicio iniciado. Ejecutando pruebas...
node test-api-endpoints.js
pause
