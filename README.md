# Soap Wallet

## Configuración del Proyecto

1. Copiar o renombrar  `.env.example` en la raiz del proyecto con el nombre `.env`.
2. En el archivo `.env` coloca tus credenciales de base de datos en las variable que inicien con `DB_`.
3. En el archivo `.env` coloca la url de la aplicacion soap en `APP_URL`.
4. Reemplazar el valor de `RESEND_KEY` por tu apikey de resend o usa el de prueba "re_bNURZbrb_LjFfdpRPqdAWDfMyh8jUpxYb"
5. Ejecuta el comando `composer install`
6. Ejecuta el comando `php artisan:migrate`
7. Ejecuta el comando `php artisan serve --host=0.0.0.0 --port=8000`

