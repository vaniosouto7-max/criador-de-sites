/**
 * js_variacoes_extrator.js
 * Extrai variações de produtos a partir do texto/HTML da página de origem
 * e preenche os campos de variação no formulário de add_produto.php
 */

/**
 * Extrai variações (cores, tamanhos, voltagens, RAM, armazenamento) do texto bruto da página de origem.
 * @param {string} texto - Texto bruto da página do produto
 * @returns {{ variacoes: Object, tipoDetectado: string }}
 */
function extrairVariacoes(texto) {
    var variacoes = {};
    var tipoDetectado = 'generico';

    if (!texto) return { variacoes: variacoes, tipoDetectado: tipoDetectado };

    // --- Voltagens ---
    var voltagens = [];
    if (/127\s*v/i.test(texto)) voltagens.push('127v');
    if (/220\s*v/i.test(texto)) voltagens.push('220v');
    if (/bivolt/i.test(texto)) { voltagens.push('127v'); voltagens.push('220v'); }
    if (voltagens.length > 0) {
        variacoes.voltagens = [...new Set(voltagens)];
        tipoDetectado = 'eletronico';
    }

    // --- RAM ---
    var ramMatches = texto.match(/\b(\d+\s*GB)\s*(RAM|de RAM|memória RAM)/gi);
    if (ramMatches && ramMatches.length > 0) {
        variacoes.ram = [...new Set(ramMatches.map(function(m) {
            return m.replace(/(RAM|de RAM|memória RAM)/gi, '').trim();
        }))];
        tipoDetectado = 'celular';
    }

    // --- Armazenamento ---
    var storageMatches = texto.match(/\b(\d+\s*(GB|TB))\s*(armazenamento|de armazenamento|interno|de armazenamento interno)?/gi);
    if (storageMatches && storageMatches.length > 0) {
        var storageList = [...new Set(storageMatches.map(function(m) {
            return m.replace(/(armazenamento|de armazenamento|interno|de armazenamento interno)/gi, '').trim();
        }).filter(function(s) { return s.length > 0; }))];
        if (storageList.length > 0) {
            variacoes.armazenamento = storageList;
            if (tipoDetectado === 'generico') tipoDetectado = 'celular';
        }
    }

    // --- Tamanhos de roupa ---
    // Procura apenas no contexto de tamanho para não capturar números,
    // códigos e medidas aleatórias presentes no HTML da página.
    var tamanhosRoupa = [];
    var contextosTamanho = texto.match(/(?:tamanho|tamanhos|size|sizes)[^<\n]{0,250}/gi) || [];
    var tamRegex = /\b(P|M|G|GG)\b/gi;
    contextosTamanho.forEach(function(contexto) {
        var tamMatch;
        while ((tamMatch = tamRegex.exec(contexto)) !== null) {
            tamanhosRoupa.push(tamMatch[1].toUpperCase());
        }
        tamRegex.lastIndex = 0;
    });
    if (tamanhosRoupa.length > 2) {
        variacoes.tamanhos = [...new Set(tamanhosRoupa)];
        if (tipoDetectado === 'generico') tipoDetectado = 'roupa';
    }

    // --- Cores ---
    var coresComuns = ['preto', 'branco', 'azul', 'vermelho', 'verde', 'amarelo', 'rosa', 'roxo', 'laranja', 'cinza', 'prata', 'dourado', 'bege', 'marrom'];
    var coresEncontradas = [];
    coresComuns.forEach(function(cor) {
        var regex = new RegExp('\\b' + cor + '\\b', 'i');
        if (regex.test(texto)) {
            coresEncontradas.push(cor.charAt(0).toUpperCase() + cor.slice(1));
        }
    });
    if (coresEncontradas.length > 0) {
        variacoes.cores = coresEncontradas;
        if (tipoDetectado === 'generico') tipoDetectado = 'roupa';
    }

    return { variacoes: variacoes, tipoDetectado: tipoDetectado };
}

/**
 * Preenche os campos de variação no formulário com base nos dados extraídos.
 * @param {Object} variacoes - Objeto com as variações extraídas
 * @param {string} tipoDetectado - Tipo de produto detectado
 */
function preencherVariacoes(variacoes, tipoDetectado) {
    // Define o tipo de produto no select
    var selectTipo = document.getElementById('tipo_produto');
    if (selectTipo && tipoDetectado) {
        selectTipo.value = tipoDetectado;
        atualizarVariacoes();
    }

    // Preenche voltagens
    if (variacoes.voltagens) {
        if (variacoes.voltagens.indexOf('127v') !== -1) {
            var v127 = document.getElementById('volt_127');
            if (v127) v127.checked = true;
        }
        if (variacoes.voltagens.indexOf('220v') !== -1) {
            var v220 = document.getElementById('volt_220');
            if (v220) v220.checked = true;
        }
    }

    // Preenche RAM
    if (variacoes.ram && document.getElementById('var_ram')) {
        document.getElementById('var_ram').value = variacoes.ram.join(', ');
    }

    // Preenche armazenamento
    if (variacoes.armazenamento && document.getElementById('var_armazenamento')) {
        document.getElementById('var_armazenamento').value = variacoes.armazenamento.join(', ');
    }

    // Preenche tamanhos de roupa
    if (variacoes.tamanhos && document.getElementById('var_tamanhos')) {
        var tamanhosPermitidos = ['P', 'M', 'G', 'GG'];
        var tamanhosFiltrados = tamanhosPermitidos.filter(function(tamanho) {
            return variacoes.tamanhos.map(String).map(function(item) { return item.toUpperCase(); }).indexOf(tamanho) !== -1;
        });
        document.getElementById('var_tamanhos').value = tamanhosFiltrados.join(', ');
    }

    // Preenche cores (até 4)
    if (variacoes.cores) {
        variacoes.cores.slice(0, 4).forEach(function(cor, idx) {
            var i = idx + 1;
            var chkAtiva = document.getElementById('cor_ativa_' + i);
            var inputNome = document.getElementById('cor_nome_' + i);
            if (chkAtiva) chkAtiva.checked = true;
            if (inputNome) inputNome.value = cor;
        });
    }

    // Serializa o JSON de variações no campo oculto
    if (typeof buildVariacoesJSON === 'function') {
        buildVariacoesJSON();
    }
}
