<?php

session_start();

$servername = "localhost";
$username = "root";
$password = "12simple36";
$dbname = "projeto_teste";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    exit();
}

$stmt = $conn->prepare("SELECT cod_cliente, nom_cliente FROM cliente");
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Cliente</title>
    <style>
        table {
            border: 1px solid black;
            border-collapse: collapse;
            width: 600px;
            margin: 0 auto 10px;
        }

        td {
            border: 1px solid black;
            padding: 8px;
        }

        .header {
            font-weight: bold;
            background-color: gray;
        }

        button {
            text-align: center;
            background-color: lightslategray;
            color: white;
            height: 40px;
        }
        div {
            display: flex;
            justify-content: center; /* Centraliza os itens horizontalmente */
            align-items: flex-end; /* Alinha os itens na parte inferior da div */
            gap: 10px;
        } 
    </style>
</head>
<body>
    <?php if (!empty($clientes)): ?>
            <?php foreach ($clientes as $row): ?>
                <table>
                    <tr>
                        <td class="header">ID:</td>
                        <td><?= $row['cod_cliente'] ?></td>
                        <td class="header">Nome do Cliente:</td>
                        <td><?= $row['nom_cliente'] ?></td>
                        <td class="right">
                            <a href='controlar_cliente.php?cod_cliente=<?= $row['cod_cliente'] ?>'>[Modificar Cliente]</a><br>
                            <a href='excluir_cliente.php?cod_cliente=<?= $row['cod_cliente'] ?>' onclick='return confirm("Tem certeza que deseja excluir este cliente?")'>[Excluir Cliente]</a><br>
                        </td>
                    </tr>
                </table>
            <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhum cliente encontrado.</p>
    <?php endif; ?>
        <div>
            <a href='gerenciar_pedidos.php'><button>Voltar</button></a>
            <a href='controlar_cliente.php'><button>Adicionar Cliente</button></a>
        </div>
    

</body>
</html>