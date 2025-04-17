<?php
require_once '../db.php';

$cod_item = isset($_POST["cod_item"]) ? intval($_POST["cod_item"]) : 0;

if ($cod_item > 0) {
    try {
        // Iniciar transação
        $conn->beginTransaction();


        $stmt_item_pedido = $conn->prepare("SELECT DISTINCT num_pedido FROM item_pedido WHERE cod_item = :cod_item");
        $stmt_item_pedido->bindParam(':cod_item', $cod_item, PDO::PARAM_INT);
        $stmt_item_pedido->execute();
        $result = $stmt_item_pedido->fetchAll(PDO::FETCH_COLUMN);


        if (empty($result)) {
            // Pode excluir, pois o item não está vinculado a nenhum pedido

            $stmt_item = $conn->prepare("DELETE FROM item WHERE cod_item = :cod_item");
            $stmt_item->bindParam(':cod_item', $cod_item, PDO::PARAM_INT);
            $stmt_item->execute();

            // Commit da transação
            $conn->commit();

            echo json_encode([
                "status" => true,
                "msg" => "Item excluído com sucesso!"
            ]);
        } else {
            // O item está em uso, não pode excluir
            echo json_encode([
                "status" => false,
                "msg" => "Item ainda está presente nos pedidos: " . json_encode($result)

            ]);
            $conn->rollBack(); // Não esquece de cancelar a transação aqui
        }
    } catch (PDOException $e) {
        // Rollback caso algo dê errado
        $conn->rollBack();
        echo json_encode(false); // Caso não tenha sido possível remover o item
    }
} else {
    echo json_encode(false); // Caso não tenha sido possível remover o item
}
exit();
