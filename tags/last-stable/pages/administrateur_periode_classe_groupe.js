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

    // Réagir au clic sur une image pour reporter des dates

    $('#bilan').on
    (
      'click',
      'q.date_ajouter',
      function()
      {
        texte = $(this).parent().html();
        $("#f_date_debut").val( texte.substring(0,10) );
        $("#f_date_fin").val(  texte.substring(13,23) );
        return false;
      }
    );

    // Réagir au clic sur un bouton (soumission du formulaire)

    $('#ajouter , #retirer').click
    (
      function()
      {
        id = $(this).attr('id');
        if( $("#select_periodes input:checked").length==0 || $("#select_classes_groupes input:checked").length==0 )
        {
          $('#ajax_msg').removeAttr("class").addClass("erreur").html("Sélectionnez dans les deux listes !");
          return(false);
        }
        if(id=='ajouter')
        {
          if( !test_dateITA( $("#f_date_debut").val() ) )
          {
            $('#ajax_msg').removeAttr("class").addClass("erreur").html("Date de début au format JJ/MM/AAAA incorrecte !");
            return(false);
          }
          if( !test_dateITA( $("#f_date_fin").val() ) )
          {
            $('#ajax_msg').removeAttr("class").addClass("erreur").html("Date de fin au format JJ/MM/AAAA incorrecte !");
            return(false);
          }
        }
        if(id=='retirer')
        {
          if(!(confirm("Les bilans officiels associés seront perdus !\n\nPour modifier les dates, il faut utiliser le bouton du dessus.\n\nConfirmez-vous vouloir retirer ces associations période(s) / classe(s) groupe(s) ?")) )
          {
            return(false);
          }
          if(!(confirm("--- ATTENTION --- DERNIÈRE DEMANDE DE CONFIRMATION ---\nLes éventuels bilans officiels associés (bulletins...) seront supprimés !\n\nAvez-bien coché ce que vous souhaitiez, en connaissance de cause ?")) )
          {
            return(false);
          }
        }
        $('button').prop('disabled',true);
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE+'&action='+id,
            data : 'csrf='+CSRF+'&'+$("#form_select").serialize(),
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button').prop('disabled',false);
              $('#ajax_msg').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $('button').prop('disabled',false);
              if(responseHTML.substring(0,6)!='<hr />')
              {
                $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
                $('#bilan').html(responseHTML);
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
