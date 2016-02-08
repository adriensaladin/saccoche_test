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
    // Alerter au changement d'un élément de formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_select').on
    (
      'change',
      'select, input',
      function()
      {
        $('#ajax_msg').removeAttr('class').addClass('alerte').html("Pensez à valider vos modifications !");
      }
    );

    // Charger le select f_eleve en ajax

    function maj_eleve(groupe_id,groupe_type)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_eleves',
          data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_statut=1'+'&f_multiple=1'+'&f_selection=1',
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg').removeAttr('class').addClass('alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==true)
            {
              $('#ajax_msg').removeAttr('class').addClass('valide').html("Affichage actualisé !");
              $('#f_eleve').html(responseJSON['value']);
            }
            else
            {
              $('#ajax_msg').removeAttr('class').addClass('alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }
    function changer_groupe()
    {
      $("#f_eleve").html('');
      var groupe_val = $("#select_groupe").val();
      if(groupe_val)
      {
        // type = $("#select_groupe option:selected").parent().attr('label');
        if(PROFIL_TYPE=='professeur')
        {
          groupe_type = $("#select_groupe option:selected").parent().attr('label');
          groupe_id = groupe_val;
        }
        else
        {
          groupe_type = groupe_val.substring(0,1);
          groupe_id   = groupe_val.substring(1);
        }
        $('#ajax_msg').removeAttr('class').addClass('loader').html("En cours&hellip;");
        maj_eleve(groupe_id,groupe_type);
      }
      else
      {
        $('#ajax_msg').removeAttr('class').html("");
      }
    }
    $("#select_groupe").change
    (
      function()
      {
        changer_groupe();
      }
    );

    // Réagir au clic sur un bouton (soumission du formulaire)

    $('#associer').click
    (
      function()
      {
        var f_serie = $('#f_serie option:selected').val();
        if( !$("#f_eleve input:checked").length || !f_serie )
        {
          $('#ajax_msg').removeAttr('class').addClass('erreur').html("Sélectionnez dans les deux listes !");
          return false;
        }
        $('#form_select button').prop('disabled',true);
        $('#ajax_msg').removeAttr('class').addClass('loader').html("En cours&hellip;");
        // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
        var tab_eleve = new Array();
        $("#f_eleve input:checked").each
        (
          function()
          {
            tab_eleve.push($(this).val());
          }
        );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=associer'+'&f_serie='+f_serie+'&f_eleve='+tab_eleve,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#form_select button').prop('disabled',false);
              $('#ajax_msg').removeAttr('class').addClass('alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#form_select button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg').removeAttr('class').addClass('alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_msg').removeAttr('class').addClass('valide').html("Demande réalisée !");
                $('#bilan').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

    // Initialisation : charger au chargement l'affichage du bilan

    $('#ajax_msg').addClass('loader').html("En cours&hellip;");
    $.ajax
    (
      {
        type : 'POST',
        url : 'ajax.php?page='+PAGE,
        data : 'csrf='+CSRF+'&f_action=initialiser',
        dataType : 'json',
        error : function(jqXHR, textStatus, errorThrown)
        {
          $('#ajax_msg').removeAttr('class').addClass('alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          return false;
        },
        success : function(responseJSON)
        {
          initialiser_compteur();
          if(responseJSON['statut']==false)
          {
            $('#ajax_msg').removeAttr('class').addClass('alerte').html(responseJSON['value']);
          }
          else
          {
            $('#ajax_msg').removeAttr('class').html("");
            $('#bilan').html(responseJSON['value']);
          }
        }
      }
    );

  }
);
