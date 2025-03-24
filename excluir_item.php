<?php

require_once 'db.php';

// Verifica se cod_item foi passado na URL
if (!isset($_GET["cod_item"]) || empty($_GET["cod_item"])) {
    echo "Código do item inválido.";
    exit();
}

$cod_item = $_GET["cod_item"]; // Mantém como string porque a chave primária é VARCHAR(15)

try {
    // Exclui primeiro da tabela item_pedido
    $sql_itens = "DELETE FROM item_pedido WHERE cod_item = ?";
    $stmt_itens = $conn->prepare($sql_itens);
    $stmt_itens->execute([$cod_item]);

    // Exclui o item da tabela item
    $sql_item = "DELETE FROM item WHERE cod_item = ?";
    $stmt_item = $conn->prepare($sql_item);
    $stmt_item->execute([$cod_item]);

    echo "Item número $cod_item e seus pedidos foram excluídos com sucesso!";
    echo "<p><a href='gerenciar_item.php'>[Voltar ao gerenciamento de itens]</a></p>";
} catch (PDOException $e) {
    echo "Erro ao excluir item: " . $e->getMessage();
}
