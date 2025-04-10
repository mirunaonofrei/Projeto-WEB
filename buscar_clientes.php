<?php
require_once 'db.php';

// Consulta todos os clientes
$stmt = $conn->prepare("SELECT cod_cliente, nom_cliente FROM cliente ORDER BY cod_cliente");
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retorno em JSON para o DataGrid
echo json_encode([
    "total" => count($clientes),
    "rows" => $clientes
]);
