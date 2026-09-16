# Atualização: Pixel Facebook / Meta

Foram adicionados a tela `@SERVIDOR/pixel.php`, o helper `api/facebook_pixel.php`, endpoints administrativos em `@SERVIDOR/api_adm/index.php` e a tabela `facebook_pixel` no SQL.

Eventos configurados: `PageView`, `ViewContent`, `AddToCart`, `InitiateCheckout`, `AddPaymentInfo` e `Purchase`.

Observação: para instalações já existentes, a própria tela do painel cria automaticamente a tabela `facebook_pixel` se ela ainda não existir.

## Correção v8 - Purchase ao copiar Pix

Nesta versão, o evento `Purchase` do Meta/Facebook Pixel passou a ser disparado no clique do botão **Copiar código Pix** em `success.php`, conforme solicitado. O disparo respeita a configuração administrativa `purchase_event`; se essa opção estiver desativada no painel, o evento de compra não será enviado.

Também foram ajustados os eventos de produto para enviar parâmetros mais completos e consistentes: `content_ids`, `contents`, `content_name`, `content_type`, `value` e `currency`. O botão principal e o botão fixo de compra da página do produto agora disparam `AddToCart` antes do redirecionamento para o checkout.
