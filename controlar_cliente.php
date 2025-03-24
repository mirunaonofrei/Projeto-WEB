<?php

require_once 'db.php';

// Obtém o código do cliente (caso esteja sendo editado)
$cod_cliente = isset($_GET["cod_cliente"]) ? intval($_GET["cod_cliente"]) : 0;
$nom_cliente = "";

// Se estiver editando um cliente, preenche os dados dele
if ($cod_cliente > 0) {
    $stmt = $conn->prepare("SELECT * FROM cliente WHERE cod_cliente = :cod_cliente");
    $stmt->bindParam(':cod_cliente', $cod_cliente, PDO::PARAM_INT);
    $stmt->execute();
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        $nom_cliente = $cliente['nom_cliente'];
    }
} else {
    // Se for um novo cliente, gera um novo código
    $stmt = $conn->prepare("SELECT COALESCE(MAX(cod_cliente), 0) + 1 AS novo_cod_cliente FROM cliente");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $cod_cliente = $result['novo_cod_cliente']; 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cod_cliente = intval($_POST["cod_cliente"]);
    $nom_cliente = trim($_POST["nom_cliente"]);

    try {
        $stmt = $conn->prepare("SELECT * FROM cliente WHERE cod_cliente = :cod_cliente");
        $stmt->bindParam(':cod_cliente', $cod_cliente, PDO::PARAM_INT);
        $stmt->execute();
        $cliente = $stmt->fetch();

        if (!$cliente) {
            // Inserir novo cliente
            $stmt = $conn->prepare("INSERT INTO cliente (cod_cliente, nom_cliente) VALUES (:cod_cliente, :nom_cliente)");
            $_SESSION['mensagem'] = "Cliente '$nom_cliente' inserido com sucesso!";
        } else {
            // Atualizar cliente existente
            $stmt = $conn->prepare("UPDATE cliente SET nom_cliente = :nom_cliente WHERE cod_cliente = :cod_cliente");
            $_SESSION['mensagem'] = "Cliente '$nom_cliente' atualizado com sucesso!";
        }
        
        $stmt->bindParam(':cod_cliente', $cod_cliente, PDO::PARAM_INT);
        $stmt->bindParam(':nom_cliente', $nom_cliente);
        $stmt->execute();

        header("Location: controlar_cliente.php?cod_cliente=$cod_cliente");
        exit();
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro ao inserir ou atualizar o cliente: " . $e->getMessage();
        header("Location: controlar_cliente.php?cod_cliente=$cod_cliente");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controlar Cliente</title>
    <style>
        .erro {
            color: red;
            font-size: 14px;
        }
        .sucesso {
            color: green;
            font-size: 16px;
        }
    </style>
</head>
<body>

<h2>Controlar Cliente</h2>

<!-- Exibir mensagem de sucesso ou erro -->
<?php if (isset($_SESSION['mensagem'])): ?>
    <p class="sucesso"><?php echo $_SESSION['mensagem']; ?></p>
    <?php unset($_SESSION['mensagem']); ?>
<?php endif; ?>

<form action="controlar_cliente.php" method="POST">
    <label for="cod_cliente">Código do Cliente:</label>
    <input type="text" id="cod_cliente" name="cod_cliente" value="<?php echo htmlspecialchars($cod_cliente); ?>" readonly><br><br>

    <label for="nom_cliente">Nome do Cliente:</label>
    <input type="text" id="nom_cliente" name="nom_cliente" value="<?php echo htmlspecialchars($nom_cliente); ?>" required><br><br>

    <input type="submit" value="Salvar">
</form>

<a href="gerenciar_cliente.php"><button>Voltar</button></a>

</body>
</html>
