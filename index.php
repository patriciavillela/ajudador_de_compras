<?php
?>
<head>
    <style>
        @media print {
            .hide_from_print {
                display: none;
            }
        }
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
    <div class="hide_from_print">
        <a href="modelo.php">Exibir lista pronta</a>
        <a href="setup.php?reset=true">Reset</a>
        <fieldset>
            <legend>Incluir item na lista</legend>
            <form action="index.php" method="post">
                Item: <input name="item" list="items">
                <datalist id="items">
                    <?php
                    $pdo = new PDO('sqlite:db/sqlite.db');
                    $query = 'SELECT nome FROM produtos';
                    $result = $pdo->query($query);
                    foreach ($result as $row) {
                        echo '<option value="' . $row['nome'] . '">' . $row['nome'] . '</option>';
                    }
                    ?>
                </datalist>
                Qtd: <input name="qtd">
                <input type="hidden" name="only">
                <input type="submit" value="Submit">
            </form>
        </fieldset>
    </div>

    <?php
    require('util.php');
    if (isset($_GET['excluir'])) {
        $query = 'DELETE FROM lista WHERE id = "' . $_GET['excluir'] . '"';
        $pdo->exec($query);
        header('Location: index.php');
    } else if (isset($_POST['item'])) {
        if (isset($_POST['only'])) {
            $nome_normalizado = remove_accents($_POST['item']);
            $query = <<<QUERY
            SELECT id FROM produtos WHERE nome_normalizado = "$nome_normalizado" COLLATE NOCASE;
            QUERY;
            $result = $pdo->query($query);
            $produto_id = $result->fetchColumn();
            if (!$produto_id) {
                $query = <<<QUERY
                INSERT INTO produtos (nome, categoria) VALUES ("{$_POST['item']}", (SELECT id FROM categorias WHERE nome = 'Sem categoria'));
                QUERY;
                $pdo->exec($query);
                $produto_id = $pdo->lastInsertId();
            }
            $item = $_POST['item'];
            $qtd = intval($_POST['qtd']);
            $query = <<<QUERY
            INSERT INTO lista (produto, qtd) VALUES ($produto_id, $qtd)
            ON CONFLICT(produto) DO UPDATE SET qtd = qtd+$qtd;
            QUERY;
            $pdo->exec($query);
        } else {
            $query = <<<QUERY
            INSERT INTO lista (produto, qtd) VALUES 
            QUERY;
            $inserts = [];
            foreach ($_POST['item'] as $item => $qtd) {
                if (empty($qtd)) continue;
                $qtd = intval($qtd);
                $inserts[] = "($item, $qtd)";
            }
            $query .= implode(', ', $inserts) . " ON CONFLICT(produto) DO UPDATE SET qtd = qtd+EXCLUDED.qtd;";
            $pdo->exec($query);
            echo "Inserido com sucesso!";
        }
    }
    $query = <<<QUERY
    SELECT
        categorias.nome,
        GROUP_CONCAT(lista.id, '|') ids,
        GROUP_CONCAT(produtos.nome, '|') items,
        GROUP_CONCAT(qtd, '|') qtds
    FROM
        lista
    INNER JOIN produtos ON produtos.id = lista.produto
    INNER JOIN categorias ON produtos.categoria = categorias.id
    GROUP BY categoria
    ORDER BY categorias.rowid ASC NULLS LAST;
    QUERY;

    $result = $pdo->query($query);
    $items_com_categorias = [];
    foreach ($result as $row) {
        $items_com_categorias[$row['nome']] = [];
        $items = explode('|', $row['items']);
        $ids = explode('|', $row['ids']);
        $qtds = explode('|', $row['qtds']);
        foreach ($items as $index => $item) {
            $items_com_categorias[$row['nome']][$item] = [
                'id' => $ids[$index],
                'qtd' => $qtds[$index]
            ];
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
        foreach ($items as $item => $detalhes) {
            ?>
            <tr><td><?= $item ?></td><td><?= $detalhes['qtd'] ?></td><td><a href="<?php echo "?excluir={$detalhes['id']}" ?>"><span class="hide_from_print">❌</span></a></td></tr>
            <?php
        }
    }
    ?>
    </table>
</body>