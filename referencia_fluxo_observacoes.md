# Observações da referência

URL analisada: https://mercadomarketplaceon.com/store/product/escada-multifuncional-de-alum-nio-4x4-com-16-degraus-dobr-vel-e-articulada-botafogo-leve-e-resistente-imported-1779600280274-715?bypass

## Estado inicial observado

A página de referência apresenta visual de loja de referência. No carregamento observado pelo navegador, a tela exibiu um fundo amarelo loja de referência (`#fff159` aproximado) ocupando praticamente toda a viewport, com um pequeno spinner/loader azul centralizado horizontalmente e verticalmente. O conteúdo textual extraído continha cabeçalho e rodapé de loja de referência, incluindo busca, CEP, categorias, links de conta e rodapé institucional.

## Implicação para replicação

O comportamento solicitado pelo usuário (“clique em comprar, veja o spinner e a página de cadastro”) indica que o projeto deve exibir uma tela intermediária de carregamento em amarelo loja de referência, com spinner azul centralizado, antes de direcionar para a etapa de cadastro/checkout.

## Após clicar em “Comprar agora”

Ao clicar no botão **Comprar agora**, a referência redireciona para `/store/checkout` e mostra uma tela de preparação de compra com fundo branco. No centro aproximado da tela aparece um spinner circular de carregamento com segmento azul e cinza claro. Abaixo do spinner aparece o texto em duas linhas:

> Estamos preparando
> tudo para sua compra

A composição é minimalista, sem cabeçalho visível nessa etapa. A tipografia segue aparência similar ao loja de referência, com texto em cinza escuro, tamanho médio e alinhamento centralizado/levemente central na viewport.

## Página de cadastro após o carregamento

Após a tela intermediária, a referência redireciona para `/store/checkout/drop` e exibe a página **Editar endereço**. A página tem cabeçalho loja de referência amarelo com busca, menu de categorias, links de conta e carrinho exibindo `1`. O conteúdo principal fica em um cartão branco alinhado à esquerda/centro sobre fundo cinza claro.

Campos e seções observados:

| Seção | Campos/elementos |
|---|---|
| Endereço | Informe o seu CEP, botão/link “Não sei meu CEP”, Rua/Avenida, Número, checkbox “Sem número”, Complemento (opcional), Informações adicionais (opcional) com contador `0 / 128` |
| Tipo de endereço | Pergunta “É trabalho ou casa?”, opções de rádio **Casa** e **Trabalho**, com Casa selecionado por padrão |
| Dados de contato | Subtítulo “Usaremos esses dados apenas para a entrega”, Nome completo, CPF, Telefone de contato |
| Ação | Botão azul **Salvar** |

A referência utiliza inputs com bordas cinza, labels pequenos, layout em duas colunas para Rua/Número em desktop e aparência loja de referência. O formulário de cadastro/endereço aparece antes da etapa de pagamento.
