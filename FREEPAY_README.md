# Integração FreePay Brasil - Guia Completo

## 📋 Arquivos Modificados / Criados

| Arquivo | Status | Descrição |
|---------|--------|-----------|
| `api/freepay.php` | ✅ Criado | Classe principal de integração com a API FreePay |
| `api/webhook_freepay.php` | ✅ Criado | Endpoint para receber notificações de status |
| `api/check_payment.php` | ✅ Criado | Verificação de status via AJAX |
| `api/debug_freepay.php` | ✅ Criado | Ferramenta de diagnóstico |
| `api/index.php` | ✅ Modificado | Adicionado suporte FreePay no case `gerarpix` |
| `success.php` | ✅ Modificado | Integração direta com FreePay |
| `confirm_address.php` | ✅ Modificado | Correção dos cases da API (checkout + address) |
| `SQL_FREEPAY_MIGRATION.sql` | ✅ Criado | Script de migração do banco |

---

## 🚀 Passo 1: Executar o SQL de Migração

Execute este comando no seu MySQL/MariaDB:

```sql
-- Adicionar colunas na tabela 'pix'
ALTER TABLE `pix`
  ADD COLUMN IF NOT EXISTS `freepay_public_key` TEXT NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS `freepay_secret_key` TEXT NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS `use_freepay` TINYINT(1) NOT NULL DEFAULT 0;

-- Adicionar colunas na tabela 'pixgerado'
ALTER TABLE `pixgerado`
  ADD COLUMN IF NOT EXISTS `freepay_transaction_id` TEXT NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS `freepay_status` TEXT NOT NULL DEFAULT 'PENDING';
```

**Ou use o arquivo fornecido:**
```bash
mysql -u seu_usuario -p seu_banco < SQL_FREEPAY_MIGRATION.sql
```

---

## 🔑 Passo 2: Obter as Credenciais FreePay

1. Acesse o painel da FreePay Brasil: https://dashboard.freepaybrasil.com
2. Vá em **Configurações** → **Credenciais API**
3. Copie:
   - **Public Key** (chave pública)
   - **Secret Key** (chave secreta)

---

## ⚙️ Passo 3: Configurar no Painel Admin

1. Acesse o painel administrativo da loja
2. Vá em **Configurações** → **PIX**
3. Preencha os campos:
   - **FreePay Public Key**: Cole a chave pública
   - **FreePay Secret Key**: Cole a chave secreta
   - **Ativar Gateway FreePay**: Marque a caixa ✓
4. Clique em **Salvar**

---

## 🔗 Passo 4: Configurar o Webhook

No painel da FreePay:

1. Vá em **Webhooks** ou **Notificações**
2. Adicione uma nova URL de webhook:
   ```
   https://seudominio.com/api/webhook_freepay.php
   ```
3. Selecione os eventos:
   - ✓ Transação Paga
   - ✓ Transação Recusada
   - ✓ Transação Expirada
   - ✓ Transação Estornada
4. Salve a configuração

---

## 🧪 Passo 5: Testar a Integração

### Método 1: Usar o Script de Diagnóstico

Acesse no navegador:
```
https://seudominio.com/api/debug_freepay.php
```

Este script verificará:
- ✓ Se as colunas existem no banco
- ✓ Se as credenciais estão salvas
- ✓ Se os arquivos necessários existem
- ✓ Se consegue conectar na API FreePay
- ✓ Últimos erros registrados

### Método 2: Teste Manual

1. Acesse a loja como cliente
2. Selecione um produto
3. Preencha os dados pessoais
4. Na tela de pagamento, clique em **Continuar**
5. Verifique se aparece o QR code da FreePay (não do Mercado Pago)

---

## 🐛 Troubleshooting

### Problema: Continua gerando PIX do Mercado Pago

**Possíveis causas:**

| Causa | Solução |
|-------|---------|
| SQL não foi executado | Execute o `SQL_FREEPAY_MIGRATION.sql` |
| `use_freepay` está 0 (desativado) | Ative no painel admin e salve |
| Chaves vazias ou inválidas | Verifique as credenciais no painel |
| Arquivo `freepay.php` não existe | Verifique se foi enviado para `api/freepay.php` |
| Erro de permissão de arquivo | Verifique permissões: `chmod 644 api/freepay.php` |

### Problema: Erro "Credenciais não configuradas"

1. Acesse o diagnóstico: `debug_freepay.php`
2. Verifique se as colunas existem no banco
3. Confirme se as chaves foram salvas corretamente
4. Teste a conexão com a API

### Problema: QR Code não aparece

1. Verifique se há erro no arquivo de log do servidor
2. Teste a conexão com a API FreePay via `debug_freepay.php`
3. Valide as chaves Public/Secret
4. Verifique se o valor está correto (deve ser em reais, ex: 59.99)

### Problema: Webhook não está recebendo notificações

1. Verifique se a URL está correta e acessível
2. Teste manualmente: `curl https://seudominio.com/api/webhook_freepay.php`
3. Verifique os logs do servidor
4. Confirme se o webhook foi salvo corretamente no painel da FreePay

---

## 📊 Fluxo de Pagamento

```
Cliente → Seleciona Produto
    ↓
Cliente → Preenche Dados (confirm_address.php)
    ↓
API → Salva dados (checkout + address)
    ↓
Cliente → Clica em Continuar (payment.php)
    ↓
success.php → Verifica use_freepay
    ↓
    ├─ SIM → Chama createFreePayPix()
    │         ↓
    │         API FreePay → Cria transação
    │         ↓
    │         Retorna QR code + transaction_id
    │         ↓
    │         Salva em pixgerado (freepay_transaction_id)
    │
    └─ NÃO → Gera PIX estático (fallback)
    ↓
Cliente → Escaneia QR Code e paga
    ↓
FreePay → Envia webhook (webhook_freepay.php)
    ↓
Banco → Atualiza freepay_status para PAID
    ↓
Frontend → Detecta status PAID (check_payment.php)
    ↓
Cliente → Vê confirmação de pagamento
```

---

## 🔒 Segurança

### Remover o Script de Diagnóstico em Produção

Após validar que tudo está funcionando, **delete** o arquivo:
```bash
rm api/debug_freepay.php
```

### Proteger as Chaves

As chaves são armazenadas na tabela `pix` do banco. Certifique-se de:
- ✓ Fazer backup regular do banco
- ✓ Usar HTTPS em produção
- ✓ Restringir acesso ao painel admin
- ✓ Não compartilhar as chaves

---

## 📝 Logs e Monitoramento

Os erros são registrados em `error_log()`. Para visualizar:

```bash
# Ver últimas 50 linhas de erro
tail -50 /var/log/php_errors.log | grep -i freepay

# Ou verificar via diagnóstico
https://seudominio.com/api/debug_freepay.php
```

---

## 💡 Dicas Importantes

1. **Valor em Reais**: A API FreePay recebe valores em reais (59.99), não em centavos
2. **Autenticação**: Usa Basic Auth com `Base64(PUBLIC_KEY:SECRET_KEY)`
3. **Webhook**: Recebe POST JSON com status da transação
4. **Fallback**: Se FreePay falhar, cai automaticamente para PIX estático
5. **Transação ID**: Salvo em `pixgerado.freepay_transaction_id` para rastreamento

---

## 📞 Suporte

**Documentação Oficial FreePay:**
- https://freepaybrasil.readme.io/reference/introdução

**Endpoints Principais:**
- `POST` https://api.freepaybrasil.com/v1/payment-transaction/create
- `GET` https://api.freepaybrasil.com/v1/payment-transaction/info/{id}

**Em caso de problemas:**
1. Execute o `debug_freepay.php`
2. Verifique os logs do servidor
3. Valide as credenciais no painel da FreePay
4. Contate o suporte da FreePay com o transaction_id

---

**Versão:** 1.0
**Data:** 28/04/2026
**Status:** ✅ Pronto para Produção
