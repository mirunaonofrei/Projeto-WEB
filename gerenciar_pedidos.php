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
            background-color: rgb(224, 237, 255);
        }

        .buttons {
            display: flex;
            flex-direction: row;
            justify-content: flex-end;
            background-color: rgb(155, 198, 255);
        }

        .datagrid-header,
        .datagrid-htable {
            background-color: rgb(155, 198, 255) !important;
        }
    </style>
</head>

<body>
    <div id="ft" class="buttons">
        <button id="gerenciar_itens" class="easyui-linkbutton" data-options="iconCls:'icon-large-smartart'">Gerenciar Itens</button>
        <button id="gerenciar_clientes" class="easyui-linkbutton" data-options="iconCls:'icon-large-smartart'">Gerenciar Clientes</button>
    </div>
    <div id="p" class="easyui-panel" title="Pedidos" style="width:100%;height:100%;padding:10px; background-color:rgb(210, 229, 255);" data-options="footer:'#ft'">
        <table id="dg" class="easyui-datagrid" style="height:500px; width: 100%;"
            data-options="
        iconCls: 'icon-edit',
        singleSelect:true,
        fit:true, 
        fitColumns:true, 
        view:detailview,
        footer:'#ft_dg'">
            <thead>
                <tr>
                    <th data-options="field:'num_pedido', width:'30%'">Pedido</th>
                    <th data-options="field:'nom_cliente', width:'70%'">Cliente</th>
                </tr>
            </thead>
        </table>


    </div>

    <div id="ft_dg" style="height:auto; background-color:rgb(155, 198, 255);">
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="append()">Adicionar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeit()">Remover</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="edit()">Editar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="reject()">Cancelar</a>
    </div>


    <div id="win" class="easyui-window" title="Confirmar Exclusão" style="width: 300px; height: 150px; padding: 10px;" data-options="modal:true,closed:true">
        <p>Tem certeza que deseja excluir este pedido?</p>
        <div style="text-align: center;">
            <a href="javascript:void(0)" class="easyui-linkbutton" onclick="confirmDelete()">Sim</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" onclick="closeWin()">Não</a>
        </div>
    </div>

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
    }
    var pedidoToDelete = null; // Variável para armazenar o pedido a ser excluído

    function removeit() {
        var row = $('#dg').datagrid('getSelected'); // Seleciona a linha do pedido
        if (row) {
            pedidoToDelete = row; // Armazena o pedido selecionado
            $('#win').window('open'); // Abre a janela de confirmação
        } else {
            alert("Por favor, selecione um pedido para excluir.");
        }
    }

    function confirmDelete() {
        if (pedidoToDelete) {
            var num_pedido = pedidoToDelete.num_pedido; // Obtém o número do pedido
            $.ajax({
                url: 'excluir_pedido.php', // Chama o arquivo PHP para excluir
                type: 'GET',
                data: {
                    num_pedido: num_pedido
                }, // Passa o número do pedido para exclusão
                success: function(response) {
                    if (response == 'sucesso') {
                        $('#dg').datagrid('reload'); // Recarrega os dados da tabela
                        $('#win').window('close'); // Fecha a janela de confirmação
                        alert("Pedido excluído com sucesso!");
                    } else {
                        alert("Erro ao excluir o pedido.");
                    }
                },
                error: function() {
                    alert("Erro ao tentar excluir o pedido.");
                }
            });
        }
    }

    
    function edit() {
        let num_pedido = $(this).data("num_pedido");
        if (num_pedido == undefined) {
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