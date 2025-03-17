<?php

session_start(); // Iniciar a sessão

$servername = "localhost";
$username = "root";
$password = "12simple36";
$dbname = "projeto_teste";

try {
    // Conexão com o banco de dados
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    exit();
}

$stmt = $conn->prepare("SELECT cod_cliente, nom_cliente FROM cliente");
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Variáveis
$num_pedido = isset($_GET["num_pedido"]) ? $_GET["num_pedido"] : "";

if ($num_pedido != "") {
    // Quando num_pedido for passado, pega o código do cliente associado ao pedido
    $stmt = $conn->prepare("SELECT * FROM pedido WHERE num_pedido = :num_pedido");
    $stmt->bindParam(':num_pedido', $num_pedido);
    $stmt->execute();
    $pedido = $stmt->fetch();
    $cod_cliente = $pedido ? $pedido['cod_cliente'] : "";
} else {
    // Gera um novo número de pedido
    $stmt = $conn->prepare("SELECT (COALESCE(MAX(num_pedido), 0) + 1) AS num_pedido FROM pedido");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $num_pedido = $result['num_pedido'];
    $cod_cliente = "";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Processar a inclusão ou atualização do pedido
    $cod_cliente = $_POST["cod_cliente"];
    $num_pedido = $_POST["num_pedido"];

    try {
        $stmt = $conn->prepare("SELECT * FROM pedido WHERE num_pedido = :num_pedido");
        $stmt->bindParam(':num_pedido', $num_pedido);
        $stmt->execute();
        $pedido = $stmt->fetch();

        if (!$pedido) {
            // Inserir um novo pedido
            $stmt = $conn->prepare("INSERT INTO pedido (num_pedido, cod_cliente) VALUES (:num_pedido, :cod_cliente)");
            $_SESSION['mensagem'] = "Pedido inserido com sucesso!";
        } else {
            // Atualizar o pedido existente
            $stmt = $conn->prepare("UPDATE pedido SET cod_cliente = :cod_cliente WHERE num_pedido = :num_pedido");
            $_SESSION['mensagem'] = "Pedido atualizado com sucesso!";
        }

        $stmt->bindParam(':num_pedido', $num_pedido);
        $stmt->bindParam(':cod_cliente', $cod_cliente);
        $stmt->execute();

        header("Location: controlar_pedido.php?num_pedido=$num_pedido");
        exit();
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro ao inserir ou atualizar o pedido: " . $e->getMessage();
        header("Location: controlar_pedido.php?num_pedido=$num_pedido");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controlar Pedido</title>
    <link rel="stylesheet" type="text/css" href="https://www.jeasyui.com/easyui/themes/default/easyui.css">
    <link rel="stylesheet" type="text/css" href="https://www.jeasyui.com/easyui/themes/icon.css">
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/jquery.min.js"></script>
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/datagrid-detailview.js"></script>
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/src/jquery.window.js"></script>
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
    <!-- <div id="win" class="easyui-window" title="Controlar Pedido" style="width:600px;height:400px;padding:10px;" data-options="modal:true,closed:true"> -->

        <h2>Controlar Pedido</h2>

        <!-- Exibir mensagem de sucesso ou erro -->
        <?php if (isset($_SESSION['mensagem'])): ?>
            <p class="sucesso"><?php echo $_SESSION['mensagem']; ?></p>
            <?php unset($_SESSION['mensagem']); ?>
        <?php endif; ?>

        <form action="controlar_pedido.php" method="POST">
            <label for="num_pedido">Número do Pedido:</label>
            <input type="text" id="num_pedido" name="num_pedido" value="<?php echo htmlspecialchars($num_pedido); ?>" readonly><br><br>

            <label for="cod_cliente">Código do Cliente:</label>
            <select name="cod_cliente" required>
                <option value="">Selecione um cliente</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?php echo $cliente['cod_cliente']; ?>" <?php echo ($cliente['cod_cliente'] == $cod_cliente) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cliente['nom_cliente']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>

            <input type="submit" value="Salvar">
        </form>

        <a href="gerenciar_pedidos.php"><button>Voltar</button></a>
    <!-- </div> -->


</body>

</html>