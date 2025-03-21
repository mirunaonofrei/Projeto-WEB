<?php

$servername = "localhost";
$username = "root";
$password = "12simple36";
$dbname = "projeto_teste";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

$conn = null;

function exclui_pedido($conn, $num_pedido) {
    // Preparando a instrução SQL de forma segura para evitar SQL Injection
    $sql = "DELETE FROM item_pedido WHERE num_pedido = :num_pedido;
            DELETE FROM pedido WHERE num_pedido = :num_pedido";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':num_pedido', $num_pedido, PDO::PARAM_INT);
    $stmt->execute();
}
?>
