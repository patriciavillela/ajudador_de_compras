<?php

$categorias_padrao = [
    ['corredor' => 0, 'nome' => 'Arroz e farinha', 'ordem' => 1, 'items' => [
        'Arroz', 'Farinha de trigo', 'Açúcar'
    ]],
    ['corredor' => 2, 'nome' => 'Batata-palha e aperitivo', 'ordem' => 2, 'items' => [
        'Batata-palha'
    ]],
    ['corredor' => 4, 'nome' => 'Desinfetante', 'ordem' => 3, 'items' => [
        'Desinfetante'
    ]],
    ['corredor' => 6, 'nome' => 'Água sanitária', 'ordem' => 4, 'items' => [
        'Água sanitária'
    ]],
    ['corredor' => 9, 'nome' => 'Maionese, mostarda e sardinha', 'ordem' => 5, 'items' => [
        'Maionese'
    ]],
    ['corredor' => 9, 'nome' => 'Ervilha, azeitona e conserva', 'ordem' => 6, 'items' => [
        'Palmito', 'Azeitona'
    ]],
    ['corredor' => 10, 'nome' => 'Pipoca, tapioca e farinácios', 'ordem' => 7, 'items' => [
        'Milho para pipoca'
    ]],
    ['corredor' => 10, 'nome' => 'Macarrão', 'ordem' => 8, 'items' => [
        'Macarrão', 'Parmesão ralado', 'Massa de lasanha'
    ]],
    ['corredor' => 11, 'nome' => 'Azeite e óleo especial', 'ordem' => 9, 'items' => [
        'Azeite'
    ]],
    ['corredor' => 11, 'nome' => 'Óleo e soja', 'ordem' => 10, 'items' => [
        'Óleo'
    ]],
    ['corredor' => 11, 'nome' => 'Extrato de tomate, molho de pimenta e shoyu', 'ordem' => 11, 'items' => [
        'Extrato de tomate', 'Molho de pimenta vermelha', 'Shoyu', 'Tabasco'
    ]],
    ['corredor' => 12, 'nome' => 'Sal, tempero em pote ne tempero sachê', 'ordem' => 12, 'items' => [
        'Sal', 'Caldo de carne', 'Caldo de legumes', 'Caldo de frango', 'Pimenta do reino', 'Canela'
    ]],
    ['corredor' => 12, 'nome' => 'Produtos saudáveis', 'ordem' => 13, 'items' => [
        'Cereal matinal', 'Geleia'
    ]],
    ['corredor' => 13, 'nome' => 'Leite condensado e creme de leite', 'ordem' => 14, 'items' => [
        'Leite condensado', 'Creme de leite'
    ]],
    ['corredor' => 13, 'nome' => 'Achocolatado em pó, mistura para bolo e fermento', 'ordem' => 15, 'items' => [
        'Achocolatados'
    ]],
    ['corredor' => 13, 'nome' => 'Café, chá e adoçante', 'ordem' => 16, 'items' => [
        'Café', 'Chá verde'
    ]],
    ['corredor' => 14, 'nome' => 'Torrada e cream cracker', 'ordem' => 17, 'items' => [
        'Torrada'
    ]],
    ['corredor' => 14, 'nome' => 'Biscoito recheado', 'ordem' => 18, 'items' => [
        'Biscoito recheado'
    ]],
    ['corredor' => 15, 'nome' => 'Chocolate, doces e paçoca', 'ordem' => 19, 'items' => [
        'Barra de chocolate'
    ]],
    ['corredor' => 16, 'nome' => 'Grandes unidades e confeitaria', 'ordem' => 20, 'items' => [
        'Grandes unidades'
    ]],
    ['corredor' => 0, 'nome' => 'Laticínios', 'ordem' => 21, 'items' => [
        'Manteiga', 'Mussarela'
    ]],
    ['corredor' => 0, 'nome' => 'Frutas, legumes e verduras', 'ordem' => 22, 'items' => [
        'Orégano', 'Salsinha', 'Cebolinha', 'Alho', 'Batata'
    ]],
    ['corredor' => 0, 'nome' => 'Pães', 'ordem' => 23, 'items' => [
        'Pão de forma', 'Pão de hambúrguer'
    ]],
    ['corredor' => 0, 'nome' => 'Congelados', 'ordem' => 24, 'items' => [
        'Batata pré-frita', 'Hambúrguer', 'Empanado', 'Pizza', 'Massas prontas'
    ]],
    ['corredor' => 0, 'nome' => 'Açougue', 'ordem' => 25, 'items' => [
        'Alcatra', 'Acém', 'Patinho', 'Frango', 'Fraldinha'
    ]],
    ['corredor' => 0, 'nome' => 'Geladeira', 'ordem' => 26, 'items' => [
        'Iogurte de morango', 'Mussarela', 'Presunto'
    ]],
    ['corredor' => 0, 'nome' => 'Sem categoria', 'ordem' => 27, 'items' => [
        'Ketchup', 'Mostarda', 'Pilha AA', 'Papel-alumínio', 'Parmesão'
    ]]
];

require('util.php');

$pdo = new PDO('sqlite:db/sqlite.db');
if (isset($_GET['reset']) && $_GET['reset'] == 'true') {
    $pdo->exec('DROP TABLE IF EXISTS lista');
    $pdo->exec('DROP TABLE IF EXISTS produtos');
    $pdo->exec('DROP TABLE IF EXISTS categorias');
    $pdo->exec('CREATE TABLE IF NOT EXISTS categorias (id INTEGER PRIMARY KEY AUTOINCREMENT, nome STRING, corredor INTEGER, ordem INTEGER);');
    $pdo->exec('CREATE TABLE IF NOT EXISTS produtos (id INTEGER PRIMARY KEY AUTOINCREMENT, nome STRING, categoria INTEGER REFERENCES categorias(id), nome_normalizado STRING);');
    $pdo->exec('CREATE TABLE IF NOT EXISTS lista (id INTEGER PRIMARY KEY AUTOINCREMENT, produto INTEGER UNIQUE REFERENCES produtos(id), qtd INTEGER);');
    foreach ($categorias_padrao as $categoria) {
        $pdo->prepare('INSERT INTO categorias (nome, corredor, ordem) VALUES (:nome, :corredor, :ordem)')
            ->execute(['nome' => $categoria['nome'], 'corredor' => $categoria['corredor'], 'ordem' => $categoria['ordem']]);
        $categoria_id = $pdo->lastInsertId();
        foreach ($categoria['items'] as $produto) {
            $pdo->prepare('INSERT INTO produtos (nome, categoria, nome_normalizado) VALUES (:nome, :categoria, :nome_normalizado)')
                ->execute(['nome' => $produto, 'categoria' => $categoria_id, 'nome_normalizado' => remove_accents($produto)]);
        }
    }
}

header('Location: index.php');
