$(document).ready(function()
{   // Inicialización del Drag-and-Drop de los Banner
     $(function() {
         if (typeof $( "#publications" ) !== 'undefined')
         {
            $("#publications").sortable({
                     out: function( event, ui ) 
                    {
                        actualizaOrden();
                    }
                });
            $("#publications").sortable( "option", "cursor", "move" );
            $("#publications").disableSelection();
         }  
     });
        
} );
// FINAL DEL DOCUMENT READY

/*
 * AJAX para actualizar el orden de los Banner
 */
function actualizaOrden()
{
    var orden = new Array(), i = 0;    
    $('#publications div[data-orden]').each(function() {                
                orden[i] = $(this).attr('data-orden');                
                i++;
                     });
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/actualizaOrden',
         data: { 'orden': orden },
         beforeSend:function(){ $('img[id*="cargar_"]').show(); },        
         dataType:'json'})
      
     .done(function(data) {    
            if (typeof data.error === 'undefined') // OPERACION EXITOSA
                $('#banners').hide().html(data.html).fadeIn('slow');            
            else
            { // ERROR DESCONOCIDO
              $('#ventanaModal .modal-title').html('Advertência');
              $('#ventanaModal p').html(traductor(data.error));
              $('#ventanaModal img').attr('src', $('#baseImg').val()+'alerta.png' );
              $('#ventanaModal').modal('show');        
            }        
       })         
       
     .always(function() { $('img[id*="cargar_"]').hide(); })
      
     .fail(function(jqXHR, textStatus, errorThrown) {            
            $('#ventanaModal .modal-title').html(traductor(textStatus));
            $('#ventanaModal p').html(traductor(errorThrown));
            $('#ventanaModal img').attr('src', $('#baseImg').val()+'error.png' );
            $('#ventanaModal').modal('show');         
        });
     return true;
}

/*
 * AJAX para activar o inactivar Banner
 */
function activarBanner(id, activar)
{
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/activarBanner',
         data: { 'idBanner': id,
                 'activar': activar },
         beforeSend:function(){ $('#cargar_'+id).show();
                                $('#inactivo_'+id).hide();
                                $('#activo_'+id).hide(); },        
         dataType:'json'})
      
     .done(function(data) {    
            if (typeof data.error === 'undefined') // OPERACION EXITOSA
            {
                if (activar === 0)
                {   $('#activo_'+id).hide();
                    $('#inactivo_'+id).show();
                    $('#banner_'+id).addClass('inactivo');}
                else
                {   $('#activo_'+id).show();
                    $('#inactivo_'+id).hide();
                    $('#banner_'+id).removeClass('inactivo');}  
                 // Refresca el lateral derecho
                 $('#banners').hide().html(data.html).fadeIn('slow');
            }   
            else
            { // ERROR DESCONOCIDO
              $('#ventanaModal .modal-title').html('Advertência');
              $('#ventanaModal p').html(traductor(data.error));
              $('#ventanaModal img').attr('src', $('#baseImg').val()+'alerta.png' );
              $('#ventanaModal').modal('show');        
            }        
       })         
       
     .always(function() { $('#cargar_'+id).hide(); })
      
     .fail(function(jqXHR, textStatus, errorThrown) {            
            $('#ventanaModal .modal-title').html(traductor(textStatus));
            $('#ventanaModal p').html(traductor(errorThrown));
            $('#ventanaModal img').attr('src', $('#baseImg').val()+'error.png' );
            $('#ventanaModal').modal('show');         
        });
     return true;
}

/*
 * AJAX para eliminar un banner id
 */
function excluirBanner(id, paso)
{  
    if (paso === 1) // Paso 1: Pregunta Modal
    {
        $('#botonFechar').hide();
        $('#botoneraExcluir').show();
        $('#botonSim').attr('onclick', 'excluirBanner('+id+', 2);');
        $('#ventanaModal .modal-title').html('Pergunta');
        $('#ventanaModal p').html('Você tem certeza que deseja excluir a banner?');
        $('#ventanaModal img').attr('src', $('#baseImg').val()+'pregunta.png' );
        $('#ventanaModal').modal('show');
        return false;    
    }
    // Paso 2: Eliminar
    $('#botonSim').attr('disabled', 'disabled');
    $('#ventanaModal .modal-title').html('Excluindo');
    $('#ventanaModal p').html('O banner esta sendo excluído.<br/>Por favor aguarde...');
    $('#ventanaModal img').attr('src', $('#baseImg').val()+'preloader.gif' );
    $('#ventanaModal').modal('show');
    
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/excluirBanner',
         data: { 'idBanner': id },         
         dataType:'json'})
      
     .done(function(data) {    
            if (typeof data.error === 'undefined') // OPERACION EXITOSA
            {
                $('#banner_'+id).hide();                
                $('#ventanaModal').modal('hide');
                // Refresca el lateral derecho
                $('#banners').hide().html(data.html).fadeIn('slow');
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