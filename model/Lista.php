<?php

namespace model;

class Lista
{
    public int $id;
    public Produto $produto;
    public int $qtd;
}

class Produto
{
    public int $id;
    public string $nome;
    public Categoria $categoria;
    public string $nome_normalizado;
}

class Categoria
{
    public int $id;
    public string $nome;
    public int $corredor;
    public int $ordem;
}