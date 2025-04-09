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
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-large-smartart',plain:true" onclick="gerenciar_itens()">Gerenciar Itens</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-large-smartart',plain:true" onclick="gerenciar_clientes()">Gerenciar Clientes</a>
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
                    href: 'item_pedido.php?num_pedido=' + row.num_pedido,
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

    function gerenciar_itens() {
        if ($('#dialogGerenciarItens').length) {
            $('#dialogGerenciarItens').remove();
        }

        $('body').append('<div id="dialogGerenciarItens"></div>');

        $('#dialogGerenciarItens').dialog({
            title: 'Gerenciar Itens',
            width: 800,
            height: 400,
            closed: false,
            cache: false,
            modal: true,
            buttons: [{
                text: 'Adicionar',
                iconCls: 'icon-add',
                handler: function() {
                    abrirFormularioItem('Novo Item');
                }
            }, {
                text: 'Remover',
                iconCls: 'icon-remove',
                handler: function() {
                    let item = $('#dgItens').datagrid('getSelected');
                    if (!item) {
                        $.messager.alert('Atenção', 'Selecione um item para remover.', 'warning');
                        return;
                    }
                    $.messager.confirm('Confirmação', 'Deseja realmente remover este item?', function(r) {
                        if (r) {
                            $.post('remover_item.php', {
                                cod_item: item.cod_item
                            }, function(res) {
                                if (res) {
                                    $('#dgItens').datagrid('reload');
                                } else {
                                    $.messager.alert('Erro', 'Erro ao remover item.', 'error');
                                }
                            }, 'json');
                        }
                    });
                }
            }, {
                text: 'Editar',
                iconCls: 'icon-edit',
                handler: function() {
                    let item = $('#dgItens').datagrid('getSelected');
                    if (!item) {
                        $.messager.alert('Atenção', 'Selecione um item para editar.', 'warning');
                        return;
                    }
                    abrirFormularioItem('Editar Item', item);
                }
            }, {
                text: 'Cancelar',
                iconCls: 'icon-undo',
                handler: function() {
                    $('#dialogGerenciarItens').dialog('close');
                }
            }]
        });

        $('#dialogGerenciarItens').html('<table id="dgItens"></table>');

        $('#dgItens').datagrid({
            url: 'buscar_itens.php',
            columns: [
                [{
                        field: 'cod_item',
                        title: 'Código',
                        width: 100
                    },
                    {
                        field: 'den_item',
                        title: 'Nome do Item',
                        width: 200
                    }
                ]
            ],
            pagination: true,
            singleSelect: true,
            fitColumns: true
        });
    }

    function abrirFormularioItem(titulo, item = null) {
        if ($('#dialogItemForm').length) {
            $('#dialogItemForm').remove();
        }

        $('body').append(`
<div id="dialogItemForm" style="padding:10px">
    <form id="formItem">
        <div style="margin-bottom:10px">
            <input name="cod_item" class="easyui-textbox" label="Código:" style="width:100%" readonly>
        </div>
        <div style="margin-bottom:10px">
            <input name="den_item" class="easyui-textbox" label="Nome do Item:" style="width:100%" required>
        </div>
    </form>
</div>
`);

        $('#dialogItemForm').dialog({
            title: titulo,
            width: 400,
            height: 200,
            modal: true,
            buttons: [{
                text: 'Salvar',
                iconCls: 'icon-save',
                handler: function() {
                    // Define a URL diferente para adição ou edição
                    const url = item ? 'itens_gerenciar.php' : 'itens_adicionar.php';

                    $('#formItem').form('submit', {
                        url: url,
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(resposta) {
                            try {
                                let res = JSON.parse(resposta);
                                if (res === true) {
                                    $.messager.alert('Sucesso', 'Item salvo com sucesso.', 'info');
                                    $('#dialogItemForm').dialog('close');
                                    $('#dgItens').datagrid('reload');
                                } else {
                                    $.messager.alert('Erro', 'Erro ao salvar item.', 'error');
                                }
                            } catch (e) {
                                $.messager.alert('Erro', 'Resposta inválida do servidor.', 'error');
                            }
                        }
                    });
                }
            }, {
                text: 'Cancelar',
                iconCls: 'icon-cancel',
                handler: function() {
                    $('#dialogItemForm').dialog('close');
                }
            }]
        });

        if (item) {
            $('#formItem').form('load', item);
        } else {
            // Se for novo item, busca o próximo código
            $.getJSON('itens_adicionar.php', function(res) {
                if (res && res.cod_item) {
                    $('#formItem input[name="cod_item"]').val(res.cod_item);
                }
            });
        }
    }
</script>