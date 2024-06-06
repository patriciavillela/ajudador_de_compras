<?php
require_once('service/ListaService.php');
require_once('model/Lista.php');

use model\Categoria;
use service\CategoriaService;
use service\ListaService;
use service\ProdutoService;

use model\Produto;
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
                    $produtoService = new ProdutoService();
                    $result = $produtoService->getAll();
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
    require_once('util.php');
    $listaService = new ListaService();
    if (isset($_GET['excluir'])) {
        $listaService->delete($_GET['excluir']);
    } else if (isset($_POST['item'])) {
        if (isset($_POST['only'])) {
            $produto_id = $produtoService->getProdutoIdByName($_POST['item']);
            if (!$produto_id) {
                $produto = new Produto();
                $produto->nome = $_POST['item'];
                $produto->nome_normalizado = remove_accents($produto->nome);
                $categoriaService = new CategoriaService();
                $produto->categoria = new Categoria();
                $produto->categoria->id = $categoriaService->getCategoriaIdByName('Sem categoria');
                $produto_id = $produtoService->createProduto($produto);
            }
            $listaService->incluiProdutoNaLista($produto_id, intval($_POST['qtd']));
        } else {
            $produtosAInserir = [];
            foreach ($_POST['item'] as $item => $qtd) {
                if (empty($qtd)) continue;
                $produtosAInserir[] = [
                    'item' => $item,
                    'qtd' => intval($qtd)
                ];
            }
            $listaService->incluiProdutosNaLista($produtosAInserir);
            echo "Inserido com sucesso!";
        }
    }

    $items_com_categorias = $listaService->getLista();

    if (empty($items_com_categorias)) {
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
            <tr><td><?= $detalhes['item'] ?></td><td><?= $detalhes['qtd'] ?></td><td><a href="<?php echo "?excluir={$detalhes['id']}" ?>"><span class="hide_from_print">❌</span></a></td></tr>
            <?php
        }
    }
    ?>
    </table>
</body>