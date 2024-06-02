<?php

$pdo = new PDO('sqlite:db/sqlite.db');

$query = <<<QUERY
    SELECT
        categorias.nome,
        GROUP_CONCAT(produtos.id, '|') ids,
        GROUP_CONCAT(produtos.nome, '|') items
    FROM
        categorias
    INNER JOIN produtos ON categorias.id = produtos.categoria
    GROUP BY categorias.id
    ORDER BY categorias.ordem ASC
    QUERY;

$result = $pdo->query($query);
$items_com_categorias = [];
foreach ($result as $row) {
    $items_com_categorias[$row['nome']] = [];
    $items = explode('|', $row['items']);
    $ids = explode('|', $row['ids']);
    foreach ($items as $index => $item) {
        $items_com_categorias[$row['nome']][] =
            ['nome' => $item, 'id' => $ids[$index]
        ];
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
                echo "<td>{$item['nome']}</td><td><input name=\"item[{$item['id']}]\"></td>";
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