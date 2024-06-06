<?php

namespace service;

use model\Categoria;
use model\Produto;

require_once('util.php');

$pdo = new \PDO('sqlite:db/sqlite.db');

class ListaService
{
    public function delete(int $id) {
        global $pdo;
        $pdo->prepare('DELETE FROM lista WHERE id = :id')
            ->execute(array('id' => $id));
    }

    public function getLista() {
        global $pdo;
        $query = <<<QUERY
        SELECT
            categorias.nome 'categoria',
            lista.id,
            produtos.nome 'item',
            qtd
        FROM
            lista
        INNER JOIN produtos ON produtos.id = lista.produto
        INNER JOIN categorias ON produtos.categoria = categorias.id
        ORDER BY categorias.rowid ASC NULLS LAST;
        QUERY;
        $return = [];
        $items_com_categorias = $pdo->query($query);
        foreach($items_com_categorias as $item) {
            if (!array_key_exists($item['categoria'], $return)) {
                $return[$item['categoria']] = [];
            }
            $return[$item['categoria']][] = $item;
        }
        return $return;
    }

    public function incluiProdutoNaLista(int $produto_id, int $qtd) {
        global $pdo;
        $statement = $pdo->prepare("INSERT INTO lista (produto, qtd) VALUES (:produto, :qtd) ON CONFLICT(produto) DO UPDATE SET qtd = qtd+$qtd");
        $statement->execute(['produto' => $produto_id, 'qtd' => $qtd]);
    }

    public function incluiProdutosNaLista(array $produtos) {
        global $pdo;
        $statementQuery = 'INSERT INTO lista (produto, qtd) VALUES ';
        $produtosToBind = [];
        $statementParts = [];
        $count = 0;
        foreach ($produtos as $produto) {
            $produtosToBind["produto_{$count}"] = $produto['item'];
            $produtosToBind["qtd_{$count}"] = $produto['qtd'];
            $statementParts[] = "(:produto_{$count}, :qtd_{$count})";
            $count++;
        }
        $statementQuery .= implode(', ', $statementParts) . " ON CONFLICT(produto) DO UPDATE SET qtd = qtd+EXCLUDED.qtd;";
        $pdo->prepare($statementQuery)->execute($produtosToBind);
    }
}

class ProdutoService
{
    public function getAll() {
        global $pdo;
        return $pdo->query("SELECT * FROM produtos");
    }
    public function getProdutoIdByName(string $nome) : int {
        global $pdo;
        $nome = remove_accents($nome);
        $statement = $pdo->prepare('SELECT id FROM produtos WHERE nome_normalizado = :nome COLLATE NOCASE');
        $statement->execute(array('nome' => $nome));
        return $statement->fetchColumn();
    }
    public function createProduto(Produto $produto) : int {
        global $pdo;
        $statement = $pdo->prepare('INSERT INTO produtos (nome, categoria, nome_normalizado) VALUES (:nome, :categoria, :nome_normalizado)');
        $statement->execute(['nome' => $produto->nome, 'categoria' => $produto->categoria->id, 'nome_normalizado' => $produto->nome_normalizado]);
        $statement->execute();
        return $pdo->lastInsertId();
    }
}

class CategoriaService
{
    public function getCategoriaIdByName(string $nome) : int {
        global $pdo;
        $statement = $pdo->prepare('SELECT id FROM categorias WHERE nome = :nome COLLATE NOCASE');
        $statement->execute(array('nome' => $nome));
        return $statement->fetchColumn();
    }
}