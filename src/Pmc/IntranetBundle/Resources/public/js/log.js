$(document).ready(function()
{     
    $('#log').dataTable( {
	"sPaginationType": "full_numbers",
        "aaSorting": [[ 0, "desc" ]],
        "oLanguage": {
                      "sProcessing": "Processamento...",
                      "sLengthMenu": "Mostrar _MENU_ Registros por página",
                      "sZeroRecords": "Não conicidencias foram encontradaos.",
                      "sInfo": "Mostrando _START_ a _END_ de _TOTAL_ Registros",
                      "sInfoEmpty": "Sem Registros",
                      "sInfoFiltered": "(Quantidade de registros filtrados: _MAX_)",
                      "sInfoPostFix": "",
                      "sSearch": "Busca:",                      
	              "oPaginate": {
	                	"sFirst":    "Primeiro",
	                	"sPrevious": "Anterior",
	                	"sNext":     "Seguinte",
	                	"sLast":     "Último"	} }
			} );
                        
   // $('.row').removeClass('oculto');    
    //$('select[aria-controls]').addClass("span1");                
} );
// FINAL DEL DOCUMENT READY