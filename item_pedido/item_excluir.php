<?php

require_once '../db.php';


if (!isset($_GET["num_pedido"], $_GET["num_seq_item"])) {
    header('Content-Type: application/json');
    echo json_encode(["status" => false, "msg" => "Parâmetros inválidos."]);
    exit;
}


$num_pedido = $_GET["num_pedido"];
$num_seq_item = $_GET["num_seq_item"];

try {
    $conn->beginTransaction();

    $sql = "DELETE FROM item_pedido WHERE num_pedido = :num_pedido AND num_seq_item = :num_seq_item";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':num_pedido', $num_pedido, PDO::PARAM_INT);
    $stmt->bindParam(':num_seq_item', $num_seq_item, PDO::PARAM_INT);
    $stmt->execute();

    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode([
        "status" => true,
        "msg" => "Item excluído com sucesso!"
    ]);
} catch (PDOException $e) {
    $conn->rollBack();

    header('Content-Type: application/json');
    echo json_encode([
        "status" => false,
        "msg" => "Erro ao excluir o item: " . $e->getMessage()
    ]);
}
