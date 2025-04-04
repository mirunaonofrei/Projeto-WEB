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
    <div id="p" class="easyui-panel" title="Pedidos" style="width:100%;height:100%;padding:10px; background-color:rgb(210, 229, 255);" data-options="footer:'#ft'">
        <table id="dg" class="easyui-datagrid" style="height:450px; width: 100%;"
            data-options="
                        singleSelect:true,
                        fit:true, 
                        fitColumns:true, 
                        view:detailview,
                        footer:'#ft_dg',
                        url: 'buscar_dados_datagrid.php'">
            <thead>
                <tr>
                    <th data-options="field:'num_pedido', width:'30%'">Pedido</th>
                    <th data-options="field:'nom_cliente', width:'69.5%'">Cliente</th>
                </tr>
            </thead>
        </table>


    </div>

    <div id="ft_dg" style="height:auto; background-color:rgb(155, 198, 255);">
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="adicionar()">Adicionar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="remover()">Remover</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="editar()">Editar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="cancelar()">Cancelar</a>
    </div>
    <div id="ft" class="buttons">
        <button id="gerenciar_itens" class="easyui-linkbutton" data-options="iconCls:'icon-large-smartart'">Gerenciar Itens</button>
        <button id="gerenciar_clientes" class="easyui-linkbutton" data-options="iconCls:'icon-large-smartart'">Gerenciar Clientes</button>
    </div>


</body>

</html>


<script type="text/javascript">
    $(function() { // só executa depois que a pagina carregar
        $('#dg').datagrid({ // seleciona d tabela com id = "dg"
            detailFormatter: function(index, row) { // define o conteudo das linhas que expandem, passa como parametro o indez da linha e os dados dela
                return '<div class="ddv" style="padding:5px 0"></div>'; //
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('div.ddv'); // ddv armazena o elemento div class="ddv"
                ddv.panel({ // converte a div em um panel EasyUI e carrega os dadosdo pedido usando AJAX
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
        $('#dg').datagrid('loadData');
    });

    function adicionar() {
        $.getJSON('pedido_adicionar.php', function(data) {
            if ($('#dialogAddPedido').length) {
                $('#dialogAddPedido').remove();
            }

            $('body').append('<div id="dialogAddPedido"></div>');

            let clienteOptions = `<option value="">Selecione um cliente</option>`;
            data.clientes.forEach(cliente => {
                clienteOptions += `<option value="${cliente.cod_cliente}">${cliente.nom_cliente}</option>`;
            });

            $('#dialogAddPedido').dialog({
                title: 'Adicionar Pedido',
                width: 400,
                height: 'auto',
                modal: true,
                content: `<form id="form_adiciona_pedido">
                    <div style="margin-bottom:10px">
                        <input class="easyui-textbox" label="Número do Pedido:" name="num_pedido" value="${data.num_pedido}" readonly style="width:100%;">
                    </div>

                    <div style="margin-bottom:10px">
                        <select class="easyui-combobox" label="Cliente:" name="cod_cliente" id="cod_cliente_form" required style="width:100%;">
                            ${clienteOptions}
                        </select>
                    </div>

                    <div style="text-align:center; padding-top: 10px;">
                        <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-back'">Voltar</a>
                        <a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="salvarNovoPedido()">Salvar</a>
                    </div>
                </form>`,
                buttons: [{
                    text: 'Fechar',
                    handler: function() {
                        $('#dialogAddPedido').dialog('close');
                    }
                }]
            });
        });
    }

    function salvarNovoPedido() {
        var form = $('#dialogAddPedido').find('form');

        if (!form.length) {
            $.messager.alert('Erro', 'Nenhum formulário encontrado.', 'error');
            return;
        }

        var cliente = form.find('#cod_cliente_form').val();
        if (!cliente || cliente === "") {
            $.messager.alert('Erro', 'Selecione um cliente para continuar.', 'error');
            return;
        }

        $.ajax({
            url: 'pedido_adicionar.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    $('#dg').datagrid('reload');
                    $('#dialogAddPedido').dialog('close');
                    $.messager.alert('Sucesso', "Pedido incluído com sucesso!", 'info');
                } else {
                    $.messager.alert('Erro', response.msg || "Erro ao incluir pedido!", 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("Erro AJAX:", xhr.responseText);
                $.messager.alert('Erro', 'Falha na comunicação com o servidor.', 'error');
            }
        });
    }

    function editar() {
        var row = $('#dg').datagrid('getSelected'); // Seleciona a linha do pedido
        if (row) {
            var pedidoToEdit = row; // Armazena o pedido selecionado
            var num_pedido = pedidoToEdit.num_pedido;

            $.getJSON('pedido_editar.php', {
                num_pedido: num_pedido
            }, function(data) {
                if ($('#dialogAddPedido').length) {
                    $('#dialogAddPedido').remove();
                }

                $('body').append('<div id="dialogAddPedido"></div>');

                let clienteOptions = `<option value="">Selecione um cliente</option>`;
                data.clientes.forEach(cliente => {
                    clienteOptions += `<option value="${cliente.cod_cliente}" ${cliente.cod_cliente == data.cod_cliente ? 'selected' : ''}>${cliente.nom_cliente}</option>`;
                });

                $('#dialogAddPedido').dialog({
                    title: 'Editar Pedido',
                    width: 400,
                    height: 'auto',
                    modal: true,
                    content: `<form id="form_edita_pedido">
                    <div style="margin-bottom:10px">
                        <input class="easyui-textbox" label="Número do Pedido:" name="num_pedido" value="${data.num_pedido}" readonly style="width:100%;">
                    </div>
                    <div style="margin-bottom:10px">
                        <select class="easyui-combobox" label="Cliente:" name="cod_cliente" id="cod_cliente_form" required style="width:100%;">
                            ${clienteOptions}
                        </select>
                    </div>
                    <div style="text-align:center; padding-top: 10px;">
                        <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-back'">Voltar</a>
                        <a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="salvarEdicaoPedido()">Salvar</a>
                    </div>
                </form>`,
                    buttons: [{
                        text: 'Fechar',
                        handler: function() {
                            $('#dialogAddPedido').dialog('close');
                        }
                    }]
                });



            }).fail(function() {
                $.messager.alert('Erro', 'Erro ao carregar os dados do pedido!', 'error');
            });

        } else {
            $.messager.alert('Atenção', 'Selecione um pedido para ser editado!', 'info');
            $('#dg').datagrid('reload');
        }
    }

    function salvarEdicaoPedido() {
        var form = $('#dialogAddPedido').find('form');

        if (!form.length) {
            $.messager.alert('Erro', 'Nenhum formulário encontrado.', 'error');
            return;
        }

        var cliente = form.find('#cod_cliente_form').val();
        if (!cliente || cliente === "") {
            $.messager.alert('Erro', 'Selecione um cliente para continuar.', 'error');
            return;
        }

        $.ajax({
            url: 'pedido_editar.php', 
            type: 'POST', 
            data: form.serialize(), // Envia os dados do formulário
            dataType: 'json', // Espera uma resposta JSON
            success: function(response) {
                if (response.status) {
                    $('#dg').datagrid('reload'); // Recarrega a tabela após sucesso
                    $('#dialogAddPedido').dialog('close'); // Fecha o formulário de edição
                    $.messager.alert('Sucesso', "Pedido atualizado com sucesso!", 'info');
                } else {
                    $.messager.alert('Erro', response.message || "Erro ao atualizar pedido!", 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("Erro AJAX:", xhr.responseText);
                $.messager.alert('Erro', 'Falha na comunicação com o servidor.', 'error');
            }
        });
    }

    function remover() {
        var pedidoToDelete = null; // Variável para armazenar o pedido a ser excluído
        var row = $('#dg').datagrid('getSelected'); // Seleciona a linha do pedido
        if (row) {
            $.messager.confirm({
                title: 'Exclusão',
                msg: 'Tem certeza que deseja excluir esse pedido?',
                fn: function(r) {
                    if (r) {
                        pedidoToDelete = row; // Armazena o pedido selecionado
                        var num_pedido = pedidoToDelete.num_pedido;
                        $.ajax({
                                url: 'pedido_excluir.php',
                                type: 'GET',
                                data: {
                                    num_pedido: num_pedido
                                }
                            })
                            .done(function(response) { // Quando a requisição for bem-sucedida
                                let jsonResponse = JSON.parse(response);

                                if (jsonResponse.status) {
                                    return $.messager.alert('Processamento Executado', jsonResponse.msg, 'info', function(r) {
                                        $('#dg').datagrid('reload');
                                    });
                                }

                                $.messager.alert('Erro no processamento', jsonResponse.msg, 'error', function(r) {
                                    $('#dg').datagrid('reload');
                                });
                            })
                    }
                }
            });
        } else {
            $.messager.alert('Atenção', 'Selecione um pedido para ser excluído!', 'info');
            $('#dg').datagrid('reload');

        }
    }

    function cancelar() {
        $('#dg').datagrid('rejectChanges');
        editIndex = undefined;
    }
</script>