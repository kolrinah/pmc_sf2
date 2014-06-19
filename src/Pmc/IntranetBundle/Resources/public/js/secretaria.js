$(document).ready(function()
{  

} );
// FINAL DEL DOCUMENT READY

/*
 * AJAX para eliminar una Secretaria id
 */
function excluirSecretaria(id, paso)
{  
    if (paso === 1) // Paso 1: Pregunta Modal
    {
        $('#botonFechar').hide();
        $('#botoneraExcluir').show();
        $('#botonSim').attr('onclick', 'excluirSecretaria('+id+', 2);');
        $('#ventanaModal .modal-title').html('Pergunta');
        $('#ventanaModal p').html('Você tem certeza que deseja excluir a Secretaria?');
        $('#ventanaModal img').attr('src', $('#baseImg').val()+'pregunta.png' );
        $('#ventanaModal').modal('show');
        return false;    
    }
    // Paso 2: Eliminar
    $('#botonSim').attr('disabled', 'disabled');
    $('#ventanaModal .modal-title').html('Excluindo');
    $('#ventanaModal p').html('A Secretaria esta sendo excluída.<br/>Por favor aguarde...');
    $('#ventanaModal img').attr('src', $('#baseImg').val()+'preloader.gif' );
    $('#ventanaModal').modal('show');
    
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/excluirSecretaria',
         data: { 'id': id },         
         dataType:'json'})
      
     .done(function(data) {    
            if (typeof data.error === 'undefined') // OPERACION EXITOSA
            {
                $('#secretaria_'+id).hide();                
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
 * AJAX para seguir o dejar de seguir a un usuario
 */
function seguir(id, seguir)
{
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/seguir',
         data: { 'idUser': id,
                 'seguir': seguir },
         beforeSend:function(){ $('#usuario_'+id+' img').show();               
                                $('#seguir_'+id).attr('disabled', 'disabled');
                                $('#excluir_'+id).attr('disabled', 'disabled'); },        
         dataType:'json'})
      
     .done(function(data) {    
            if (typeof data.error === 'undefined') // OPERACION EXITOSA
            {
                if (seguir === 0)
                {   $('#excluir_'+id).hide();
                    $('#seguir_'+id).show();}
                else
                {   $('#excluir_'+id).show();
                    $('#seguir_'+id).hide();}    
            }   
            else
            { // ERROR DESCONOCIDO
              $('#ventanaModal .modal-title').html('Advertência');
              $('#ventanaModal p').html(traductor(data.error));
              $('#ventanaModal img').attr('src', $('#baseImg').val()+'alerta.png' );
              $('#ventanaModal').modal('show');        
            }        
       })         
       
     .always(function() { $('#usuario_'+id+' img').hide();
                          $('#seguir_'+id).removeAttr('disabled');
                          $('#excluir_'+id).removeAttr('disabled'); })
      
     .fail(function(jqXHR, textStatus, errorThrown) {            
            $('#ventanaModal .modal-title').html(traductor(textStatus));
            $('#ventanaModal p').html(traductor(errorThrown));
            $('#ventanaModal img').attr('src', $('#baseImg').val()+'error.png' );
            $('#ventanaModal').modal('show');         
        });
     return true;
}