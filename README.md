# Pix Payload Generator

Gere o código de payload EMV para Pix no Brasil.

## Instalação

```bash
composer require rayanetenorios/pix-payload
```

## Métodos e Tipos de Dados

### `setPixKey(string $pixKey)`
**Tipo:** `string`  
**Obrigatório:** ✅  
**Descrição:** Chave Pix registrada no Bacen.  
**Formatos aceitos:**  
- CPF: apenas números (ex: `12345678901`)  
- CNPJ: apenas números (ex: `12345678000199`)  
- E-mail (ex: `email@exemplo.com`)  
- Telefone no formato internacional (ex: `+5511999999999`)  
- Chave aleatória UUID (ex: `a1b2c3d4-e5f6-7890-abcd-1234567890ef`)  

---

### `getPayload()`
**Retorno:** `string`  
**Descrição:** Retorna o código EMV.

---

## Exemplo de uso

```bash
use Rayanetenorios\Pix\Payload;

$payload = (new Payload())
    ->setPixKey('rayane@alvoz.com.br')
    ->getPayload();

echo $payload;
```

### Resultado
```bash
00020126410014br.gov.bcb.pix0119rayane@alvoz.com.br5204000053039865802BR5901N6001C62070503***63043A81
```