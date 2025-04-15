<?php

require_once '../db.php';

$sql = "SELECT 
            pedido.num_pedido, 
            cliente.nom_cliente,
            SUM(it.qtd_solicitada * it.pre_unitario) AS total_pedido
        FROM pedido 
        INNER JOIN cliente ON pedido.cod_cliente = cliente.cod_cliente
        LEFT JOIN item_pedido AS it ON pedido.num_pedido = it.num_pedido
        GROUP BY pedido.num_pedido, cliente.nom_cliente
        ORDER BY pedido.num_pedido";

$stmt = $conn->prepare($sql);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Formatar o total_pedido como float com 2 casas decimais
foreach ($pedidos as &$pedido) {
        $pedido['total_pedido'] = 'R$ ' . number_format((float)$pedido['total_pedido'], 2, ',', '.');

}

header('Content-Type: application/json');
echo json_encode($pedidos);
exit;
