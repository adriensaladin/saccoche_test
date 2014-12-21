/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
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
    // Variables globales
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var memo_eleve_info = '';
    var memo_classe_id  = 0;
    var memo_eleve_id   = 0;
    var memo_serie_ref  = '';
    var memo_eleve_info_first = $('#go_selection_eleve option:first').val();
    var memo_eleve_info_last  = $('#go_selection_eleve option:last').val();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Navigation d'un élève à un autre
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function charger_nouvel_eleve(eleve_info)
    {
      memo_eleve_info = eleve_info;
      tab_id = eleve_info.split('_');
      memo_classe_id = tab_id[0];
      memo_eleve_id  = tab_id[1];
      memo_serie_ref = tab_id[2];
      $('#form_choix_eleve button , #form_choix_eleve select').prop('disabled',true);
      $('#ajax_msg').removeAttr("class").addClass("loader").html('En cours&hellip;');
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action='+'proposer'+'&f_classe='+memo_classe_id+'&f_user='+memo_eleve_id+'&f_serie='+memo_serie_ref,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#form_choix_eleve button , #form_choix_eleve select').prop('disabled',false);
            $('#valider_notes').prop('disabled',true);
            $('#ajax_msg').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
            return false;
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            $('#form_choix_eleve button , #form_choix_eleve select').prop('disabled',false);
            if(responseHTML.substring(0,8)!='<tr><td>')
            {
              $('#valider_notes').prop('disabled',true);
              $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
              return false;
            }
            else
            {
              $('#go_selection_eleve option[value='+eleve_info+']').prop('selected',true);
              masquer_element_navigation_choix_eleve();
              $('#ajax_msg').removeAttr("class").html('');
              $('#zone_resultat_eleve h3').html( $('#m_'+eleve_info).children('img').attr('title') );
              $('#zone_resultat_eleve table tbody').html(responseHTML);
            }
          }
        }
      );
    }

    function masquer_element_navigation_choix_eleve()
    {
      $('#form_choix_eleve button').css('visibility','visible');
      if(memo_eleve_info==memo_eleve_info_first)
      {
        $('#go_premier_eleve , #go_precedent_eleve').css('visibility','hidden');
      }
      if(memo_eleve_info==memo_eleve_info_last)
      {
        $('#go_dernier_eleve , #go_suivant_eleve').css('visibility','hidden');
      }
    }

    $('#zone_action_eleve').on
    (
      'click',
      '#go_premier_eleve',
      function()
      {
        var eleve_info = $('#go_selection_eleve option:first').val();
        charger_nouvel_eleve(eleve_info);
      }
    );

    $('#zone_action_eleve').on
    (
      'click',
      '#go_dernier_eleve',
      function()
      {
        var eleve_info = $('#go_selection_eleve option:last').val();
        charger_nouvel_eleve(eleve_info);
      }
    );

    $('#zone_action_eleve').on
    (
      'click',
      '#go_precedent_eleve',
      function()
      {
        if( $('#go_selection_eleve option:selected').prev().length )
        {
          var eleve_info = $('#go_selection_eleve option:selected').prev().val();
          charger_nouvel_eleve(eleve_info);
        }
      }
    );

    $('#zone_action_eleve').on
    (
      'click',
      '#go_suivant_eleve',
      function()
      {
        if( $('#go_selection_eleve option:selected').next().length )
        {
          var eleve_info = $('#go_selection_eleve option:selected').next().val();
          charger_nouvel_eleve(eleve_info);
        }
      }
    );

    $('#zone_action_eleve').on
    (
      'change',
      '#go_selection_eleve',
      function()
      {
        var eleve_info = $('#go_selection_eleve option:selected').val();
        charger_nouvel_eleve(eleve_info);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur un nom d'élève => Charger les données à traiter
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_accueil').on
    (
      'click',
      'a',
      function()
      {
        var eleve_info = $(this).attr('id').substring(2); // m_
        $('#table_accueil').hide(0);
        $('#zone_action_eleve').show(0);
        charger_nouvel_eleve(eleve_info);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour fermer la zone action_eleve
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      '#fermer_zone_action_eleve',
      function()
      {
        $('#ajax_msg').removeAttr("class").html('');
        $('#zone_resultat_eleve table tbody').html('<tr><td colspan="4"></td></tr>');
        $('#zone_action_eleve').hide(0);
        $('#table_accueil').show(0);
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur une checkbox de référentiel => Reporter la note proposée
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_resultat_eleve').on
    (
      'click',
      'input[type=radio]',
      function()
      {
        var epreuve_id    = $(this).attr('name').substring(6); // check_
        var note_reportee = $(this).next('i').html();
        if(note_reportee)
        {
          $('#note_'+epreuve_id+' option[value='+note_reportee+']').prop('selected',true);
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Modification d'un select de référentiel => Alerter sur la nécessiter de valider
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_resultat_eleve').on
    (
      'change',
      'select',
      function()
      {
        $('#ajax_msg').removeAttr("class").addClass("alerte").html("Pensez à valider vos modifications !");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Valider les notes d'un élève
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_action_eleve').on
    (
      'click',
      '#valider_notes',
      function()
      {
        $('#form_choix_eleve button , #form_choix_eleve select').prop('disabled',true);
        $('#ajax_msg').removeAttr("class").addClass("loader").html('En cours&hellip;');
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+'enregistrer'+'&f_classe='+memo_classe_id+'&f_user='+memo_eleve_id+'&f_serie='+memo_serie_ref+'&'+$('#zone_resultat_eleve').serialize(),
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#form_choix_eleve button , #form_choix_eleve select').prop('disabled',false);
              $('#ajax_msg').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $('#form_choix_eleve button , #form_choix_eleve select').prop('disabled',false);
              if(responseHTML.substring(0,3)!='<td')
              {
                $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
                return false;
              }
              else
              {
                $('#ajax_msg').removeAttr("class").addClass("valide").html('Notes enregistrées.');
                $('#m_'+memo_eleve_info).children('label').removeAttr("class").addClass("valide");
                var tab_td = responseHTML.split('¤');
                var numero_ligne = 0;
                $('#zone_resultat_eleve table tbody tr').each
                (
                  function()
                  {
                    $(this).children('td:last,th:last').replaceWith(tab_td[numero_ligne]);
                    numero_ligne++;
                  }
                );
              }
            }
          }
        );
      }
    );

  }
);
