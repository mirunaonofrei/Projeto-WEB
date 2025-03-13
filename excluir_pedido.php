<?php

$servername = "localhost";
$username = "root";
$password = "12simple36";
$dbname = "projeto_teste";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $num_pedido = $_GET["num_pedido"];
    exclui_pedido($conn, $num_pedido);

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}

$conn = null;

function exclui_pedido($conn, $num_pedido) {
    $sql = "DELETE FROM item_pedido WHERE num_pedido = $num_pedido;
            DELETE FROM pedido WHERE num_pedido = $num_pedido;
            ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    echo "Pedido numero: $num_pedido excluido com sucesso!";
    echo "<p><a href='gerenciar_pedidos.php'>[Voltar ao gerenciamento de pedidos]</a></p>";

}


?>

