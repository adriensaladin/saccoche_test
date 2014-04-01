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

    function curseur()
    {
      if($("#f_profil").val()!='structure')
      {
        $('#f_password').focus();
      }
      else if($("#f_login").length)
      {
        $('#f_login').focus();
      }
    }

    // Appel en ajax pour initialiser le formulaire au chargement
    function chargement()
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=initialiser'+'&f_base='+$("#f_base").val()+'&f_profil='+$("#f_profil").val(),
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg').removeAttr("class").addClass("alerte").html(afficher_json_message_erreur(jqXHR,textStatus));
            return false;
          },
          success : function(responseJSON)
          {
            if(responseJSON['statut']==true)
            {
              $("#form_auth fieldset").html(responseJSON['value']);
              curseur();
            }
            else
            {
              $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseJSON['value']);
            }
          }
        }
      );
    }
    chargement();

    // Choix dans le formulaire des structures => Afficher le formulaire de la structure
    $('#form_auth').on
    (
      'click',
      '#f_choisir',
      function()
      {
        $('button').prop('disabled',true);
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=charger'+'&f_base='+$('#f_base option:selected').val()+'&f_profil='+$("#f_profil").val(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button').prop('disabled',false);
              $('#ajax_msg').removeAttr("class").addClass("alerte").html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              $('button').prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $("#form_auth fieldset").html(responseJSON['value']);
                curseur();
              }
              else
              {
                $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

    // Clic sur le lien pour changer de structure
    $('#form_auth').on
    (
      'click',
      '#f_changer',
      function()
      {
        $('#f_changer').hide();
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=choisir'+'&f_base='+$('#f_base').val()+'&f_profil='+$("#f_profil").val(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#f_changer').show();
              $('#ajax_msg').removeAttr("class").addClass("alerte").html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              $('#f_changer').show();
              if(responseJSON['statut']==true)
              {
                $("#form_auth fieldset").html(responseJSON['value']);
                curseur();
              }
              else
              {
                $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

    // Clic sur le lien [ Identifiants oubliés ! ]
    $('#form_auth').on
    (
      'click',
      'a.lost',
      function()
      {
        var ancre = $(this).attr('href').substr(1); // #...
        $('#form_auth').hide();
        $('#'+ancre+', #form_lost').show();

      }
    );

    // Clic sur le bouton [Retour au formulaire d'identification]
    $('#form_lost').on
    (
      'click',
      '#quit_lost',
      function()
      {
        $('#lost_structure, #lost_webmestre, #lost_confirmation, #lost_partenaire, #form_lost').hide();
        $('#ajax_msg_lost').removeAttr("class").html('');
        $('#form_auth').show();
      }
    );

    // Afficher / masquer le formulaire d'identifiants SACoche si formulaire ENT possible
    $('#form_auth').on
    (
      'change',
      'input[type=radio]',
      function()
      {
        if($('#f_mode_normal').is(':checked'))
        {
          $("#fieldset_normal, #lien_lost").show();
          $('#f_login').focus();
        }
        else
        {
          $("#fieldset_normal, #lien_lost").hide();
        }
      }
    );

    // Appel en ajax pour tester le numéro de la dernière version (et le comparer avec l'actuelle).
    function tester_version()
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=tester_version',
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_version').addClass("alerte").html(afficher_json_message_erreur(jqXHR,textStatus));
            return false;
          },
          success : function(responseJSON)
          {
            $('#ajax_version').addClass(responseJSON['class']).html(responseJSON['texte']).after(responseJSON['after']);
            if(responseJSON['after'])
            {
              format_liens('#cadre_milieu');
            }
          }
        }
      );
    }
    tester_version();

    // Intercepter la touche entrée
    $('#form_lost').on
    (
      'keyup',
      'input',
      function(e)
      {
        if(e.which==13)  // touche entrée
        {
          $('#submit_lost').click();
          return false;
        }
      }
    );

    // Demande pour obtenir la génération de nouveaux identifiants
    $('#form_lost').on
    (
      'click',
      '#submit_lost',
      function()
      {
        var f_courriel = $('#f_courriel_lost').val();
        if(!f_courriel)
        {
          $('#ajax_msg_lost').removeAttr("class").addClass("erreur").html('adresse manquante');
          $('#f_courriel_lost').focus();
          return false;
        }
        if(!testMail(f_courriel))
        {
          $('#ajax_msg_lost').removeAttr("class").addClass("erreur").html('adresse invalide');
          $('#f_courriel_lost').focus();
          return false;
        }
        $('#ajax_msg_lost').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $('button').prop('disabled',true);
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=demande_mdp'+'&f_base='+$('#f_base').val()+'&f_courriel='+encodeURIComponent(f_courriel),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button').prop('disabled',false);
              $('#ajax_msg_lost').removeAttr("class").addClass("alerte").html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              $('button').prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $('#lost_structure').hide();
                $('#lost_confirmation').show();
              }
              else
              {
                $('#ajax_msg_lost').removeAttr("class").addClass("alerte").html(responseJSON['value']);
              }
            }
          }
        );
      }
    );


// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire de validation de ses identifiants
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $('form');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_base       : { required:true },
          f_partenaire : { required:true },
          f_login      : { required:true , maxlength:20 },
          f_password   : { required:true , maxlength:20 }
        },
        messages :
        {
          f_base       : { required:"établissement manquant" },
          f_partenaire : { required:"partenariat manquant" },
          f_login      : { required:"nom d'utilisateur manquant" , maxlength:"20 caractères maximum" },
          f_password   : { required:"mot de passe manquant" , maxlength:"20 caractères maximum" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { element.after(error); }
        // success: function(label) {label.text("ok").removeAttr("class").addClass("valide");} Pas pour des champs soumis à vérification PHP
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
      target : "#ajax_msg",
      beforeSubmit : test_form_avant_envoi,
      error : retour_form_erreur,
      success : retour_form_valide
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire.submit
    (
      function()
      {
        if( ($('#fieldset_normal').length) && !($('#f_mode_normal').is(':checked')) )
        {
          document.location.href = './index.php?sso&base='+$('#f_base').val();
          return false;
        }
        else
        {
          $(this).ajaxSubmit(ajaxOptions);
          return false;
        }
      }
    );

    // Fonction précédent l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg').removeAttr("class").html("&nbsp;");
      var readytogo = validation.form();
      if(readytogo)
      {
        $('button').prop('disabled',true);
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $('button').prop('disabled',false);
      $('#ajax_msg').removeAttr("class").addClass("alerte").html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      $('button').prop('disabled',false);
      if(responseJSON['statut']==true)
      {
        $('#ajax_msg').removeAttr("class").addClass("valide").html("Identification réussie !");
        document.location.href = responseJSON['value'];
      }
      else
      {
        $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseJSON['value']);
      }
    }

  }
);
