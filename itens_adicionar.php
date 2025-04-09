<?php

require_once 'db.php';

$queryRecuperaitems = "SELECT cod_item, den_item FROM item";
$stmt = $conn->prepare($queryRecuperaitems);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$queryProxItem = "SELECT (COALESCE(MAX(cod_item), 0) + 1) AS cod_item FROM item";
$exeProxItem = $conn->prepare($queryProxItem);
$exeProxItem->execute();
$rowProxItem = $exeProxItem->fetch(PDO::FETCH_ASSOC);
$cod_item =  $rowProxItem['cod_item'];

// Se for requisição GET, retorna os dados
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    header('Content-Type: application/json');
    echo json_encode([
        "cod_item" => $cod_item,
        "items" => $items
    ]);
    exit();
}

// Se for requisição POST, insere um novo Item
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $cod_item = $_POST["cod_item"];
        $den_item = $_POST["den_item"];

        // Inicia a inserção no banco de dados
        $queryInsereItem = "INSERT INTO Item (cod_item, den_item) VALUES (:cod_item, :den_item)";
        $stmt = $conn->prepare($queryInsereItem);
        $stmt->bindParam(':cod_item', $cod_item);
        $stmt->bindParam(':den_item', $den_item);
        $stmt->execute();
        echo json_encode(true);
    } catch (Exception $e) {
        echo json_encode(false);
    }
    exit();
}
