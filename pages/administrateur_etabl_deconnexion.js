/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

// jQuery !
$(document).ready
(
  function()
  {

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Initialisation
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#url_deconnexion').focus();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Éviter une soumission d'un formulaire sans contrôle (appui sur la touche "entrée")
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_adresse').submit
    (
      function()
      {
        $('#bouton_valider').click();
        return false;
      }
    );

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Soumission
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_valider').click
    (
      function()
      {
        $("#bouton_valider").prop('disabled',true);
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        var url_deconnexion = $('#url_deconnexion').val();
        if( (url_deconnexion!='') && !testURL(url_deconnexion) )
        {
          $("#bouton_valider").prop('disabled',false);
          $('#ajax_msg').removeAttr("class").addClass("erreur").html("Adresse incorrecte !");
          $('#url_deconnexion').focus();
          return false;
        }
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&url_deconnexion='+encodeURIComponent(url_deconnexion),
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("#bouton_valider").prop('disabled',false);
              $('#ajax_msg').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $("#bouton_valider").prop('disabled',false);
              if(responseHTML!='ok')
              {
                $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_msg').removeAttr("class").addClass("valide").html("Valeur enregistrée !");
                // Il faut aussi modifier la valeur de cette variable js au cas où on cliquerait directement sur le bouton de déconnexion sans avoir changé de page
                DECONNEXION_REDIR = url_deconnexion;
              }
              return false;
            }
          }
        );
      }
    );

  }
);
