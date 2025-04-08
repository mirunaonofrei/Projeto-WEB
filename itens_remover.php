<?php
require_once 'db.php';

$cod_item = isset($_POST["cod_item"]) ? intval($_POST["cod_item"]) : 0;

if ($cod_item > 0) {
    $stmt = $conn->prepare("DELETE FROM item WHERE cod_item = :cod_item");
    $stmt->bindParam(':cod_item', $cod_item, PDO::PARAM_INT);
    $stmt->execute();
    
    echo json_encode(true); // Indica que a remoção foi bem-sucedida
} else {
    echo json_encode(false); // Caso não tenha sido possível remover o item
}
exit();
?>
