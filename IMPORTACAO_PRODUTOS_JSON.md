# Importação e exportação de produtos em JSON

Na aba **Produtos** do painel administrativo foram adicionados os controles **Baixar lista JSON** e **Carregar lista JSON**.

Ao cadastrar ou editar um produto, o sistema mantém automaticamente um arquivo JSON individual em `@SERVIDOR/dados_produtos_json/`. O arquivo contém os campos cadastrados na tabela `produto`, incluindo imagens, descrição, características, avaliações, variações, status, categoria, ordem e código do produto.

O botão **Baixar lista JSON** gera um único arquivo com todos os produtos. Esse arquivo pode ser guardado como backup ou carregado em outra instalação do painel.

Ao usar **Carregar lista JSON**, produtos com o mesmo `codigo` são atualizados e produtos com códigos novos são cadastrados. O campo `id` do banco não é importado, portanto os IDs locais continuam sendo controlados pela instalação de destino. Registros sem nome são ignorados e o painel mostra o resumo da operação.

O endpoint de importação exige sessão autenticada no painel, limita o upload a 20 MB e aceita tanto a lista gerada pelo sistema quanto um JSON contendo um produto individual.
