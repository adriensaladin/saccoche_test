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
// Afficher / Masquer le formulaire de jointure aux périodes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('select[name=f_periode]').change
    (
      function()
      {
        if( $(this).val() )
        {
          $(this).next().show();
        }
        else
        {
          $(this).next().hide();
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Alerter sur la nécessité de valider
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('select').change
    (
      function()
      {
        var groupe_id = $(this).parent().data('id');
        $(this).parent().next('td').html('<button type="button" class="valider">Valider.</button><label class="alerte">Pensez à enregistrer !</label>');
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Soumission du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'click',
      'button.valider',
      function()
      {
        var obj_bouton = $(this);
        var obj_label  = $(this).next('label');
        var obj_td     = $(this).parent().prev('td');
        var groupe_id  = obj_td.data('id');
        var f_periode  = obj_td.children('select[name=f_periode]' ).val();
        var f_jointure = obj_td.children('select[name=f_jointure]').val();
        var f_cycle    = obj_td.children('select[name=f_cycle]'   ).val();
        var f_college  = obj_td.children('select[name=f_college]' ).val();
        obj_bouton.prop('disabled',true);
        obj_label.removeAttr('class').addClass('loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_groupe='+groupe_id+'&f_periode='+f_periode+'&f_jointure='+f_jointure+'&f_cycle='+f_cycle+'&f_college='+f_college,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              obj_bouton.prop('disabled',false);
              obj_label.removeAttr('class').addClass('alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              obj_bouton.prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                obj_label.removeAttr('class').addClass('alerte').html(responseJSON['value']);
              }
              else
              {
                obj_bouton.remove();
                obj_label.removeAttr('class').addClass('valide').html("Choix enregistrés !").fadeOut( 2000, function() { $(this).remove(); } );
                var td_class = ( f_periode || f_cycle || f_college ) ? 'bv' : 'bj' ;
                obj_td.prev('td').removeAttr('class').addClass(td_class);
              }
            }
          }
        );
      }
    );

  }
);
