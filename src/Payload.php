<?php

namespace Rayanetenorios\Pix;

class Payload
{
    private $pixKey;
    private $description;
    private $merchantName;
    private $merchantCity;
    private $amount;
    private $txid;

    public function setPixKey(string $key): self
    {
        $this->pixKey = $key;
        return $this;
    }

    public function setDescription(string $desc): self
    {
        $this->description = $desc;
        return $this;
    }

    public function setMerchantName(string $name): self
    {
        $this->merchantName = $name;
        return $this;
    }

    public function setMerchantCity(string $city): self
    {
        $this->merchantCity = $city;
        return $this;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = number_format($amount, 2, '.', '');
        return $this;
    }

    public function setTxid(string $txid): self
    {
        $this->txid = $txid;
        return $this;
    }

    private function formatValue(string $id, string $value): string
    {
        $len = strlen($value);
        return $id . str_pad($len, 2, '0', STR_PAD_LEFT) . $value;
    }

    private function getValueCRC16(string $payload): string
    {
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

        return strtoupper(dechex($resultado));
    }

    public function getPayload(): string
    {
        $gui = $this->formatValue('00', 'BR.GOV.BCB.PIX');

        $pixKey = $this->formatValue('01', $this->pixKey);

        $merchantAccountInfo = $this->formatValue('26', $gui . $pixKey);

        $merchantCategory = $this->formatValue('52', '0000');

        $currency = $this->formatValue('53', '986');

        $amount = $this->amount ? $this->formatValue('54', $this->amount) : '';

        $name = $this->formatValue('59', substr($this->merchantName, 0, 25));
        $city = $this->formatValue('60', substr($this->merchantCity, 0, 15));

        $txid = $this->formatValue('05', $this->txid ?? '***');
        $additionalData = $this->formatValue('62', $txid);

        $payloadSemCRC =
            $this->formatValue('00', '01') .
            $merchantAccountInfo .
            $merchantCategory .
            $currency .
            $amount .
            $name .
            $city .
            $additionalData .
            '6304';

        $crc = $this->getValueCRC16($payloadSemCRC);

        return $payloadSemCRC . $crc;
    }
}
