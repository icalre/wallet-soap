# Soap Wallet

1.- Copiar el archivo ".env.example" en la raiz del proyecto con el nombre ".env".
2.- Setear las variables de entorno


## Configuración del Proyecto

1. Copiar o renombrar  `.env.example` en la raiz del proyecto con el nombre `.env`.
2. En el archivo `.env` coloca tus credenciales de base de datos en las variable que inicien con `DB_`.
3. Reemplazar el valor de `RESEND_KEY` por tu apikey de resend o usa el de prueba "re_bNURZbrb_LjFfdpRPqdAWDfMyh8jUpxYb"
4. Ejecuta el comando `composer install`
5. Ejecuta el comando `php artisan:migrate`
6. Ejecuta el comando `php artisan serve --host=0.0.0.0 --port=8000`

