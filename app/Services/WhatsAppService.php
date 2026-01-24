<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * WhatsApp Service - Generic implementation
 * 
 * Supports multiple providers:
 * 1. Fonnte (fonnte.com) - Recommended for Indonesia
 * 2. Wablas (wablas.com)
 * 3. Twilio WhatsApp API
 * 4. Meta WhatsApp Business API
 * 
 * Configuration in .env:
 * WHATSAPP_PROVIDER=fonnte
 * WHATSAPP_API_KEY=your_api_key
 * WHATSAPP_API_URL=https://api.fonnte.com
 * WHATSAPP_SENDER=628xxx (phone number with country code)
 */
class WhatsAppService
{
    protected string $provider;
    protected string $apiKey;
    protected string $apiUrl;
    protected ?string $sender;

    public function __construct()
    {
        $this->provider = config('services.whatsapp.provider', 'fonnte');
        $this->apiKey = config('services.whatsapp.api_key', '');
        $this->apiUrl = config('services.whatsapp.api_url', 'https://api.fonnte.com');
        $this->sender = config('services.whatsapp.sender');
    }

    /**
     * Send WhatsApp message
     *
     * @param string $phoneNumber Phone number with country code (e.g., 628123456789)
     * @param string $message Message content
     * @return array Response from API
     * @throws Exception
     */
    public function sendMessage(string $phoneNumber, string $message): array
    {
        // Validate configuration
        if (empty($this->apiKey)) {
            throw new Exception('WhatsApp API key not configured');
        }

        // Route to appropriate provider
        return match ($this->provider) {
            'fonnte' => $this->sendViaFonnte($phoneNumber, $message),
            'wablas' => $this->sendViaWablas($phoneNumber, $message),
            'twilio' => $this->sendViaTwilio($phoneNumber, $message),
            'meta' => $this->sendViaMeta($phoneNumber, $message),
            default => throw new Exception("Unsupported WhatsApp provider: {$this->provider}"),
        };
    }

    /**
     * Send via Fonnte (Indonesian provider)
     */
    protected function sendViaFonnte(string $phoneNumber, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
            ])->post("{$this->apiUrl}/send", [
                'target' => $phoneNumber,
                'message' => $message,
                'countryCode' => '62', // Indonesia
            ]);

            if (!$response->successful()) {
                throw new Exception("Fonnte API error: {$response->body()}");
            }

            $data = $response->json();
            
            Log::info('WhatsApp sent via Fonnte', [
                'phone' => $phoneNumber,
                'status' => $data['status'] ?? 'unknown',
                'response' => $data
            ]);

            return $data;

        } catch (Exception $e) {
            Log::error('Fonnte send failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send via Wablas (Indonesian provider)
     */
    protected function sendViaWablas(string $phoneNumber, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
            ])->post("{$this->apiUrl}/api/send-message", [
                'phone' => $phoneNumber,
                'message' => $message,
            ]);

            if (!$response->successful()) {
                throw new Exception("Wablas API error: {$response->body()}");
            }

            $data = $response->json();

            Log::info('WhatsApp sent via Wablas', [
                'phone' => $phoneNumber,
                'status' => $data['status'] ?? 'unknown',
                'response' => $data
            ]);

            return $data;

        } catch (Exception $e) {
            Log::error('Wablas send failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send via Twilio WhatsApp API
     */
    protected function sendViaTwilio(string $phoneNumber, string $message): array
    {
        try {
            $accountSid = config('services.whatsapp.twilio_sid');
            $authToken = config('services.whatsapp.twilio_token');
            $fromNumber = config('services.whatsapp.twilio_from'); // e.g., whatsapp:+14155238886

            $response = Http::withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                    'From' => $fromNumber,
                    'To' => "whatsapp:+{$phoneNumber}",
                    'Body' => $message,
                ]);

            if (!$response->successful()) {
                throw new Exception("Twilio API error: {$response->body()}");
            }

            $data = $response->json();

            Log::info('WhatsApp sent via Twilio', [
                'phone' => $phoneNumber,
                'sid' => $data['sid'] ?? 'unknown',
                'status' => $data['status'] ?? 'unknown'
            ]);

            return $data;

        } catch (Exception $e) {
            Log::error('Twilio send failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send via Meta WhatsApp Business API
     */
    protected function sendViaMeta(string $phoneNumber, string $message): array
    {
        try {
            $phoneNumberId = config('services.whatsapp.meta_phone_id');
            $accessToken = config('services.whatsapp.meta_access_token');

            $response = Http::withToken($accessToken)
                ->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $phoneNumber,
                    'type' => 'text',
                    'text' => [
                        'body' => $message
                    ]
                ]);

            if (!$response->successful()) {
                throw new Exception("Meta API error: {$response->body()}");
            }

            $data = $response->json();

            Log::info('WhatsApp sent via Meta', [
                'phone' => $phoneNumber,
                'message_id' => $data['messages'][0]['id'] ?? 'unknown',
                'response' => $data
            ]);

            return $data;

        } catch (Exception $e) {
            Log::error('Meta send failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Check service availability
     */
    public function checkConnection(): bool
    {
        try {
            // Implement provider-specific health check
            return match ($this->provider) {
                'fonnte' => $this->checkFonnteConnection(),
                'wablas' => $this->checkWablasConnection(),
                'twilio' => $this->checkTwilioConnection(),
                'meta' => $this->checkMetaConnection(),
                default => false,
            };
        } catch (Exception $e) {
            Log::error('WhatsApp connection check failed', [
                'provider' => $this->provider,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    protected function checkFonnteConnection(): bool
    {
        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
        ])->post("{$this->apiUrl}/validate");

        return $response->successful();
    }

    protected function checkWablasConnection(): bool
    {
        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
        ])->get("{$this->apiUrl}/api/device/status");

        return $response->successful();
    }

    protected function checkTwilioConnection(): bool
    {
        $accountSid = config('services.whatsapp.twilio_sid');
        $authToken = config('services.whatsapp.twilio_token');

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->get("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}.json");

        return $response->successful();
    }

    protected function checkMetaConnection(): bool
    {
        $phoneNumberId = config('services.whatsapp.meta_phone_id');
        $accessToken = config('services.whatsapp.meta_access_token');

        $response = Http::withToken($accessToken)
            ->get("https://graph.facebook.com/v18.0/{$phoneNumberId}");

        return $response->successful();
    }
}
