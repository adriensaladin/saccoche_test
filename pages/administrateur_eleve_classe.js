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
    // Alerter au changement d'un élément de formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_select').on
    (
      'change',
      'select, input',
      function()
      {
        $('#ajax_msg').removeAttr("class").addClass("alerte").html("Pensez à valider vos modifications !");
      }
    );

    // Charger le select f_eleve en ajax

    function maj_eleve(groupe_id,groupe_type,select_eleve)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_eleves',
          data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_statut=1'+'&f_multiple=1'+'&f_selection='+select_eleve,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            if(responseHTML.substring(0,6)=='<label')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#ajax_msg').removeAttr("class").addClass("valide").html("Affichage actualisé !");
              $('#f_eleve').html(responseHTML);
            }
            else
            {
              $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
            }
          }
        }
      );
    }
    function changer_groupe(memo_check)
    {
      var select_eleve = 0;
      if(memo_check)
      {
        select_eleve = new Array(); $("#f_eleve input:checked").each(function(){select_eleve.push($(this).val());});
      }
      $("#f_eleve").html('');
      var groupe_val = $("#select_groupe").val();
      if(groupe_val)
      {
        groupe_type = groupe_val.substring(0,1);
        groupe_id   = groupe_val.substring(1);
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        maj_eleve(groupe_id,groupe_type,select_eleve);
      }
      else
      {
        $('#ajax_msg').removeAttr("class").html("&nbsp;");
      }
    }
    $("#select_groupe").change
    (
      function()
      {
        changer_groupe(false);
      }
    );

    // Réagir au clic sur un bouton (soumission du formulaire)

    $('#ajouter , #retirer').click
    (
      function()
      {
        id = $(this).attr('id');
        if( !$("#f_eleve input:checked").length || !$("#f_classe input:checked").length )
        {
          $('#ajax_msg').removeAttr("class").addClass("erreur").html("Sélectionnez dans les deux listes !");
          return false;
        }
        $('#form_select button').prop('disabled',true);
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE+'&action='+id,
            data : 'csrf='+CSRF+'&'+$('#form_select').serialize(),
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#form_select button').prop('disabled',false);
              $('#ajax_msg').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $('#form_select button').prop('disabled',false);
              if(responseHTML.substring(0,6)!='<hr />')
              {
                $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
                $('#bilan').html(responseHTML);
                changer_groupe(true);
              }
            }
          }
        );
      }
    );

    // Initialisation : charger au chargement l'affichage du bilan

    $('#ajax_msg').addClass("loader").html("En cours&hellip;");
    $.ajax
    (
      {
        type : 'POST',
        url : 'ajax.php?page='+PAGE+'&action=initialiser',
        data : 'csrf='+CSRF,
        dataType : "html",
        error : function(jqXHR, textStatus, errorThrown)
        {
          $('#ajax_msg').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
          return false;
        },
        success : function(responseHTML)
        {
          initialiser_compteur();
          if(responseHTML.substring(0,6)!='<hr />')
          {
            $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
          }
          else
          {
            $('#ajax_msg').removeAttr("class").html("&nbsp;");
            $('#bilan').html(responseHTML);
          }
        }
      }
    );

  }
);
