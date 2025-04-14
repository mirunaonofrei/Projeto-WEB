<?php

require_once '../db.php';

$num_pedido = $_GET["num_pedido"];


try {

    $result = $conn->beginTransaction();

    if ($result) $deleteItem = "DELETE FROM item_pedido WHERE num_pedido = :num_pedido";

    $result = $conn->prepare($deleteItem);
    $result->bindParam(':num_pedido', $num_pedido, PDO::PARAM_INT);
    $result->execute();

    if($result) $deletePedido = "DELETE FROM pedido WHERE num_pedido = :num_pedido";
    
    $result = $conn->prepare($deletePedido);
    $result->bindParam(':num_pedido', $num_pedido, PDO::PARAM_INT);
    $result->execute();


    if ($result) $result = $conn->commit();
    
    if ($result) {
        echo json_encode([
            "status" => true,
            "msg" => "Pedido excluído com sucesso!"
        ]);
    }
} catch (PDOException $e) {
    $conn->rollBack();

    echo json_encode([
        "status" => false,
        "msg" => "Erro ao excluir o pedido: " . $e->getMessage()
    ]);
}


