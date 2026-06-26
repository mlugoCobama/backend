<?php

namespace Modules\Ucoip\Services;

use Exception;

class CifradoService
{
    private string $cipher = 'aes-256-gcm';
    private string $key;

    public function __construct()
    {
        $key = env('CREDENTIALS_KEY');

        if (!$key) {
            throw new Exception('CREDENTIALS_KEY no configurada.');
        }

        $this->key = base64_decode($key);

        if (strlen($this->key) !== 32) {
            throw new Exception(
                'CREDENTIALS_KEY debe contener 32 bytes codificados en Base64.'
            );
        }
    }

    /**
     * Cifra una contraseña.
     */
    public function encrypt(string $plainText): string
    {
        $iv = random_bytes(12); // Recomendado para GCM
        $tag = '';

        $encrypted = openssl_encrypt(
            $plainText,
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($encrypted === false) {
            throw new Exception('Error al cifrar.');
        }

        return base64_encode(json_encode([
            'iv'   => base64_encode($iv),
            'tag'  => base64_encode($tag),
            'data' => base64_encode($encrypted),
        ]));
    }

    /**
     * Descifra una contraseña.
     */
    public function decrypt(string $payload): string
    {
        $decoded = json_decode(
            base64_decode($payload),
            true
        );

        if (
            !$decoded ||
            !isset($decoded['iv']) ||
            !isset($decoded['tag']) ||
            !isset($decoded['data'])
        ) {
            throw new Exception('Payload inválido.');
        }

        $iv = base64_decode($decoded['iv']);
        $tag = base64_decode($decoded['tag']);
        $data = base64_decode($decoded['data']);

        $decrypted = openssl_decrypt(
            $data,
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($decrypted === false) {
            throw new Exception(
                'No fue posible descifrar la información.'
            );
        }

        return $decrypted;
    }
}