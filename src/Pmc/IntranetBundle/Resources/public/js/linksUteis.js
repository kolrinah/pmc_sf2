$(document).ready(function()
{  
} );
// FINAL DEL DOCUMENT READY

/*
 * AJAX para eliminar un Link id
 */
function excluirLinkUteis(id, paso)
{  
    if (paso === 1) // Paso 1: Pregunta Modal
    {
        $('#botonFechar').hide();
        $('#botoneraExcluir').show();
        $('#botonSim').attr('onclick', 'excluirLinkUteis('+id+', 2);');
        $('#ventanaModal .modal-title').html('Pergunta');
        $('#ventanaModal p').html('Você tem certeza que deseja excluir a Link?');
        $('#ventanaModal img').attr('src', $('#baseImg').val()+'pregunta.png' );
        $('#ventanaModal').modal('show');
        return false;    
    }
    // Paso 2: Eliminar
    $('#botonSim').attr('disabled', 'disabled');
    $('#ventanaModal .modal-title').html('Excluindo');
    $('#ventanaModal p').html('O Link esta sendo excluído.<br/>Por favor aguarde...');
    $('#ventanaModal img').attr('src', $('#baseImg').val()+'preloader.gif' );
    $('#ventanaModal').modal('show');
    
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/excluirLinkUteis',
         data: { 'idLink': id },         
         dataType:'json'})
      
     .done(function(data) {    
            if (typeof data.error === 'undefined') // OPERACION EXITOSA
            {
                $('#linkUteis_'+id).hide();                
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