<?php

namespace App\Services;

use App\Services\SoapService;
use SoapServer;

class SoapWalletServer
{
    protected $soapService;
    protected $server;

    public function __construct(SoapService $soapService)
    {
        $this->soapService = $soapService;
    }

    /**
     * Inicia el servidor SOAP
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Desactivar el caché WSDL
            ini_set('soap.wsdl_cache_enabled', 0);

            // Crear el servidor SOAP
            $this->server = new SoapServer(null, [
                'uri' => env('APP_URL').'/soap/wallet'
            ]);

            // Establecer la clase de servicio
            $this->server->setObject($this->soapService);

            // Verificar si hay datos de entrada (para solicitudes POST)
            $postData = file_get_contents('php://input');
            if (empty($postData)) {
                // Si no hay datos POST, usar los datos de la solicitud global
                $postData = $GLOBALS['HTTP_RAW_POST_DATA'] ?? '';
            }

            // Manejar la solicitud
            if (!empty($postData)) {
                $this->server->handle($postData);
            } else {
                $this->server->handle();
            }
        } catch (\Exception $e) {
            // Registrar el error para depuración
            logger()->error('SOAP Server Error: ' . $e->getMessage());
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">';
            echo '<SOAP-ENV:Body>';
            echo '<SOAP-ENV:Fault>';
            echo '<faultcode>SOAP-ENV:Server</faultcode>';
            echo '<faultstring>' . $e->getMessage() . '</faultstring>';
            echo '</SOAP-ENV:Fault>';
            echo '</SOAP-ENV:Body>';
            echo '</SOAP-ENV:Envelope>';
        }
    }
}
