<?php

namespace Rayanetenorios\Pix;

class Payload
{
    private $pixKey;
    private $txid;
    private $amount;

    public function setPixKey(string $key): self { $this->pixKey = $key; return $this; }
    public function setTxid(?string $txid): self { $this->txid = $txid; return $this; }
    
    public function withTxid(): self
    {
        $this->txid = strtoupper(substr(bin2hex(random_bytes(13)), 0, 25));
        return $this;
    }

    public function setAmount(?float $amount): self { 
        $this->amount = $amount !== null ? number_format($amount, 2, '.', '') : null; 
        return $this; 
    }

    private function formatValue(string $id, string $value): string {
        $len = strlen($value);
        return $id . str_pad($len, 2, '0', STR_PAD_LEFT) . $value;
    }

    private function getValueCRC16(string $payload): string {
        $polinomio = 0x1021;
        $resultado = 0xFFFF;

        for ($offset = 0; $offset < strlen($payload); $offset++) {
            $resultado ^= (ord($payload[$offset]) << 8);
            for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                if (($resultado & 0x8000) != 0) {
                    $resultado = ($resultado << 1) ^ $polinomio;
                } else {
                    $resultado = $resultado << 1;
                }
                $resultado &= 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($resultado), 4, '0', STR_PAD_LEFT));
    }

    public function getPayload(): string
    {
        $guiField = $this->formatValue('00', 'br.gov.bcb.pix');
        $keyField = $this->formatValue('01', $this->pixKey);
        $merchantAccountInfo = $this->formatValue('26', $guiField . $keyField);

        $merchantCategory = $this->formatValue('52', '0000');
        $currency = $this->formatValue('53', '986');
        $amount = $this->amount !== null ? $this->formatValue('54', $this->amount) : '';
        $country = $this->formatValue('58', 'BR');
        $name = $this->formatValue('59', 'N');
        $city = $this->formatValue('60', 'C');

        $txidValue = $this->txid ?? '***';
        $txidField = $this->formatValue('05', $txidValue);
        $additionalData = $this->formatValue('62', $txidField);

        $payloadSemCRC =
            $this->formatValue('00', '01') .
            $merchantAccountInfo .
            $merchantCategory .
            $currency .
            $amount .
            $country .
            $name .
            $city .
            $additionalData .
            '6304';

        $crc = $this->getValueCRC16($payloadSemCRC);

        return $payloadSemCRC . $crc;
    }
}
