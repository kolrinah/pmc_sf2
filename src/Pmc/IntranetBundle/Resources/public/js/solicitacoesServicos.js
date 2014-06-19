$(document).ready(function()
{ 
} );
// FINAL DEL DOCUMENT READY

/*
 * AJAX para Finalizar una solicitud
 */
function finalizarSolicitacao(id)
{
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/finalizarSolicitacao',
         data: { 'id': id },
         beforeSend:function(){ $('#botonFinalizar_'+id+' img').show();
                                $('#botonFinalizar_'+id+' button').attr('disabled','disabled'); },        
         dataType:'json'})
      
     .done(function(data) {    
            if (typeof data.error === 'undefined') // OPERACION EXITOSA               
               $('#botonFinalizar_'+id).html(data.html);
            else
            { // ERROR DESCONOCIDO                                    
              $('#ventanaModal .modal-title').html('Advertência');
              $('#ventanaModal p').html(traductor(data.error));
              $('#ventanaModal img').attr('src', $('#baseImg').val()+'alerta.png' );
              $('#ventanaModal').modal('show');        
            }        
       })         
       
     .always(function() { $('#botonFinalizar_'+id+' img').hide();
                          $('#botonFinalizar_'+id+' button').removeAttr('disabled'); })
      
     .fail(function(jqXHR, textStatus, errorThrown) {                      
            $('#ventanaModal .modal-title').html(traductor(textStatus));
            $('#ventanaModal p').html(traductor(errorThrown));
            $('#ventanaModal img').attr('src', $('#baseImg').val()+'error.png' );
            $('#ventanaModal').modal('show');         
        });
     return true;    
}

/*
 * AJAX para postear un comentario en una solicitud id
 */
function postarComentario(id)
{  
    var postar = trim($('#postar_'+id+' textarea').val());
    if ( postar ==='' ) return false;   
      
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/postarComentarioSolicitacao',
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
 * AJAX PARA REFRESCAR LOS COMENTAROS DE UNA SOLICITUD id
 */
function refrescarComentarios(id)
{   
    $('#postar_'+id+' textarea').val('');  
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/refrescarComentariosSolicitacao',
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
         url:  $('#baseURL').val()+'ajax/excluirComentarioSolicitacao',
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
