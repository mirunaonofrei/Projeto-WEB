<?php

$servername = "localhost";
$username = "root";
$password = "12simple36";
$dbname = "projeto_teste";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}

$sql = "SELECT pedido.num_pedido, cliente.nom_cliente 
        FROM pedido 
        INNER JOIN cliente ON pedido.cod_cliente = cliente.cod_cliente
        ORDER BY pedido.num_pedido";

$stmt = $conn->prepare($sql);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];


?>

<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>PEDIDOS</title>
    <link rel="stylesheet" type="text/css" href="https://www.jeasyui.com/easyui/themes/default/easyui.css">
    <link rel="stylesheet" type="text/css" href="https://www.jeasyui.com/easyui/themes/icon.css">
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/jquery.min.js"></script>
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/datagrid-detailview.js"></script>
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/src/jquery.window.js"></script>
    <style>
        body {
            background-color: #f4f4f4;
        }

        .container {
            text-align: center;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .buttons {
            display: flex;
            justify-content: right;
            align-items: right;
            flex-direction: row;
            margin-bottom: 15px;
            margin-top: 15px;
        }

        .buttons a {
            margin: 5px;
        }
    </style>
</head>

<body>
    <div class="buttons">
        <button id="gerenciar_itens" class="easyui-linkbutton" data-options="iconCls:'icon-large-smartart'">Gerenciar Itens</button>
        <button id="gerenciar_clientes" class="easyui-linkbutton" data-options="iconCls:'icon-large-smartart'">Gerenciar Clientes</button>

    </div>
    <div class="container">
        <table id="dg" class="easyui-datagrid" style="width:800px;height:400px;"
            data-options="
            iconCls: 'icon-edit',
            singleSelect:true, 
            fitColumns:true, 
            view:detailview,
            toolbar: '#tb',">
            <thead>
                <tr>
                    <th data-options="field:'num_pedido', width:80">Pedido</th>
                    <th data-options="field:'nom_cliente', width:200">Cliente</th>
                </tr>
            </thead>
        </table>
    </div>
    <div id="tb" style="height:auto">
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="append()">Adicionar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeit()">Remover</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="edit()">Editar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="reject()">Cancelar</a>
    </div>


    <div id="win"></div>

</body>

</html>


<script type="text/javascript">
    $(function() {
        $('#dg').datagrid({
            detailFormatter: function(index, row) {
                return '<div class="ddv" style="padding:5px 0"></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('div.ddv');
                ddv.panel({
                    href: 'get_itens_pedido.php?num_pedido=' + row.num_pedido,
                    border: false,
                    cache: false,
                    onLoad: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    }
                });
                setTimeout(function() {
                    $('#dg').datagrid('fixDetailRowHeight', index);
                }, 0);
            }
        });

        // Carregar os pedidos via AJAX
        $('#dg').datagrid('loadData', <?= json_encode($pedidos) ?>);
    });


    /*$(document).ready(function() {
        
        $("#incluir_pedido").click(function() {
            $('#win').window('refresh', 'controlar_pedido.php');
            $('#win').window('open');
        });
        
        $("#gerenciar_itens").click(function() {
            $('#win').window('refresh', 'gerenciar_item.php');
            $('#win').window('open');
        });
        $("#gerenciar_clientes").click(function() {
            $('#win').window('refresh', 'gerenciar_cliente.php');
            $('#win').window('open');
        });
        $(document).on("click", ".modificar_pedido", function() {
            let num_pedido = $(this).data("num_pedido");
            $('#win').window('refresh', 'controlar_pedido.php?num_pedido=' + num_pedido);
            $('#win').window('open');
        });
        $(document).on("click", ".excluir_pedido", function() {
            let num_pedido = $(this).data("num_pedido");
            if (confirm("Tem certeza que deseja excluir este pedido?")) {
                $('#win').window('refresh', 'excluir_pedido.php?num_pedido=' + num_pedido);
                $('#win').window('open');
            }
        });
        $(document).on("click", ".incluir_item", function() {
            let num_pedido = $(this).data("num_pedido");
            $('#win').window('refresh', 'controlar_item_pedido.php?num_pedido=' + num_pedido);
            $('#win').window('open');
        });
        
    });
*/

    var editIndex = undefined;

    // function endEditing() {
    //     if (editIndex == undefined) {
    //         return true
    //     }
    //     if ($('#dg').datagrid('validateRow', editIndex)) {
    //         $('#dg').datagrid('endEdit', editIndex);
    //         editIndex = undefined;
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }

    // function onClickCell(index, field) {
    //     if (editIndex != index) {
    //         if (endEditing()) {
    //             $('#dg').datagrid('selectRow', index)
    //                 .datagrid('beginEdit', index);
    //             var ed = $('#dg').datagrid('getEditor', {
    //                 index: index,
    //                 field: field
    //             });
    //             if (ed) {
    //                 ($(ed.target).data('textbox') ? $(ed.target).textbox('textbox') : $(ed.target)).focus();
    //             }
    //             editIndex = index;
    //         } else {
    //             setTimeout(function() {
    //                 $('#dg').datagrid('selectRow', editIndex);
    //             }, 0);
    //         }
    //     }
    // }

    // function onEndEdit(index, row) {
    //     var ed = $(this).datagrid('getEditor', {
    //         index: index,
    //         field: 'num_pedido'
    //     });
    //     row.nom_cliente = $(ed.target).combobox('getText');
    // }

    function append() {
        $('#win').window('refresh', 'controlar_pedido.php');
        $('#win').window('open');
        // $('#dg').datagrid('appendRow', {
        //     status: 'P'
        // });
        // editIndex = $('#dg').datagrid('getRows').length - 1;
        // $('#dg').datagrid('selectRow', editIndex)
        //     .datagrid('beginEdit', editIndex);
    }

    function removeit() {
        if (editIndex == undefined) {
            return
        }
        $('#dg').datagrid('cancelEdit', editIndex)
            .datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    function edit() {
        let num_pedido = $(this).data("num_pedido");
        if(num_pedido == undefined){
            return 
        }
        $('#win').window('refresh', 'controlar_pedido.php?num_pedido=' + num_pedido);
        $('#win').window('open');
    }

    function reject() {
        $('#dg').datagrid('rejectChanges');
        editIndex = undefined;
    }

   

    $('#win').window({
        width: 600,
        height: 400,
        modal: true,
        closed: true // Garante que a janela inicie fechada
    });
</script>