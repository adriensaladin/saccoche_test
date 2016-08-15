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

    var groupe_id    = 0;
    var groupe_type  = $("#f_groupe option:selected").parent().attr('label'); // Il faut indiquer une valeur initiale au moins pour le profil élève
    var eleves_ordre = '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Charger le select f_pilier en ajax
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var maj_pilier = function()
    {
      $("#f_pilier").html('').parent().hide();
      palier_id = $("#f_palier").val();
      if(palier_id)
      {
        $('#ajax_maj_pilier').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page=_maj_select_piliers',
            data : 'f_palier='+palier_id+'&f_multiple=1',
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_maj_pilier').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==true)
              {
                $('#ajax_maj_pilier').removeAttr('class').html('&nbsp;');
                $('#f_pilier').html(responseJSON['value']).parent().show();
              }
              else
              {
                $('#ajax_maj_pilier').attr('class','alerte').html(responseJSON['value']);
              }
            }
          }
        );
      }
      else
      {
        $('#ajax_maj_pilier').removeAttr('class').html("");
      }
    };

    $("#f_palier").change( maj_pilier );

    maj_pilier();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Charger le select f_eleve en ajax
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var maj_eleve = function()
    {
      $("#f_eleve").html('<option value="">&nbsp;</option>').parent().hide();
      groupe_id = $("#f_groupe option:selected").val();
      if(groupe_id)
      {
        eleves_ordre = $("#f_eleves_ordre option:selected").val();
        groupe_type  = $("#f_groupe option:selected").parent().attr('label');
        if(typeof(groupe_type)=='undefined') {groupe_type = 'Classes';} // Cas d'un P.P.
        $('#ajax_maj_eleve').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page=_maj_select_eleves',
            data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_eleves_ordre='+eleves_ordre+'&f_statut=1'+'&f_multiple=1'+'&f_selection=1',
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_maj_eleve').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(groupe_type=='Classes')
              {
                $("#bloc_ordre").hide();
              }
              else
              {
                $("#bloc_ordre").show();
              }
              if(responseJSON['statut']==true)
              {
                $('#ajax_maj_eleve').removeAttr('class').html("");
                $('#f_eleve').html(responseJSON['value']).parent().show();
              }
              else
              {
                $('#ajax_maj_eleve').attr('class','alerte').html(responseJSON['value']);
              }
            }
          }
        );
      }
      else
      {
        $("#bloc_ordre").hide();
        $('#ajax_maj_eleve').removeAttr('class').html("");
      }
    };

    $("#f_groupe").change( maj_eleve );
    $("#f_eleves_ordre").change( maj_eleve );

    maj_eleve(); // Dans le cas d'un P.P.

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du premier formulaire pour afficher le tableau avec les états de validations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire0 = $('#zone_choix');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation0 = formulaire0.validate
    (
      {
        rules :
        {
          'f_pilier[]'   : { required:true },
          f_groupe       : { required:true },
          'f_eleve[]'    : { required:true },
          f_eleves_ordre : { required:true }
        },
        messages :
        {
          'f_pilier[]'   : { required:"compétence(s) manquante(s)" },
          f_groupe       : { required:"classe / groupe manquant" },
          'f_eleve[]'    : { required:"élève(s) manquant(s)" },
          f_eleves_ordre : { required:"ordre manquant" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.is("select")) {element.after(error);}
          else if(element.attr("type")=="checkbox") {element.parent().parent().next().after(error);}
        }
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions0 =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_choix",
      beforeSubmit : test_form_avant_envoi0,
      error : retour_form_erreur0,
      success : retour_form_valide0
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire0.submit
    (
      function()
      {
        // récupération d'éléments
        $('#f_groupe_type').val( groupe_type );
        $(this).ajaxSubmit(ajaxOptions0);
        return false;
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi0(formData, jqForm, options)
    {
      $('#ajax_msg_choix').removeAttr('class').html("");
      var readytogo = validation0.form();
      if(readytogo)
      {
        $("#Afficher_validation").prop('disabled',true);
        $('#ajax_msg_choix').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur0(jqXHR, textStatus, errorThrown)
    {
      $("#Afficher_validation").prop('disabled',false);
      $('#ajax_msg_choix').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide0(responseJSON)
    {
      initialiser_compteur();
      $("#Afficher_validation").prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg_choix').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        responseJSON['value'] = responseJSON['value'].replace( '@PALIER@' , $("#f_palier option:selected").text() );
        $('#tableau_validation').html(responseJSON['value']);
        $('#zone_validation').show('fast');
        $('#ajax_msg_choix').removeAttr('class').html('');
        $('#zone_choix').hide('fast');
        $("body").oneTime("1s", function() {window.scrollTo(0,1000);} );
      }
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur une cellule validée du tableau => Afficher le message de confirmation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var td_id = '';

    $('#tableau_validation').on
    (
      'click',
      'td.V1',
      function()
      {
        td_id = $(this).attr('id');
        var user_id  = td_id.substring(1,td_id.indexOf('C'));
        $('#report_nom').html( $('#I'+user_id).attr('alt') );
        $('#report_compet').html( $(this).parent().children('th').text().substring(0,12) );
        $('#confirmation').css('opacity',1);
        $('#fermer_zone_validation').attr('class',"annuler").html('Annuler / Retour');
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour fermer la zone de validation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#tableau_validation').on
    (
      'click',
      '#fermer_zone_validation',
      function()
      {
        $('#zone_choix').show('fast');
        $('#zone_validation').hide('fast');
        $('#tableau_validation').html('<tbody><tr><td></td></tr></tbody>');
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour envoyer les validations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#tableau_validation').on
    (
      'click',
      '#Enregistrer_validation',
      function()
      {
        $("button").prop('disabled',true);
        $('#ajax_msg_validation').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=Enregistrer_validation'+'&delete_id='+td_id,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("button").prop('disabled',false);
              $('#ajax_msg_validation').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $("button").prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_validation').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#'+td_id).removeAttr("data-etat").attr('class',"v3");
                $('#fermer_zone_validation').attr('class',"retourner").html('Retour');
                $('#confirmation').css('opacity',0);
                $('#ajax_msg_validation').removeAttr('class').html("");
              }
            }
          }
        );
      }
    );

  }
);

