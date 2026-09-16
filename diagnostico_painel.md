# Diagnóstico: Problemas Identificados no Painel

## 1. Desconfiguração Visual do Painel

### Causa Raiz
O painel está sem estilização porque faltam os arquivos CSS e JavaScript do framework Material Dashboard:

**Arquivos Faltantes:**
- `./assets/css/material-dashboard.css` (v3.0.4)
- `./assets/css/nucleo-icons.css`
- `./assets/css/nucleo-svg.css`
- `./assets/js/plugins/perfect-scrollbar.min.js`
- `./assets/js/plugins/smooth-scrollbar.min.js`
- `./assets/img/` (pasta com ícones e imagens)

**Referências no Código:**
- `dashboard.php` linha 23-30
- `index.php` linha 24-34
- `pix.php` linha 24-28

### Solução
Criar um arquivo CSS minimalista que simule o Material Dashboard para que o painel funcione visualmente enquanto aguarda os assets completos.

---

## 2. Falha no Salvamento de Integrações de Pagamento

### Causa Raiz
O formulário em `pix.php` envia os dados corretamente para `api_adm/index.php` (case "trocapix"), mas a query UPDATE falha silenciosamente porque:

1. **Registro não existe**: A tabela `pix` pode não ter um registro com `id='1'`
2. **Colunas faltam**: Dependendo da versão do banco, colunas como `mp_access_token`, `use_mercadopago`, `pixgo_api_key`, etc. podem não existir
3. **Erro na query**: A query UPDATE é muito longa e pode ter problemas de sintaxe ou escape

### Análise da Query (linha 862 de api_adm/index.php)
```php
$query = mysqli_query($conn, "UPDATE pix SET chave='$chave', cidade='$cidade',
identificador='$identificador', descricao='$descricao', beneficiario='$beneficiario',
freepay_public_key='$freepay_public', freepay_secret_key='$freepay_secret',
use_freepay='$use_freepay', mp_access_token='$mp_access_token',
use_mercadopago='$use_mercadopago', use_pix_produto='$use_pix_produto',
pixgo_api_key='$pixgo_api_key', pixgo_webhook_secret='$pixgo_webhook_secret',
use_pixgo='$use_pixgo' WHERE id='1'");
```

**Problemas:**
- Não verifica se o registro existe antes de atualizar
- Não valida se as colunas existem
- Não trata erros de forma informativa
- Se `WHERE id='1'` não encontrar nada, a query retorna sucesso mas 0 linhas afetadas

### Solução
1. Verificar se o registro `pix` com `id='1'` existe
2. Se não existir, criar um registro vazio
3. Verificar se todas as colunas necessárias existem
4. Se não existirem, criá-las via ALTER TABLE
5. Retornar mensagens de erro mais descritivas

---

## Próximos Passos

1. ✅ Criar arquivo CSS minimalista para o Material Dashboard
2. ✅ Adicionar verificação de registro na tabela `pix` antes de UPDATE
3. ✅ Adicionar verificação de colunas antes de UPDATE
4. ✅ Melhorar tratamento de erros na API
5. ✅ Criar script de diagnóstico para verificar o estado do banco
