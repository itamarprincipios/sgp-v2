<?php
/**
 * Script auxiliar para criar o link simbólico da pasta public_html para a pasta public do Laravel.
 */

// 1. Seu caminho de usuário na Hostinger (conforme visto na sua conta)
$caminho_usuario = '/home/u199671261'; 

// 2. Origem: Onde está a pasta pública do seu projeto Laravel
$target = $caminho_usuario . '/sgp-v2/public';

// 3. Destino: Onde a Hostinger espera carregar o site
$link = $caminho_usuario . '/public_html';

echo "<h3>Diagnóstico do Link Simbólico</h3>";
echo "<strong>Origem (Laravel public):</strong> " . $target . " -> " . (is_dir($target) ? "<span style='color:green'>✓ OK (Pasta encontrada)</span>" : "<span style='color:red'>✗ ERRO (Pasta não encontrada. Ajuste o caminho no script)</span>") . "<br>";
echo "<strong>Destino (public_html):</strong> " . $link . " -> " . (file_exists($link) ? "<span style='color:red'>✗ Bloqueado (Já existe uma pasta física chamada public_html. Delete ou renomeie ela primeiro!)</span>" : "<span style='color:green'>✓ OK (Livre para criar)</span>") . "<br><br>";

if (!file_exists($link) && is_dir($target)) {
    if (symlink($target, $link)) {
        echo "<h2 style='color:green'>✓ SUCESSO! O link simbólico 'public_html' foi criado apontando para 'sgp-v2/public'.</h2>";
    } else {
        echo "<h2 style='color:red'>✗ Falha ao executar o comando no servidor.</h2>";
    }
}
