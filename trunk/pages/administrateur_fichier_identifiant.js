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
    // Enlever le message ajax au changement d'un élément de formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_select').on
    (
      'change',
      'select, input',
      function()
      {
        $('#ajax_msg').removeAttr("class").html("&nbsp;");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Réagir au changement dans le premier formulaire (choix principal)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#f_choix_principal").change
    (
      function()
      {
        // Masquer tout
        $('fieldset[id^=fieldset]').hide(0);
        $('#ajax_msg').removeAttr("class").html("&nbsp;");
        $('#ajax_retour').html("&nbsp;");
        // Puis afficher ce qu'il faut
        var objet = $(this).val();
        switch (objet)
        {
          case 'new_loginmdp' : maj_eleve_birth(); maj_f_user(); $('#fieldset_new_loginmdp').show(); break;
          case 'import_loginmdp'      : $('#fieldset_import_loginmdp').show();      break;
          case 'import_id_lcs'        : $('#fieldset_import_id_lcs').show();        break;
          case 'import_id_argos'      : $('#fieldset_import_id_argos').show();      break;
          case 'import_id_ent_normal' : $('#fieldset_import_id_ent_normal').show(); break;
          case 'import_id_ent_cas'    : $('#fieldset_import_id_ent_cas').show();    break;
          case 'import_id_gepi'       : $('#fieldset_import_id_gepi').show();       break;
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Réagir au changement dans le choix d'un profil ou d'un groupe
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#f_profil , #f_groupe").change
    (
      function()
      {
        $('#ajax_msg').removeAttr("class").html("&nbsp;");
        $('#ajax_retour').html("&nbsp;");
        maj_eleve_birth();
        maj_f_user();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Mettre à jour la liste des utilisateurs concernés
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_eleve_birth()
    {
      if($('#f_profil option:selected').val()=='eleves')
      {
        $('#eleve_birth').show();
      }
      else
      {
        $('#eleve_birth').hide();
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Mettre à jour la liste des utilisateurs concernés
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_f_user()
    {
      $('#fieldset_new_loginmdp button').prop('disabled',true);
      $('#div_users').hide();
      // On récupère le profil
      var profil = $('#f_profil option:selected').val();
      // On récupère le regroupement
      var groupe_val = $("#f_groupe").val();
      if( !profil || !groupe_val )
      {
        return false
      }
      groupe_type = groupe_val.substring(0,1);
      groupe_id   = groupe_val.substring(1);
      $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
      $('#bilan tbody').html('');
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_'+profil,
          data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_statut=1'+'&f_multiple=1'+'&f_selection=1'+'&f_nom=f_user',
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
              $('#f_user').html(responseHTML);
              $('#div_users').show();
              $('#fieldset_new_loginmdp button').prop('disabled',false);
            }
            else
            {
              $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
            }
          }
        }
      );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Réagir au clic sur un bouton pour demander un export csv de la base (user_ent -> user_export)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#user_export').click
    (
      function()
      {
        $('#form_select button').prop('disabled',true);
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&action='+'user_export',
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#form_select button').prop('disabled',false);
              $('#ajax_msg').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              if(responseHTML.substring(0,3)!='<ul')
              {
                $('#form_select button').prop('disabled',false);
                $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#form_select button').prop('disabled',false);
                $('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
                $('#ajax_retour').html(responseHTML);
                format_liens('#ajax_retour');
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Soumission du formulaire - choix 1 et 2
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#generer_login , #generer_mdp , #forcer_mdp_birth').click
    (
      function()
      {
        var action = $(this).attr('id');
        if( !$('#f_profil option:selected').val() )
        {
          $('#ajax_msg').removeAttr("class").addClass("erreur").html("Sélectionnez déjà un profil utilisateur !");
          return(false);
        }
        if( !$("#f_user input:checked").length )
        {
          $('#ajax_msg').removeAttr("class").addClass("erreur").html("Sélectionnez au moins un utilisateur !");
          return(false);
        }
        $('#form_select button').prop('disabled',true);
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&action='+action+'&'+$("#form_select").serialize(),
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
              if(responseHTML.substring(0,3)!='<ul')
              {
                $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_msg').removeAttr("class").addClass("valide").html('Demande réalisée.');
                $('#ajax_retour').html(responseHTML);
                format_liens('#ajax_retour');
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Réagir au clic sur un bouton pour envoyer un import csv afin de forcer les logins ou/et mdp élèves (user_ent -> user_import)
// Réagir au clic sur le bouton pour envoyer un csv issu de l'ENT
// Réagir au clic sur un bouton pour envoyer un csv issu de Gepi
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Envoi du fichier avec jquery.ajaxupload.js
    new AjaxUpload
    ('#import_loginmdp',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'action':'import_loginmdp'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier,
        onComplete: retourner_fichier
      }
    );
    new AjaxUpload
    ('#import_ent',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'action':'import_ent'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier,
        onComplete: retourner_fichier
      }
    );
    new AjaxUpload
    ('#import_gepi_profs',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'action':'import_gepi_profs'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier,
        onComplete: retourner_fichier
      }
    );
    new AjaxUpload
    ('#import_gepi_parents',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'action':'import_gepi_parents'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier,
        onComplete: retourner_fichier
      }
    );
    new AjaxUpload
    ('#import_gepi_eleves',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'action':'import_gepi_eleves'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier,
        onComplete: retourner_fichier
      }
    );

    function changer_fichier(fichier_nom,fichier_extension)
    {
      $('#ajax_msg').removeAttr("class").html('&nbsp;');
      $('#ajax_retour').html("&nbsp;");
      return true;
    }

    function verifier_fichier(fichier_nom,fichier_extension)
    {
      if (fichier_nom==null || fichier_nom.length<5)
      {
        $('#ajax_msg').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
        return false;
      }
      else if ('.csv.txt.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1)
      {
        $('#ajax_msg').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas une extension "csv" ou "txt".');
        return false;
      }
      else
      {
        $('#form_select button').prop('disabled',true);
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        return true;
      }
    }

    function retourner_fichier(fichier_nom,responseHTML)  // Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
    {
      $('#form_select button').prop('disabled',false);
      if( (responseHTML.substring(0,3)!='<ul') && (responseHTML.substring(0,3)!='<UL') )
      {
        $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        initialiser_compteur();
        $('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
        $('#ajax_retour').html(responseHTML);
        format_liens('#ajax_retour');
      }
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Réagir au clic sur un bouton afin de demander la duplication d'un champ
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('button[name=dupliquer]').click
    (
      function()
      {
        var action = $(this).attr('id');
        $('#ajax_retour').html('&nbsp;');
        $('#form_select button').prop('disabled',true);
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&action='+action,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#form_select button').prop('disabled',false);
              $('#ajax_msg').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $('#form_select button').prop('disabled',false);
              if(responseHTML=='ok')
              {
                $('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
              }
              else if(responseHTML.substring(0,3)=='<ul') // pour le webservice argos ou lcs
              {
                $('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
                $('#ajax_retour').html(responseHTML);
              }
              else
              {
                $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
              }
            }
          }
        );
      }
    );

  }
);
