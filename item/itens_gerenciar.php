<?php
require_once '../db.php';
header('Content-Type: application/json');

// Se for uma requisição POST (edição ou inserção)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cod_item = intval($_POST["cod_item"]);
    $den_item = trim($_POST["den_item"]);

    try {
        // Verifica se o item já existe
        $stmt = $conn->prepare("SELECT 1 FROM item WHERE cod_item = :cod_item");
        $stmt->bindParam(':cod_item', $cod_item, PDO::PARAM_INT);
        $stmt->execute();
        $itemExiste = $stmt->fetchColumn();

        if (!$itemExiste) {
            // Inserir novo item
            $stmt = $conn->prepare("INSERT INTO item (cod_item, den_item) VALUES (:cod_item, :den_item)");
        } else {
            // Atualizar item existente
            $stmt = $conn->prepare("UPDATE item SET den_item = :den_item WHERE cod_item = :cod_item");
        }

        $stmt->bindParam(':cod_item', $cod_item, PDO::PARAM_INT);
        $stmt->bindParam(':den_item', $den_item);
        $stmt->execute();

        // Retorna um JSON com sucesso
        echo json_encode(true);
        exit();
    } catch (PDOException $e) {
        // Em caso de erro, retorna um JSON com false
        echo json_encode(false);
        exit();
    }
}

// Se for uma requisição GET (para pegar o próximo código de item)
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        // Busca o próximo código de item
        $stmt = $conn->prepare("SELECT MAX(cod_item) + 1 AS prox_codigo FROM item");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Retorna o próximo código disponível
        echo json_encode(['cod_item' => $result['prox_codigo'] ?? 1]);  // Se não houver itens, começa de 1
        exit();
    } catch (PDOException $e) {
        echo json_encode(['cod_item' => 1]); // Se der erro, começar do 1
        exit();
    }
}

// Caso a requisição não seja POST nem GET, retorna erro
echo json_encode(false);
exit();
