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

    var profil = '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Alerter sur la nécessité de valider
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('select').change
    (
      function()
      {
        profil = $(this).attr('id').substr(8); // f_delai_XXX
        $('#ajax_msg_'+profil).removeAttr('class').addClass('alerte').html("Pensez à valider !");
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Validation du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('button[class=parametre]').click
    (
      function()
      {
        profil = $(this).attr('id').substr(15); // bouton_valider_XXX
        var delai = $('#f_delai_'+profil+' option:selected').val();
        $('#bouton_valider_'+profil).prop('disabled',true);
        $('#ajax_msg_'+profil).removeAttr('class').addClass('loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_profil='+profil+'&f_delai='+delai,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#bouton_valider_'+profil).prop('disabled',false);
              $('#ajax_msg_'+profil).removeAttr('class').addClass('alerte').html("Échec de la connexion !");
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $('#bouton_valider_'+profil).prop('disabled',false);
              if(responseHTML!='ok')
              {
                $('#ajax_msg_'+profil).removeAttr('class').addClass('alerte').html(responseHTML);
              }
              else
              {
                if(profil=='ALL')
                {
                  $('select option[value='+delai+']').prop('selected',true);
                  $('label[id^=ajax_msg_]').removeAttr('class').html("&nbsp;");
                }
                $('#ajax_msg_'+profil).removeAttr('class').addClass('valide').html("Valeur enregistrée !");
                if( (profil=='ALL') || (profil=='ADM') )
                {
                  DUREE_AUTORISEE = delai;
                }
                initialiser_compteur();
              }
            }
          }
        );
      }
    );

  }
);
