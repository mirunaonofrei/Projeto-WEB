<?php
require_once 'db.php';

$cod_item = isset($_POST["cod_item"]) ? intval($_POST["cod_item"]) : 0;
$den_item = isset($_POST["den_item"]) ? trim($_POST["den_item"]) : "";

// Para inserção de um novo item, buscamos o próximo código
if ($cod_item == 0) {
    // Buscando o maior cod_item existente
    $stmt = $conn->prepare("SELECT COALESCE(MAX(cod_item), 0) + 1 AS next_cod_item FROM item");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $cod_item = $result['next_cod_item'];
}

if ($cod_item > 0) {
    // Edição de item
    $stmt = $conn->prepare("UPDATE item SET den_item = :den_item WHERE cod_item = :cod_item");
    $stmt->bindParam(':cod_item', $cod_item, PDO::PARAM_INT);
    $stmt->bindParam(':den_item', $den_item, PDO::PARAM_STR);
    $stmt->execute();
    // Resposta de sucesso
    echo json_encode(true);
} else {
    // Inserção de item
    $stmt = $conn->prepare("INSERT INTO item (cod_item, den_item) VALUES (:cod_item, :den_item)");
    $stmt->bindParam(':cod_item', $cod_item, PDO::PARAM_INT);
    $stmt->bindParam(':den_item', $den_item, PDO::PARAM_STR);
    $stmt->execute();
    // Resposta de sucesso
    echo json_encode(true);
}
exit();
?>
