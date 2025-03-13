<?php
$servername = "localhost";
$username = "root";
$password = "12simple36";
$dbname = "projeto_teste";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['cod_item'])) {
        $cod_item = $_GET['cod_item'];

        $stmt = $conn->prepare("SELECT den_item FROM item WHERE cod_item = :cod_item");
        $stmt->bindParam(':cod_item', $cod_item);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode($item ? $item : ["den_item" => ""]);
    }
} catch (PDOException $e) {
    echo json_encode(["den_item" => "Erro ao buscar"]);
}
?>
