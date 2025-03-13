<?php

$servername = "localhost";
$username = "root";
$password = "12simple36";
$dbname = "projeto_teste";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    exit();
}

if (!isset($_GET["cod_cliente"]) || empty($_GET["cod_cliente"])) {
    echo "Código do cliente inválido.";
    exit();
}

$cod_cliente = intval($_GET["cod_cliente"]); // Converte para inteiro para evitar SQL Injection

exclui_cliente($conn, $cod_cliente);
$conn = null;

function exclui_cliente($conn, $cod_cliente) {
    try {
        // Obtém os pedido do cliente
        $sql_pedido = "SELECT num_pedido FROM pedido WHERE cod_cliente = ?";
        $stmt_pedido = $conn->prepare($sql_pedido);
        $stmt_pedido->execute([$cod_cliente]);
        $pedido = $stmt_pedido->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($pedido)) {
            // Exclui os itens dos pedido primeiro
            $sql_itens = "DELETE FROM item_pedido WHERE num_pedido = ?";
            $stmt_itens = $conn->prepare($sql_itens);
            foreach ($pedido as $num_pedido) {
                $stmt_itens->execute([$num_pedido]);
            }

            // Exclui os pedido do cliente
            $sql_excluir_pedido = "DELETE FROM pedido WHERE cod_cliente = ?";
            $stmt_excluir_pedido = $conn->prepare($sql_excluir_pedido);
            $stmt_excluir_pedido->execute([$cod_cliente]);
        }

        // Exclui o cliente
        $sql_cliente = "DELETE FROM cliente WHERE cod_cliente = ?";
        $stmt_cliente = $conn->prepare($sql_cliente);
        $stmt_cliente->execute([$cod_cliente]);

        echo "Cliente número $cod_cliente e seus pedido foram excluídos com sucesso!";
        echo "<p><a href='gerenciar_cliente.php'>[Voltar ao gerenciamento de clientes]</a></p>";
    } catch (PDOException $e) {
        echo "Erro ao excluir cliente: " . $e->getMessage();
    }
}
?>
