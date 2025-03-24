<?php

require_once 'db.php';

try {
    $num_pedido = $_GET["num_pedido"];
    // Iniciar transação para garantir que ambas as exclusões aconteçam de forma atômica
    $conn->beginTransaction();

    exclui_pedido($conn, $num_pedido);

    // Se tudo estiver correto, commit na transação
    $conn->commit();
    echo "sucesso"; // Retorna sucesso para o AJAX
} catch (PDOException $e) {
    // Se ocorrer algum erro, faz rollback e exibe erro
    $conn->rollBack();
    echo "Erro: " . $e->getMessage();
}


function exclui_pedido($conn, $num_pedido) {
    // Preparando a instrução SQL de forma segura para evitar SQL Injection
    $sql = "DELETE FROM item_pedido WHERE num_pedido = :num_pedido;
            DELETE FROM pedido WHERE num_pedido = :num_pedido";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':num_pedido', $num_pedido, PDO::PARAM_INT);
    $stmt->execute();
}
?>
