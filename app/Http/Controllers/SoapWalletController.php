<?php

namespace App\Http\Controllers;

use App\Services\SoapWalletServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SoapWalletController extends Controller
{
    protected $soapServer;

    public function __construct(SoapWalletServer $soapServer)
    {
        $this->soapServer = $soapServer;
    }

    /**
     * Maneja las solicitudes SOAP
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleRequest(Request $request)
    {
        // Iniciar buffer de salida
        ob_start();

        // Procesar la solicitud SOAP
        $this->soapServer->handle();

        // Obtener la respuesta
        $response = ob_get_clean();

        // Devolver la respuesta con el tipo MIME correcto
        return Response::make($response, 200, [
            'Content-Type' => 'text/xml; charset=utf-8'
        ]);
    }

    /**
     * Genera el WSDL (documento de descripción del servicio)
     *
     * @return \Illuminate\Http\Response
     */
    public function wsdl()
    {
        $wsdl = '<?xml version="1.0" encoding="UTF-8"?>
        <definitions name="WalletService"
            targetNamespace="'.env('APP_URL').'/soap/wallet"
            xmlns="http://schemas.xmlsoap.org/wsdl/"
            xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
            xmlns:tns="'.env('APP_URL').'/soap/wallet"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema">

            <message name="registerRequest">
                <part name="name" type="xsd:string"/>
                <part name="email" type="xsd:string"/>
                <part name="documentNumber" type="xsd:string"/>
                <part name="phoneNumber" type="xsd:string"/>
            </message>
            <message name="registerResponse">
                <part name="return" type="xsd:string"/>
            </message>

            <message name="chargeWalletRequest">
                <part name="documentNumber" type="xsd:string"/>
                <part name="phoneNumber" type="xsd:string"/>
                <part name="amount" type="xsd:float"/>
            </message>
            <message name="chargeWalletResponse">
                <part name="return" type="xsd:string"/>
            </message>

            <message name="payCodeRequest">
                <part name="userId" type="xsd:int"/>
                <part name="amount" type="xsd:float"/>
            </message>
            <message name="payCodeResponse">
                <part name="return" type="xsd:string"/>
            </message>

            <message name="codeVerificationRequest">
                <part name="userId" type="xsd:int"/>
                <part name="code" type="xsd:string"/>
                <part name="hash" type="xsd:string"/>
            </message>
            <message name="codeVerificationResponse">
                <part name="return" type="xsd:string"/>
            </message>
            <message name="loadBalanceRequest">
                <part name="documentNumber" type="xsd:string"/>
                <part name="phoneNumber" type="xsd:string"/>
            </message>
            <message name="loadBalanceResponse">
                <part name="return" type="xsd:string"/>
            </message>


            <portType name="WalletPortType">
                <operation name="register">
                    <input message="tns:registerRequest"/>
                    <output message="tns:registerResponse"/>
                </operation>
                <operation name="chargeWallet">
                    <input message="tns:chargeWalletRequest"/>
                    <output message="tns:chargeWalletResponse"/>
                </operation>
                <operation name="payCode">
                    <input message="tns:payCodeRequest"/>
                    <output message="tns:payCodeResponse"/>
                </operation>
                <operation name="codeVerification">
                    <input message="tns:codeVerificationRequest"/>
                    <output message="tns:codeVerificationResponse"/>
                </operation>
                <operation name="loadBalance">
                    <input message="tns:loadBalanceRequest"/>
                    <output message="tns:loadBalanceResponse"/>
                </operation>
            </portType>

            <binding name="WalletBinding" type="tns:WalletPortType">
                <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
                <operation name="register">
                    <soap:operation soapAction="'.env('APP_URL').'/soap/wallet#register"/>
                    <input>
                        <soap:body use="encoded" namespace="'.env('APP_URL').'/soap/wallet" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
                    </input>
                    <output>
                        <soap:body use="encoded" namespace="'.env('APP_URL').'/soap/wallet" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
                    </output>
                </operation>
                <operation name="chargeWallet">
                    <soap:operation soapAction="'.env('APP_URL').'/soap/wallet#chargeWallet"/>
                    <input>
                        <soap:body use="encoded" namespace="'.env('APP_URL').'/soap/wallet" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
                    </input>
                    <output>
                        <soap:body use="encoded" namespace="'.env('APP_URL').'/soap/wallet" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
                    </output>
                </operation>
                <operation name="payCode">
                    <soap:operation soapAction="'.env('APP_URL').'/soap/wallet#payCode"/>
                    <input>
                        <soap:body use="encoded" namespace="'.env('APP_URL').'/soap/wallet" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
                    </input>
                    <output>
                        <soap:body use="encoded" namespace="'.env('APP_URL').'/soap/wallet" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
                    </output>
                </operation>
                <operation name="codeVerification">
                    <soap:operation soapAction="'.env('APP_URL').'/soap/wallet#codeVerification"/>
                    <input>
                        <soap:body use="encoded" namespace="'.env('APP_URL').'/soap/wallet" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
                    </input>
                    <output>
                        <soap:body use="encoded" namespace="'.env('APP_URL').'/soap/wallet" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
                    </output>
                </operation>
                <operation name="loadBalance">
                    <soap:operation soapAction="'.env('APP_URL').'/soap/wallet#loadBalance"/>
                    <input>
                        <soap:body use="encoded" namespace="'.env('APP_URL').'/soap/wallet" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
                    </input>
                    <output>
                        <soap:body use="encoded" namespace="'.env('APP_URL').'/soap/wallet" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
                    </output>
                </operation>
            </binding>

            <service name="WalletService">
                <port name="WalletPort" binding="tns:WalletBinding">
                    <soap:address location="'.env('APP_URL').'/soap/wallet"/>
                </port>
            </service>
        </definitions>';

        return Response::make($wsdl, 200, [
            'Content-Type' => 'text/xml; charset=utf-8'
        ]);
    }
}
