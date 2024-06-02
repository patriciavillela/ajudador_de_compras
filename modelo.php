<?php

$items = [
    'Arroz',
    'Farinha de trigo',
    'Açúcar',
    'Batata-palha',
    'Desinfetante',
    'Pasta de dente',
    'Água sanitária',
    'Maionese',
    'Palmito',
    'Azeitona',
    'Milho para pipoca',
    'Macarrão',
    'Parmesão ralado',
    'Massa de lasanha',
    'Azeite',
    'Óleo',
    'Extrato de tomate',
    'Molho de pimenta vermelha',
    'Shoyu',
    'Tabasco',
    'Sal',
    'Caldo de carne',
    'Caldo de frango',
    'Caldo de legumes',
    'Pimenta do reino',
    'Canela',
    'Cereal matinal',
    'Geleia',
    'Leite condensado',
    'Creme de leite',
    'Achocolatados',
    'Café',
    'Chá verde',
    'Torrada',
    'Biscoito',
    'Barra de chocolate',
    'Leite',
    'Manteiga',
    'Mussarela',
    'Orégano',
    'Salsinha',
    'Cebolinha',
    'Alho',
    'Batata',
    'Pão de forma',
    'Pão de hambúrguer',
    'Batata pré-frita',
    'Hambúrguer',
    'Empanado',
    'Pizza',
    'Massas Prontas',
    'Alcatra',
    'Acém',
    'Patinho',
    'Frango',
    'Fraldinha',
    'Iogurte de morango',
    'Presunto',
    'Ketchup',
    'Mostarda',
    'Pilha AA',
    'Papel-alumínio',
    'Parmesão'
];

$pdo = new PDO('sqlite:sqlite.db');

$query = <<<QUERY
    SELECT
        categoria,
        GROUP_CONCAT(item, '|') items
    FROM categorias
    GROUP BY categoria
    ORDER BY categorias.rowid ASC;
    QUERY;

$result = $pdo->query($query);
$items_com_categorias = [];
foreach ($result as $row) {
    $items_com_categorias[$row['categoria']] = [];
    foreach (preg_split('/\\|/', $row['items']) as $item) {
        $items_com_categorias[$row['categoria']][] = $item;
    }
}
?>
<head>
    <style>
        table {
            border-collapse: collapse;
            width: 25%;
        }
        table td, table th {
            border: 1px solid black;
        }
    </style>
</head>
<body>
<table>
    <form action="index.php" method="post">
        <input type="submit" value="Salvar">
        <tr><th>Item</th><th>Qtd</th></tr>
        <?php
        foreach ($items_com_categorias as $categoria => $items) {
            ?>
            <tr><th colspan="3"><?= $categoria ?></th></tr>
            <?php
            foreach ($items as $item) {
                echo "<tr>";
                echo "<td>$item</td><td><input name=\"item[$item]\"></td>";
                echo "</tr>";
            }
        }
        ?>
    </form>
</table>
</body>

<!--<body>-->
<!--    <table>-->
<!--        <tr><th>Item</th><th>Quantidade</th></tr>-->
<!--        <form action="index.php" method="post">-->
<!--            <input type="submit" value="Salvar">-->
<!--            --><?php
//            foreach($items as $item){
//                echo '<tr>';
//                echo "<td>$item</td><td><input name=\"item[$item]\"></td>";
//                echo '</tr>';
//            }
//            ?>
<!--        </form>-->
<!--    </table>-->
<!--</body>-->