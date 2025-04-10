<?php
require_once 'db.php';

$cod_item = isset($_POST["cod_item"]) ? intval($_POST["cod_item"]) : 0;

if ($cod_item > 0) {
    try {
        // Iniciar transação
        $conn->beginTransaction();

        // Deletar registros da tabela item_pedido relacionados ao item
        $stmt_item_pedido = $conn->prepare("DELETE FROM item_pedido WHERE cod_item = :cod_item");
        $stmt_item_pedido->bindParam(':cod_item', $cod_item, PDO::PARAM_INT);
        $stmt_item_pedido->execute();

        // Deletar o item da tabela item
        $stmt_item = $conn->prepare("DELETE FROM item WHERE cod_item = :cod_item");
        $stmt_item->bindParam(':cod_item', $cod_item, PDO::PARAM_INT);
        $stmt_item->execute();

        // Commit da transação
        $conn->commit();

        echo json_encode(true); // Indica que a remoção foi bem-sucedida
    } catch (PDOException $e) {
        // Rollback caso algo dê errado
        $conn->rollBack();
        echo json_encode(false); // Caso não tenha sido possível remover o item
    }
} else {
    echo json_encode(false); // Caso não tenha sido possível remover o item
}
exit();
?>
