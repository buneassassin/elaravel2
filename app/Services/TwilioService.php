<?php

namespace App\Services;

use Twilio\Rest\Client;
use Twilio\Http\CurlClient;

class TwilioService
{
    protected $twilio;
    protected $from;

    public function __construct()
    {
        $sid    = env('TWILIO_SID');
        $token  = env('TWILIO_AUTH_TOKEN');
        $this->from = "whatsapp:+14155238886";

        // Configurar cliente HTTP para ignorar validación SSL (opcional)
        $options = [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ];
        $httpClient = new CurlClient($options);

        // Crear cliente Twilio
        $this->twilio = new Client($sid, $token);
        $this->twilio->setHttpClient($httpClient);
    }

    public function sendMessage($to, $message)
    {
        try {
            $response = $this->twilio->messages->create(
                $to,
                [
                    'from' => $this->from,
                    'body' => $message,
                ]
            );

            return [
                'success' => true,
                'sid' => $response->sid,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
