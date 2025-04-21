<?php

namespace App\Services;

use App\Mail\VerificationCode;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletCharge;
use App\Models\WalletPurchase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SoapService
{
    /**
     * Registra un nuevo cliente en el sistema
     *
     * @param string $name Nombre del cliente
     * @param string $email Correo electrónico
     * @param string $documentNumber Número de documento
     * @param string $phoneNumber Número de teléfono
     * @param string $password Contraseña
     * @return array Resultado de la operación
     */
    public function register(string $name, string $email, string $documentNumber, string $phoneNumber): array
    {
        try {
            // Verificar si el usuario ya existe
            if (User::where('email', $email)->exists()) {
                return [
                    'success' => false,
                    'message' => 'El correo ya está registrado',
                ];
            }

            // Crear el usuario
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'document_number' => $documentNumber,
                'phone_number' => $phoneNumber
            ]);

            // Crear wallet para el usuario
            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
            ]);

            return [
                'success' => true,
                'message' => 'Cliente registrado correctamente',
                'user_id' => $user->id,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al registrar cliente: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Recarga el wallet de un usuario
     *
     * @param int $user_id ID del usuario
     * @param float $amount Monto a recargar
     * @return array Resultado de la operación
     */
    public function chargeWallet(string $document_number, string $phone_number, float $amount): array
    {
        try {
            // Verificar si el usuario existe
            $user = User::where('document_number', $document_number)->orWhere('phone_number', $phone_number)->first();
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                ];
            }

            // Buscar wallet del usuario
            $wallet = Wallet::where('user_id', $user->id)->first();
            if (!$wallet) {
                return [
                    'success' => false,
                    'message' => 'Wallet no encontrado para este usuario',
                ];
            }

            // Registrar la recarga
            WalletCharge::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'amount' => $amount,
            ]);

            // Actualizar el balance
            $wallet->balance += $amount;
            $wallet->save();

            return [
                'success' => true,
                'message' => 'Wallet recargado correctamente',
                'new_balance' => $wallet->balance,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al recargar wallet: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Realiza un pago utilizando un código de 6 dígitos
     *
     * @param int $user_id ID del usuario
     * @param float $amount Monto a pagar
     * @return array Resultado de la operación con el código generado
     */
    public function payCode(int $user_id, float $amount): array
    {
        try {
            // Verificar si el usuario existe
            $user = User::find($user_id);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                ];
            }

            // Buscar wallet del usuario
            $wallet = Wallet::where('user_id', $user_id)->first();
            if (!$wallet) {
                return [
                    'success' => false,
                    'message' => 'Wallet no encontrado para este usuario',
                ];
            }

            // Verificar saldo suficiente
            if ($wallet->balance < $amount) {
                return [
                    'success' => false,
                    'message' => 'Saldo insuficiente',
                ];
            }

            // Generar código de 6 dígitos
            $code = Str::padLeft(rand(0, 999999), 6, '0');

            WalletPurchase::query()->update(['is_active' => false]);

            // Registrar el pago
            $wallet_purchase =  WalletPurchase::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user_id,
                'amount' => $amount,
                'code' => $code
            ]);

            Mail::to($user->email)->send(new VerificationCode($code));

            return [
                'success' => true,
                'message' => 'Código de pago generado correctamente',
                'hash'=> Hash::make($wallet_purchase->id),
                'code' => $code
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al generar pago: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verifica un código de pago
     *
     * @param int $user_id id de usuario
     * @param string $code Código de 6 dígitos
     * @param string $hash Id de sesión generado por el servidor SOAP
     * @return array Resultado de la verificación
     */
    public function codeVerification(int $user_id,string $code, string $hash): array
    {
        try {

            // Buscar el pago por código
            $purchase = WalletPurchase::where('code', $code)->where('is_active', true)->where('user_id', $user_id)->first();

            if (!$purchase) {
                return [
                    'success' => false,
                    'message' => 'Código no válido',
                ];
            }

            if (!Hash::check($purchase->id, $hash)) {
                return [
                    'success' => false,
                    'message' => 'Id de sesión no válido',
                ];
            }

            // Buscar wallet del usuario
            $wallet = Wallet::where('user_id', $purchase->user_id)->first();

            $wallet->balance -= $purchase->amount;
            $wallet->save();

            $purchase->is_active = false;
            $purchase->is_paid = true;
            $purchase->save();

            return [
                'success' => true,
                'message' => 'Código válido',
                'purchase' => [
                    'amount' => $purchase->amount,
                    'user_id' => $purchase->user_id,
                    'balance' => $wallet->balance
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al verificar código: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Consulta el saldo disponible en la wallet del usuario
     *
     * @param string $document_number ID del usuario
     * @param string $phone_number ID del usuario
     * @return array Resultado de la consulta con el saldo disponible
     */
    public function loadBalance(string $document_number, string $phone_number): array
    {
        try {
            // Verificar si el usuario existe
            $user = User::where('document_number', $document_number)->where('phone_number', $phone_number)->first();
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                ];
            }

            // Buscar wallet del usuario
            $wallet = Wallet::where('user_id', $user->id)->first();
            if (!$wallet) {
                return [
                    'success' => false,
                    'message' => 'Wallet no encontrado para este usuario',
                ];
            }

            return [
                'success' => true,
                'message' => 'Consulta de saldo exitosa',
                'user_id' => $user->id,
                'balance' => $wallet->balance
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al consultar saldo: ' . $e->getMessage(),
            ];
        }
    }

}
