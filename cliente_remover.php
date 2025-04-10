<?php
require_once 'db.php';

$cod_cliente = isset($_POST["cod_cliente"]) ? intval($_POST["cod_cliente"]) : 0;
try {
    // Iniciar transação
    $conn->beginTransaction();

    // Deletar registros da tabela cliente_pedido relacionados ao cliente
    $stmt_cliente_pedido = $conn->prepare("DELETE FROM pedido WHERE cod_cliente = :cod_cliente");
    $stmt_cliente_pedido->bindParam(':cod_cliente', $cod_cliente, PDO::PARAM_INT);
    $stmt_cliente_pedido->execute();

    // Deletar o cliente da tabela cliente
    $stmt_cliente = $conn->prepare("DELETE FROM cliente WHERE cod_cliente = :cod_cliente");
    $stmt_cliente->bindParam(':cod_cliente', $cod_cliente, PDO::PARAM_INT);
    $stmt_cliente->execute();

    // Commit da transação
    $conn->commit();

    echo json_encode(true); // Indica que a remoção foi bem-sucedida
} catch (PDOException $e) {
    // Rollback caso algo dê errado
    $conn->rollBack();
    echo json_encode(false); // Caso não tenha sido possível remover o cliente
}
exit();
