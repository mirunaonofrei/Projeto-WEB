<?php
require_once '../db.php';

// Consulta todos os itens
$stmt = $conn->prepare("SELECT cod_item, den_item FROM item ORDER BY cod_item");
$stmt->execute();
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retorno em JSON para o DataGrid
echo json_encode([
    "total" => count($itens),
    "rows" => $itens
]);
