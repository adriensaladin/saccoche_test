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

    // initialisation
    if( !$('#f_courriel').length )
    {
      return false;
    }
    $('#f_courriel').focus();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Gestion CAPTCHA
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var captcha_count  = 0;
    var captcha_length = 6;
    var captcha_string = '';

    // Clic sur une image
    $('#captcha_game').on
    (
      'click',
      'img',
      function()
      {
        captcha_string += $(this).attr('id').substring(4); // cap_
        captcha_count++;
        $(this).hide(0);
        if(captcha_count==captcha_length)
        {
          $('#f_captcha').val(captcha_string);
          $('#captcha_game').hide(0);
          $('#captcha_init').show(0);
        }
      }
    );

    // Clic sur le bouton pour recommencer
    $('#captcha_init').on
    (
      'click',
      'button',
      function()
      {
        captcha_count  = 0;
        captcha_string = '';
        $('#f_captcha').val('');
        $('#captcha_init').hide(0);
        $('#captcha_game').show(0).children('img').show(0);
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $('#form_lost');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_base     : { required:true },
          f_courriel : { required:true , email:true , maxlength:63 },
          f_captcha  : { required:true }
        },
        messages :
        {
          f_base     : { required:"établissement manquant" },
          f_courriel : { required:"adresse manquante" , email:"adresse invalide", maxlength:"63 caractères maximum" },
          f_captcha  : { required:"réponse manquante" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { element.after(error); }
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_envoyer",
      beforeSubmit : test_form_avant_envoi,
      error : retour_form_erreur,
      success : retour_form_valide
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions);
        return false;
      }
    );

    // Fonction précédent l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg_envoyer').removeAttr("class").html("&nbsp;");
      var readytogo = validation.form();
      if(readytogo)
      {
        $('button').prop('disabled',true);
        $('#ajax_msg_envoyer').removeAttr("class").addClass("loader").html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $('button').prop('disabled',false);
      $('#ajax_msg_envoyer').removeAttr("class").addClass("alerte").html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      $('button').prop('disabled',false);
      if(responseJSON['statut']==true)
      {
        $('#form_lost').hide();
        $('#lost_confirmation').show();
      }
      else
      {
        $('#ajax_msg_envoyer').removeAttr("class").addClass("alerte").html(responseJSON['value']);
        if( responseJSON['value'].substring(0,15) == 'Ordre incorrect' )
        {
          $('#captcha_init').children('button').click();
        }
      }
    }

  }
);
