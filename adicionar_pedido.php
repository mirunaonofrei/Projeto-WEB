<?php

require_once 'db.php';

$queryRecuperaClientes = "SELECT cod_cliente, nom_cliente FROM cliente";
$stmt = $conn->prepare($queryRecuperaClientes);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$queryProxPedido = "SELECT (COALESCE(MAX(num_pedido), 0) + 1) AS num_pedido FROM pedido";
$exeProxPedido = $conn->prepare($queryProxPedido);
$exeProxPedido->execute();
$rowProxPedido = $exeProxPedido->fetch(PDO::FETCH_ASSOC);
$num_pedido =  $rowProxPedido['num_pedido'];

// Se for requisição GET, retorna os dados
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    header('Content-Type: application/json');
    echo json_encode([
        "num_pedido" => $num_pedido,
        "clientes" => $clientes
    ]);
    exit();
}

// Se for requisição POST, insere um novo pedido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $cod_cliente = $_POST["cod_cliente"];
        $num_pedido = $_POST["num_pedido"];

        // Inicia a inserção no banco de dados
        $queryInserePedido = "INSERT INTO pedido (num_pedido, cod_cliente) VALUES (:num_pedido, :cod_cliente)";
        $stmt = $conn->prepare($queryInserePedido);
        $stmt->bindParam(':num_pedido', $num_pedido);
        $stmt->bindParam(':cod_cliente', $cod_cliente);
        $stmt->execute();
        header('Content-Type: application/json');
        echo json_encode(["status" => true]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(["status" => false.$e->getMessage()]);
    }
    exit();
}
