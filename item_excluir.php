<?php

require_once 'db.php';

$num_pedido = $_GET["num_pedido"];
$num_seq_item = $_GET["num_seq_item"];

$sql = "DELETE FROM item_pedido WHERE num_pedido = :num_pedido AND num_seq_item = :num_seq_item";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':num_pedido', $num_pedido, PDO::PARAM_INT);
$stmt->bindParam(':num_seq_item', $num_seq_item, PDO::PARAM_INT);

if ($stmt->execute()) {
    echo json_encode([
        "status" => true,
        "msg" => "Item excluído com sucesso!"
    ]);
} else {
    $conn->rollBack();

    echo json_encode([
        "status" => false,
        "msg" => "Erro ao excluir o pedido: " . $e->getMessage()
    ]);
}
