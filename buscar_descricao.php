<?php

require_once 'db.php';

try {
    if (isset($_GET['cod_item'])) {
        $cod_item = $_GET['cod_item'];
        $nom_item = "SELECT den_item FROM item WHERE cod_item = :cod_item"; // retorna o nome dos itens para o cod_item que bate

        $stmt = $conn->prepare($nom_item);
        $stmt->bindParam(':cod_item', $cod_item); // substitui o :cod_item pelo valor dele
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC); // pega o primeiro valor que a consulta dá, e retorna um array associativo (coluna => valor)

        echo json_encode($item ? $item : ["den_item" => ""]);
    }
} catch (PDOException $e) {
    echo json_encode(["den_item" => "Erro ao buscar"]);
}
?>
