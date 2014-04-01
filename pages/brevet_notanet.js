/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre GPL 3 <http://www.rodage.org/gpl-3.0.fr.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Générale Publique GNU pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Générale Publique GNU avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

// jQuery !
$(document).ready
(
  function()
  {

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Cocher / décocher par lot des élèves
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('q.cocher_tout').click
    (
      function()
      {
        var classe_id = $(this).parent().attr('id').substring(4); // for_
        cocher(classe_id,true);
      }
    );
    $('q.cocher_rien').click
    (
      function()
      {
        var classe_id = $(this).parent().attr('id').substring(4); // for_
        cocher(classe_id,false);
      }
    );
    
    function cocher(classe_id,etat)
    {
      $('#groupe_'+classe_id).find('label input:enabled').prop('checked',etat);
      $('#ajax_msg').removeAttr("class").html('');
      return false;
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Modification d'un checkbox => Retirer un message de confirmation ou d'erreur
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_accueil').on
    (
      'change',
      'input',
      function()
      {
        $('#ajax_msg').removeAttr("class").html('');
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Générer le fichier d'export
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#export_notanet').click
    (
      function()
      {
        // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
        var f_eleve = new Array(); $("#table_accueil input:enabled:checked").each(function(){f_eleve.push($(this).val());});
        if(!f_eleve.length)
        {
          $('#ajax_msg').removeAttr("class").addClass("erreur").html("Sélectionnez au moins un élève !");
          return false;
        }
        $('#export_notanet').prop('disabled',true);
        $('#ajax_msg').removeAttr("class").addClass("loader").html('En cours&hellip;');
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_eleve='+f_eleve,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#export_notanet').prop('disabled',false);
              $('#ajax_msg').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $('#export_notanet').prop('disabled',false);
              if(responseHTML.substring(0,14)!='export_notanet')
              {
                $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
                return false;
              }
              else
              {
                $('#ajax_msg').removeAttr("class").addClass("valide").html('Fichier généré.');
                $('#lien_notanet').attr('href','./force_download.php?fichier='+responseHTML);
                format_liens('#ajax_info');
                $.fancybox( { 'href':'#ajax_info' , onStart:function(){$('#ajax_info').css("display","block");} , onClosed:function(){$('#ajax_info').css("display","none");} , 'minWidth':600 , 'centerOnScroll':true } );
              }
            }
          }
        );
      }
    );

  }
);
