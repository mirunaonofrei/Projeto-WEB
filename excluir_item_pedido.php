<?php

require_once 'db.php';

$num_pedido = $_GET["num_pedido"];
$num_seq_item = $_GET["num_seq_item"];

$sql = "DELETE FROM item_pedido WHERE num_pedido = :num_pedido AND num_seq_item = :num_seq_item";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':num_pedido', $num_pedido, PDO::PARAM_INT);
$stmt->bindParam(':num_seq_item', $num_seq_item, PDO::PARAM_INT);

if ($stmt->execute()) {
    echo "Item número $num_seq_item do pedido número $num_pedido excluído com sucesso!";
} else {
    echo "Erro ao excluir o item do pedido.";
}

echo "<p><a href='gerenciar_pedidos.php'>[Voltar ao gerenciamento de pedidos]</a></p>";
