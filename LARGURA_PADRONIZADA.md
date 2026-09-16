# Padronização de Largura — Loja Ester

## Ajustes Implementados

Todas as seções do projeto foram padronizadas para manter uma **largura máxima de 1200px** no desktop, garantindo consistência visual em todo o fluxo de compra.

### Padrão Aplicado

| Página | Seções Ajustadas | Largura Máxima | Centralização |
|---|---|---|---|
| **produto.php** | Produto, Variações, Preço, Vendedor, Características, Descrição, Avaliações, Produtos Relacionados | 1200px | Automática (margin: 0 auto) |
| **checkout.php** | Carrinho, Resumo | 1200px | Automática com flex layout |
| **confirm_address.php** | Formulário, Título | 1200px | Automática com margin auto |
| **payment.php** | Pagamento, Resumo | 1200px | Automática com flex layout |
| **success.php** | Card de Sucesso | 1200px | Automática com margin auto |

## Comportamento por Dispositivo

### 📱 Mobile (≤ 768px)
- Largura: 100% da tela
- Padding: 12px — 16px
- Layout: Stack vertical
- Seções: Sem bordas arredondadas para economia de espaço

### 📱 Tablet (769px — 1199px)
- Largura: 100% da tela
- Padding: 16px — 20px
- Layout: Flexível (lado-a-lado quando possível)
- Seções: Bordas suavizadas

### 💻 Desktop (≥ 1200px)
- Largura: 1200px máximo
- Padding: 20px — 40px
- Layout: Lado-a-lado otimizado
- Seções: Bordas arredondadas e sombras aprimoradas
- Centralização: Automática em telas maiores

## Classes CSS Padronizadas

```css
/* Aplicado em todas as seções */
.produto-card, .variacoes-section, .preco-section,
.vendedor-section, .caracteristicas-section,
.detalhes-section, .descricao-section,
.avaliacoes-section, .produtos-relacionados-section,
.card, .cart-content, .cart-summary,
.payment-section, .summary-section {
  max-width: 1200px;
  margin-left: auto;
  margin-right: auto;
}
```

## Validação

✅ Todos os arquivos PHP passaram em validação de sintaxe
✅ Largura máxima consistente em todas as páginas
✅ Centralização automática no desktop
✅ Responsividade mantida em mobile e tablet
✅ Integrações e funcionalidades preservadas

## Resultado Visual

O projeto agora oferece uma experiência **profissional e consistente** em todas as resoluções:
- Conteúdo centralizado e bem distribuído no desktop
- Aproveitamento total da tela em mobile
- Transição suave entre breakpoints
