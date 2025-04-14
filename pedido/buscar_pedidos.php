<?php

require_once '../db.php';

$sql = "SELECT pedido.num_pedido, cliente.nom_cliente  -- colunas escolhidas
        FROM pedido 
        INNER JOIN cliente ON pedido.cod_cliente = cliente.cod_cliente -- retorna apenas os clientes que correspondem a um pedido
        ORDER BY pedido.num_pedido";

$stmt = $conn->prepare($sql);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []; //retorna todas as linhas como um array associativo (coluna => valor)

header('Content-Type: application/json');
echo json_encode($pedidos);
exit;