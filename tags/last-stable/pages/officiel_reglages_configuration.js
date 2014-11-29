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
    // Afficher masquer des éléments du formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_releve_check_supplementaire').click
    (
      function()
      {
        $('#f_releve_ligne_factice , #f_releve_ligne_supplementaire').toggle();
        $('#f_releve_ligne_supplementaire').focus();
      }
    );

    $('#f_bulletin_check_supplementaire').click
    (
      function()
      {
        $('#f_bulletin_ligne_factice , #f_bulletin_ligne_supplementaire').toggle();
        $('#f_bulletin_ligne_supplementaire').focus();
      }
    );

    $('#f_socle_check_supplementaire').click
    (
      function()
      {
        $('#f_socle_ligne_factice , #f_socle_ligne_supplementaire').toggle();
        $('#f_socle_ligne_supplementaire').focus();
      }
    );

    $('#f_releve_etat_acquisition').click
    (
      function()
      {
        $('#span_releve_etat_acquisition').toggle();
      }
    );

    $('#f_releve_moyenne_scores , #f_releve_pourcentage_acquis').click
    (
      function()
      {
        if( ($('#f_releve_moyenne_scores').is(':checked')) || ($('#f_releve_pourcentage_acquis').is(':checked')) )
        {
          $('label[for=f_releve_conversion_sur_20]').show();
        }
        else
        {
          $('label[for=f_releve_conversion_sur_20]').hide();
        }
      }
    );

    $('#f_bulletin_moyenne_scores').click
    (
      function()
      {
        if($('#f_bulletin_moyenne_scores').is(':checked'))
        {
          $('#span_moyennes').show();
        }
        else
        {
          $('#span_moyennes').hide();
        }
      }
    );

    $('#f_bulletin_appreciation_generale').change
    (
      function()
      {
        if(parseInt($('#f_bulletin_appreciation_generale').val(),10)>0)
        {
          $('#span_moyenne_generale').show();
        }
        else
        {
          $('#span_moyenne_generale').hide();
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Alerter sur la nécessité de valider
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#form_releve input , #form_releve select").change
    (
      function()
      {
        $('#ajax_msg_releve').removeAttr("class").addClass("alerte").html("Enregistrer pour confirmer.");
      }
    );

    $("#form_bulletin input , #form_bulletin select").change
    (
      function()
      {
        $('#ajax_msg_bulletin').removeAttr("class").addClass("alerte").html("Enregistrer pour confirmer.");
      }
    );

    $("#form_socle input , #form_socle select").change
    (
      function()
      {
        $('#ajax_msg_socle').removeAttr("class").addClass("alerte").html("Enregistrer pour confirmer.");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire "Relevé d'évaluations"
    // Traitement du formulaire "Bulletin scolaire"
    // Traitement du formulaire "État de maîtrise du socle"
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_valider_releve , #bouton_valider_bulletin , #bouton_valider_socle').click
    (
      function()
      {
        var objet = $(this).attr('id').substring(15);
        if( (objet=='releve') && (!$('#f_'+objet+'_etat_acquisition').is(':checked')) && ($('#f_'+objet+'_cases_nb option:selected').val()==0) )
        {
          $('#ajax_msg_'+objet).removeAttr("class").addClass("erreur").html("Choisir au moins une indication à faire figurer sur le bilan !");
          return false;
        }
        if( (objet=='socle') && (!$('#f_'+objet+'_pourcentage_acquis').is(':checked')) && (!$('#f_'+objet+'_etat_validation').is(':checked')) )
        {
          $('#ajax_msg_'+objet).removeAttr("class").addClass("erreur").html("Choisir au moins une indication à faire figurer sur le bilan !");
          return false;
        }
        if( ($('#f_'+objet+'_check_supplementaire').is(':checked')) && (!$('#f_'+objet+'_ligne_supplementaire').val()) )
        {
          $('#ajax_msg_'+objet).removeAttr("class").addClass("erreur").html("Indiquer le texte de la ligne additionnelle à faire figurer sur le bilan !");
          $('#f_'+objet+'_ligne_supplementaire').focus();
          return false;
        }
        $('#bouton_valider_'+objet).prop('disabled',true);
        $('#ajax_msg_'+objet).removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&objet='+objet+'&'+$('#form_'+objet).serialize(),
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#bouton_valider_'+objet).prop('disabled',false);
              $('#ajax_msg_'+objet).removeAttr("class").addClass("alerte").html("Échec de la connexion !");
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $('#bouton_valider_'+objet).prop('disabled',false);
              if(responseHTML!='ok')
              {
                $('#ajax_msg_'+objet).removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_msg_'+objet).removeAttr("class").addClass("valide").html("Données enregistrées !");
              }
              return false;
            }
          }
        );
      }
    );

  }
);
