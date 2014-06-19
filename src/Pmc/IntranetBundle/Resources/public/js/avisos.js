$(document).ready(function()
{  
    $(".voltarTopo").hide();
        $(function () {
            $(window).scroll(function () {
            if ($(this).scrollTop() > 300) {
                $('.voltarTopo').fadeIn();
            } else {
                $('.voltarTopo').fadeOut();
            }
        });
        $('.voltarTopo').click(function() {
            $('body,html').animate({scrollTop:0},600);
        }); 
    });    
    
    $(function() {
          $('select[id*="form_destinatarios"] > option').each(function(){
               if (!$(this).is(':selected')) $(this).remove();
          });
          autocompletar($('#para > input[type="text"]'));
    });
} );
// FINAL DEL DOCUMENT READY

function autocompletar(elemento)
{  
     elemento.autocomplete({
     minLength:0, //Indicamos que busque a partir de haber escrito uno o mas caracteres en el input
     delay:0,
     position:{my: "left top", at: "left bottom", collision: "none"},
     source: function(request, response)
             {
                var url=$('#baseURL').val()+'ajax/autocompletarUsuario';  //url donde buscará la lista
                $.post(url,{'frase':request.term,}, response, 'json');
             },
     /*focus: function( event, ui ) 
            {
                $("#para").append( ui.item.nome );                
                return false;
            },               */      
     select: function( event, ui ) 
             {                  
                $("#para > div").append(destino(ui.item) );
                $("#para > input").val('');
                $("#user_"+ui.item.id).popover({html:true,
                                             content: function(){
                                    return $('#popo_'+ui.item.id).html();
                 }});
                $("#user_"+ui.item.id).bind('close.bs.alert', function (){
                  $('.popover').remove();
                })
                return false;
             }
     }).data( "ui-autocomplete" )._renderItem = function( ul, item) {
                return $( "<li></li>" )
                .data( "item.autocomplete", item )
		.append( boxUsuario(item) )
                .appendTo( ul );
	  };
}

function boxUsuario(item)
{
    var caja; var img; var foto;
    
    foto = (item.foto !== null)? 'upload/retratos/' + item.foto:
                                'bundles/pmcintranet/img/ico-user.png';
    img = $('#baseURL').val() + foto;
    caja = '<a>'+'<div><img src="' + img + 
            '" align="left" style="height:45px; padding-right:5px"/>' +
           '<span style="font-size:0.9em; margin:0px;">'+ item.nome + '</span><br/>'+
           '<span style="font-size:0.8em; margin:0px; color: green;">'+
            item.secretaria + '</span></div></a>';
    return caja;
}

function destino(item)
{
    var caja; var img; var foto; var ancho; var popo; var check;
    
    foto = (item.foto !== null)? 'upload/retratos/' + item.foto:
                                'bundles/pmcintranet/img/ico-user.png';
    img = $('#baseURL').val() + foto;
    ancho = (item.nome !== null)? (item.nome.length)*10 : 20;
    popo = '<div id="popo_'+item.id+'" style="display:none">'+ boxUsuario(item) + '</div>';
    check = '<input type="checkbox" name="form[destinatarios][][usuario]" checked="checked" '+
            'style="display:none" value="'+item.id+'" />';
    
    caja = '<div id="user_'+item.id+'" class="btn alert alert-warning alert-dismissable" '+
           'style="margin:2px; padding:5px; max-width:'+ancho+'px; display:inline-block" '+
           ' data-toggle="popover" data-placement="bottom" '+           
           'data-trigger="hover" data-content="" >'+
           '<button type="button" class="close" data-dismiss="alert" '+           
           'style="right:0px; padding-left: 5px" '+
           'aria-hidden="true">&times;</button>'+
           item.nome +popo+ check +
           '</div>';
    return caja;    
}

/*
 * AJAX para postear un comentario en un Aviso id
 */
function postarComentario(id)
{  
    var postar = trim($('#postar_'+id+' textarea').val());
    if ( postar ==='' ) return false;   
      
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/postarComentarioAviso',
         data: { 'postar': postar,
                 'id': id },
         beforeSend:function(){ $('#postar_'+id+' img').show();
                                $('#postar_'+id+' button').attr('disabled','disabled'); },        
         dataType:'json'})
      
     .done(function(data) {    
            if (typeof data.error === 'undefined') // OPERACION EXITOSA
               refrescarComentarios(id);
            else
            { // ERROR DESCONOCIDO                                    
              $('#ventanaModal .modal-title').html('Advertência');
              $('#ventanaModal p').html(traductor(data.error));
              $('#ventanaModal img').attr('src', $('#baseImg').val()+'alerta.png' );
              $('#ventanaModal').modal('show');        
            }        
       })         
       
     .always(function() { $('#postar_'+id+' img').hide();
                          $('#postar_'+id+' button').removeAttr('disabled'); })
      
     .fail(function(jqXHR, textStatus, errorThrown) {                      
            $('#ventanaModal .modal-title').html(traductor(textStatus));
            $('#ventanaModal p').html(traductor(errorThrown));
            $('#ventanaModal img').attr('src', $('#baseImg').val()+'error.png' );
            $('#ventanaModal').modal('show');         
        });
     return true;
}

/*
 * AJAX PARA REFRESCAR LOS COMENTAROS DE UN AVISO id
 */
function refrescarComentarios(id)
{   
    $('#postar_'+id+' textarea').val('');  
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/refrescarComentariosAviso',
         data: { 'id': id },         
         dataType:'html'})
      
     .done(function(data) { $('#comentarios_'+id).hide();
                            $('#comentarios_'+id).html(data).fadeIn('slow'); })
      
     .fail(function(jqXHR, textStatus, errorThrown) {
            $('#botonFechar').show();
            $('#botoneraExcluir').hide();
            $('#ventanaModal .modal-title').html(traductor(textStatus));
            $('#ventanaModal p').html(traductor(errorThrown));
            $('#ventanaModal img').attr('src', $('#baseImg').val()+'error.png' );
            $('#ventanaModal').modal('show');         
        });
     return true;    
}

/*
 * AJAX para eliminar un comentario id
 */
function excluirComentario(id)
{  
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/excluirComentarioAviso',
         data: { 'idComentario': id },
         beforeSend:function(){ $('#comentario_'+id+' img').show();
                                $('#comentario_'+id+' span.glyphicon').hide(); },        
         dataType:'json'})
      
     .done(function(data) {    
            if (typeof data.error === 'undefined') // OPERACION EXITOSA
            {
                $('#comentarios_'+data.id).hide();
                            $('#comentarios_'+data.id).html(data.html).fadeIn('slow');
            }   
            else
            { // ERROR DESCONOCIDO              
              $('#comentario_'+id+' span.glyphicon').show();
              $('#botonFechar').show();
              $('#botoneraExcluir').hide();
              $('#ventanaModal .modal-title').html('Advertência');
              $('#ventanaModal p').html(traductor(data.error));
              $('#ventanaModal img').attr('src', $('#baseImg').val()+'alerta.png' );
              $('#ventanaModal').modal('show');        
            }        
       })         
       
     .always(function() { $('#postar_'+id+' img').hide();
                          $('#postar_'+id+' button').removeAttr('disabled'); })
      
     .fail(function(jqXHR, textStatus, errorThrown) {                      
            $('#botonFechar').show();
            $('#botoneraExcluir').hide();
            $('#ventanaModal .modal-title').html(traductor(textStatus));
            $('#ventanaModal p').html(traductor(errorThrown));
            $('#ventanaModal img').attr('src', $('#baseImg').val()+'error.png' );
            $('#ventanaModal').modal('show');         
        });
     return true;
}

/*
 * AJAX para eliminar un Aviso id
 */
function excluirAviso(id, paso)
{  
    if (paso === 1) // Paso 1: Pregunta Modal
    {
        $('#botonFechar').hide();
        $('#botoneraExcluir').show();
        $('#botonSim').attr('onclick', 'excluirAviso('+id+', 2);');
        $('#ventanaModal .modal-title').html('Pergunta');
        $('#ventanaModal p').html('Você tem certeza que deseja excluir o Aviso?');
        $('#ventanaModal img').attr('src', $('#baseImg').val()+'pregunta.png' );
        $('#ventanaModal').modal('show');
        return false;    
    }
    // Paso 2: Eliminar
    $('#botonSim').attr('disabled', 'disabled');
    $('#ventanaModal .modal-title').html('Excluindo');
    $('#ventanaModal p').html('O Aviso está sendo excluído.<br/>Por favor aguarde...');
    $('#ventanaModal img').attr('src', $('#baseImg').val()+'preloader.gif' );
    $('#ventanaModal').modal('show');
    
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/excluirAviso',
         data: { 'id': id },         
         dataType:'json'})
      
     .done(function(data) {    
            if (typeof data.error === 'undefined') // OPERACION EXITOSA
            {
                $('#aviso_'+id).remove();                
                $('#ventanaModal').modal('hide');
            }   
            else
            { // ERROR DESCONOCIDO 
              $('#botonFechar').show();
              $('#botoneraExcluir').hide();               
              $('#ventanaModal .modal-title').html('Advertência');
              $('#ventanaModal p').html(traductor(data.error));
              $('#ventanaModal img').attr('src', $('#baseImg').val()+'alerta.png' );
              $('#ventanaModal').modal('show');        
            }        
       })         
       
     .always(function() { $('#botonSim').removeAttr('disabled'); })
      
     .fail(function(jqXHR, textStatus, errorThrown) {            
            $('#botonFechar').show();
            $('#botoneraExcluir').hide();
            $('#ventanaModal .modal-title').html(traductor(textStatus));
            $('#ventanaModal p').html(traductor(errorThrown));
            $('#ventanaModal img').attr('src', $('#baseImg').val()+'error.png' );
            $('#ventanaModal').modal('show');         
        });
     return true;
}

/*
 * AJAX para marcar aviso como leido
 */
function lerAviso(id)
{   
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/lerAviso',
         data: { 'id': id },     
         dataType:'json'})
      
     .done(function(data) {    
            if (typeof data.error === 'undefined') // OPERACION EXITOSA
            {               
               $('#leer'+id+' img').attr('src', $('#baseImg').val()+'avisoAberto.png' ).fadeIn('slow');
               $('#leer'+id).attr('title', 'Aviso lido');
            }   
            else
            { // ERROR DESCONOCIDO
              $('#ventanaModal .modal-title').html('Advertência');
              $('#ventanaModal p').html(traductor(data.error));
              $('#ventanaModal img').attr('src', $('#baseImg').val()+'alerta.png' );
              $('#ventanaModal').modal('show');        
            }        
     })         
     
     .fail(function(jqXHR, textStatus, errorThrown) {            
            $('#ventanaModal .modal-title').html(traductor(textStatus));
            $('#ventanaModal p').html(traductor(errorThrown));
            $('#ventanaModal img').attr('src', $('#baseImg').val()+'error.png' );
            $('#ventanaModal').modal('show');         
        });
     return true;
}