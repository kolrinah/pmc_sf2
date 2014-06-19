$(document).ready(function()
{  

} );
// FINAL DEL DOCUMENT READY

/*
 * AJAX para eliminar un Servicio id
 */
function excluirServico(id, paso)
{  
    if (paso === 1) // Paso 1: Pregunta Modal
    {
        $('#botonFechar').hide();
        $('#botoneraExcluir').show();
        $('#botonSim').attr('onclick', 'excluirServico('+id+', 2);');
        $('#ventanaModal .modal-title').html('Pergunta');
        $('#ventanaModal p').html('Você tem certeza que deseja excluir a Serviço?');
        $('#ventanaModal img').attr('src', $('#baseImg').val()+'pregunta.png' );
        $('#ventanaModal').modal('show');
        return false;    
    }
    // Paso 2: Eliminar
    $('#botonSim').attr('disabled', 'disabled');
    $('#ventanaModal .modal-title').html('Excluindo');
    $('#ventanaModal p').html('O Serviço esta sendo excluído.<br/>Por favor aguarde...');
    $('#ventanaModal img').attr('src', $('#baseImg').val()+'preloader.gif' );
    $('#ventanaModal').modal('show');
    
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/excluirServico',
         data: { 'id': id },         
         dataType:'json'})
      
     .done(function(data) {    
            if (typeof data.error === 'undefined') // OPERACION EXITOSA
            {
                $('#servico_'+id).hide();                
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

// Ajax para cambiar los usuarios según la secretaría seleccionada
function mostrarFuncionarios()
{   
    var checkados = Array();
    var i = 0;
    // Guardamos los usuarios que estaban con atributo checked
    $('#form_responsavel input[type="checkbox"]').each(function(){
            if ( $(this).is(':checked') )
            {
               checkados[i] = $(this).val();
               i++;
            }            
    } );   
    
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/mostrarFuncionarios',
         data: { 'id': $('#form_secretaria').val(),
                 'checkados': checkados },
         beforeSend:function(){ $('#form_responsavel').html('');
                                $('#iconEspera').show(); }, 
         dataType:'json'})
         
     .done(function(data) { $('#form_responsavel').html(data.html).fadeIn(); })
     
     .always(function() { $('#iconEspera').hide(); })
      
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