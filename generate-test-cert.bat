@echo off
echo Generando certificado de prueba para SIFEN...
echo.

REM Crear directorio si no existe
if not exist "storage\app\certificados" mkdir storage\app\certificados

REM Crear directorio para el servicio Node si no existe
if not exist "node-service\cert" mkdir node-service\cert

REM Generar clave privada
echo Generando clave privada...
openssl genrsa -out storage\app\certificados\certificado.key 2048

REM Generar certificado autofirmado
echo Generando certificado autofirmado...
openssl req -new -x509 -key storage\app\certificados\certificado.key -out storage\app\certificados\certificado.crt -days 365 -subj "/C=PY/ST=Asuncion/L=Asuncion/O=Empresa Ejemplo S.A./OU=IT/CN=Facturacion Electronica/emailAddress=info@empresa.com"

REM Crear archivo PKCS#12
echo Creando archivo PKCS#12...
openssl pkcs12 -export -out storage\app\certificados\certificado.p12 -inkey storage\app\certificados\certificado.key -in storage\app\certificados\certificado.crt -password pass:test1234

REM Copiar al directorio del servicio Node
echo Copiando certificado al servicio Node...
copy storage\app\certificados\certificado.p12 node-service\certificado.p12
copy storage\app\certificados\certificado.p12 node-service\cert\certificado.p12

echo.
echo Certificado de prueba generado exitosamente!
echo.
echo Archivos creados:
echo - storage\app\certificados\certificado.p12
echo - node-service\certificado.p12
echo - node-service\cert\certificado.p12
echo.
echo Contraseña del certificado: test1234
echo.
echo IMPORTANTE: Este es un certificado de PRUEBA solamente.
echo Para produccion necesita un certificado real de una CA autorizada.
echo.
echo Configuracion para .env:
echo CERT_PASSWORD=test1234
echo.
pause
