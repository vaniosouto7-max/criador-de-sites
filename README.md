# Criador de Sites — SaaS multiusuário

Esta aplicação transforma o painel legado de lojas em uma plataforma SaaS. Cada conta cria uma loja com banco MySQL exclusivo, recebe um subdomínio e pode cadastrar um domínio próprio no painel. O checkout, os produtos, os webhooks e o painel legado são reaproveitados dentro do tenant correto.

## Segurança multi-tenant

O banco de controle guarda apenas usuários, lojas e domínios. O `api/db.php` escolhe o banco do tenant pela requisição de domínio e todas as tabelas da loja são criadas a partir do schema legado. Não há credenciais hardcoded no código.

## Deploy no Railway

1. Crie um projeto no Railway e conecte o repositório `vaniosouto7-max/criador-de-sites`.
2. Adicione um serviço MySQL no mesmo projeto e configure o serviço PHP para construir pelo `Dockerfile`.
3. Configure as variáveis `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER` e `MYSQLPASSWORD` usando os valores fornecidos pelo serviço MySQL.
4. Configure `APP_ROOT_DOMAIN` com o domínio principal da plataforma, por exemplo `app.seudominio.com.br`.
5. Configure `RAILWAY_PUBLIC_DOMAIN` com o domínio público gerado pelo serviço no Railway. Ele é o destino do CNAME dos domínios dos clientes.
6. Adicione o domínio principal da plataforma ao serviço e aponte o DNS para o Railway.

O usuário final cadastra `www.sualoja.com.br` no painel, cria um CNAME apontando para `RAILWAY_PUBLIC_DOMAIN` e então clica em **Verificar**. O tráfego chega no mesmo serviço, mas o host seleciona somente o banco daquele tenant.

## Desenvolvimento local

Use PHP 8.2+ com a extensão mysqli e MySQL. Exporte as variáveis do banco antes de rodar `php -S 127.0.0.1:8080`. Para testar um tenant local, use o header `Host: loja.localhost` e defina `APP_ROOT_DOMAIN=localhost`.

## Próximas etapas recomendadas

Adicionar cobrança/planos, recuperação de senha por e-mail, rate limiting, logs centralizados, backups automáticos, verificação de propriedade via TXT além do CNAME, fila para provisionamento e revisão dos webhooks de pagamento antes de produção.
