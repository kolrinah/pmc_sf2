$(document).ready(function()
{   /*    
    $('#proyectos').dataTable( {
	"sPaginationType": "full_numbers",
        "aaSorting": [[ 0, "asc" ],[ 1, "asc" ]],
        "oLanguage": {
                      "sProcessing": "Procesando...",
                      "sLengthMenu": "Mostrar _MENU_ Registros por página",
                      "sZeroRecords": "No se hallaron conicidencias.",
                      "sInfo": "Mostrando _START_ a _END_ de _TOTAL_ Registros",
                      "sInfoEmpty": "Ningún Registro",
                      "sInfoFiltered": "(Total de Registros Filtrados: _MAX_)",
                      "sInfoPostFix": "",
                      "sSearch": "Buscar:",                      
	              "oPaginate": {
	                	"sFirst":    "Primero",
	                	"sPrevious": "Anterior",
	                	"sNext":     "Siguiente",
	                	"sLast":     "Último"	} }
			} );
                        
    $('.row').removeClass('oculto');    
    $('select[aria-controls]').addClass("span1");
    */    
       
     // Ajustes del widget JQueryUI DatePicker  
     if ( $('input[type=text][id*="form_data"]').length > 0 )
     {  datar($('input[type=text][id*="form_data"]'));  
        $('div#ui-datepicker-div.ui-widget').css( "font-size", "0.9em" ); 
     }
} );
// FINAL DEL DOCUMENT READY

/*
 * AJAX para postear un comentario en una publicación id
 */
function postarComentario(id)
{  
    var postar = trim($('#postar_'+id+' textarea').val());
    if ( postar ==='' ) return false;   
      
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/postarComentario',
         data: { 'postar': postar,
                 'idPublicacao': id },
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
 * AJAX PARA REFRESCAR LOS COMENTAROS DE UNA PUBLICACION id
 */
function refrescarComentarios(id)
{   
    $('#postar_'+id+' textarea').val('');  
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/refrescarComentarios',
         data: { 'idPublicacao': id },         
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
         url:  $('#baseURL').val()+'ajax/excluirComentario',
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
 * AJAX para eliminar una publicación id
 */
function excluirPublicacao(id, paso)
{  
    if (paso === 1) // Paso 1: Pregunta Modal
    {
        $('#botonFechar').hide();
        $('#botoneraExcluir').show();
        $('#botonSim').attr('onclick', 'excluirPublicacao('+id+', 2);');
        $('#ventanaModal .modal-title').html('Pergunta');
        $('#ventanaModal p').html('Você tem certeza que deseja excluir a publicação?');
        $('#ventanaModal img').attr('src', $('#baseImg').val()+'pregunta.png' );
        $('#ventanaModal').modal('show');
        return false;    
    }
    // Paso 2: Eliminar
    $('#botonSim').attr('disabled', 'disabled');
    $('#ventanaModal .modal-title').html('Excluindo');
    $('#ventanaModal p').html('Sua publicação esta sendo excluída.<br/>Por favor aguarde...');
    $('#ventanaModal img').attr('src', $('#baseImg').val()+'preloader.gif' );
    $('#ventanaModal').modal('show');
    
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/excluirPublicacao',
         data: { 'idPublicacao': id },         
         dataType:'json'})
      
     .done(function(data) {    
            if (typeof data.error === 'undefined') // OPERACION EXITOSA
            {
                $('#publicacao_'+data.id).html(data.html);
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
 * AJAX para postear una Publicación
 */
function postarPublicacao(tipo)
{  
    var postar = trim($('#postar_'+id+' textarea').val());
    if ( postar ==='' ) return false;   
      
    $.ajax({
         type: 'POST',
         url:  $('#baseURL').val()+'ajax/postarComentario',
         data: { 'postar': postar,
                 'idPublicacao': id },
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

function datar(elemento)
{
  elemento.datepicker({
            showOn: "both",
            buttonImage: $('#baseImg').val()+"cal.gif",
            buttonText:"clique para Selecionar",
            buttonImageOnly: true,
            showOtherMonths: true,
            selectOtherMonths: true,
            dateFormat:"dd/mm/yy",
            currentText:"Hoje",
            nextText:"Seg",
            //prevText:"Ant",
            defaultDate:elemento.val(),
            minDate:_diaHoy(),
            maxDate:$('#fechaFin').val(),
            dayNames:[ "Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado" ],
            dayNamesMin:[ "Do", "Se", "Te", "Qu", "Qu", "Se", "Sá" ],
            monthNames:["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"],
            onSelect: function( selectedDate ) {
                elemento.datepicker( "option", "minDate", selectedDate )}            
        });
}