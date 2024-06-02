<?php
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
    <h1>Lista de compras</h1>
    <a href="modelo.php">Exibir lista pronta</a>
    <fieldset>
        <legend>Incluir item na lista</legend>
        <form action="index.php" method="post">
            Item: <input name="item">
            Qtd: <input name="qtd">
            <input type="hidden" name="only">
            <input type="submit" value="Submit">
        </form>
    </fieldset>

    <?php
    $pdo = new PDO('sqlite:sqlite.db');
    if (isset($_GET['excluir'])) {
        $query = 'DELETE FROM lista WHERE item = "' . $_GET['excluir'] . '"';
        $pdo->exec($query);
    } else if (isset($_POST['item'])) {
        if (isset($_POST['only'])) {
            $item = $_POST['item'];
            $qtd = intval($_POST['qtd']);
            $query = <<<QUERY
            INSERT INTO lista (item, qtd) VALUES ('$item', $qtd)
            ON CONFLICT(item) DO UPDATE SET qtd = qtd+$qtd;
            QUERY;
            $pdo->exec('CREATE TABLE IF NOT EXISTS lista (item STRING UNIQUE, qtd INTEGER)');
            $pdo->exec($query);
        } else {
            $query = <<<QUERY
            INSERT INTO lista (item, qtd) VALUES
            QUERY;
            $inserts = [];
            foreach ($_POST['item'] as $item => $qtd) {
                if (empty($qtd)) continue;
                $inserts[] = "('$item', $qtd)";
            }
            $query .= implode(', ', $inserts) . " ON CONFLICT(item) DO UPDATE SET qtd = qtd+EXCLUDED.qtd;";
            $pdo->exec('CREATE TABLE IF NOT EXISTS lista (item STRING UNIQUE, qtd INTEGER)');
            $pdo->exec($query);
            echo "Inserido com sucesso!";
        }
    }
    $query = <<<QUERY
    SELECT
        number,
        categoria,
        GROUP_CONCAT(item, '|') items,
        GROUP_CONCAT(qtd, '|') qtds
    FROM lista
    LEFT JOIN categorias USING (item)
    GROUP BY categoria
    ORDER BY categorias.rowid ASC NULLS LAST;
    QUERY;

    $result = $pdo->query($query);
    $items_com_categorias = [];
    foreach ($result as $row) {
        $items_com_categorias[$row['categoria']] = [];
        $items = explode('|', $row['items']);
        $qtds = explode('|', $row['qtds']);
        foreach ($items as $index => $item) {
            $items_com_categorias[$row['categoria']][$item] = $qtds[$index];
        }
    }
    if (!$result) {
        exit();
    }
    ?>
    <table>
        <tr><th>Item</th><th>Qtd</th></tr>
    <?php
    foreach ($items_com_categorias as $categoria => $items) {
        ?>
        <tr><th colspan="3"><?php echo $categoria != null ? $categoria : 'Sem categoria' ?></th></tr>
        <?php
        foreach ($items as $item => $qtd) {
            ?>
            <tr><td><?= $item ?></td><td><?= $qtd ?></td><td><a href<a href="<?php echo "?excluir=$item" ?>">❌</a></td></tr>
            <?php
        }
    }
    ?>
    </table>
</body>