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
// Clic sur le lien pour Lancer une recherche de structure sur le serveur communautaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#ouvrir_recherche').click
    (
      function()
      {
        $('#div_instance').hide();
        $('#ajax_msg_sesamath').removeAttr("class").html("&nbsp;");
        // Décocher tous les boutons radio
        $('#f_recherche_mode input[type=radio]').each
        (
          function()
          {
            this.checked = false;
          }
        );
        $('#f_recherche_uai').hide();
        $('#f_recherche_geo').hide();
        $('#f_recherche_resultat').html('<li></li>').hide();
        $('#form_communautaire').show();
        initialiser_compteur();
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour Annuler la recherche sur le serveur communautaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#rechercher_annuler').click
    (
      function()
      {
        $('#div_instance').show();
        $('#form_communautaire').hide();
        return false;
      }
    );


    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire form_sesamath
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_sesamath = $('#form_sesamath');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation_sesamath = formulaire_sesamath.validate
    (
      {
        rules :
        {
          f_sesamath_id       : { required:true , digits:true },
          f_sesamath_uai      : { required:false , uai_format:true , uai_clef:true },
          f_sesamath_type_nom : { required:true , maxlength:50 },
          f_sesamath_key      : { required:true , rangelength:[32,32] }
        },
        messages :
        {
          f_sesamath_id       : { required:"identifiant manquant" , digits:"identifiant uniquement composé de chiffres" },
          f_sesamath_uai      : { uai_format:"n°UAI invalide" , uai_clef:"n°UAI invalide" },
          f_sesamath_type_nom : { required:"dénomination manquante" , maxlength:"50 caractères maximum" },
          f_sesamath_key      : { required:"clef manquante" , rangelength:"la clef doit comporter 32 caractères" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { element.after(error); }
        // success: function(label) {label.text("ok").removeAttr("class").addClass("valide");} Pas pour des champs soumis à vérification PHP
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_sesamath =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : "html",
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_sesamath",
      beforeSubmit : test_form_avant_envoi_sesamath,
      error : retour_form_erreur_sesamath,
      success : retour_form_valide_sesamath
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_sesamath.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_sesamath);
        return false;
      }
    ); 

    // Fonction précédent l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi_sesamath(formData, jqForm, options)
    {
      $('#ajax_msg_sesamath').removeAttr("class").html("&nbsp;");
      var readytogo = validation_sesamath.form();
      if(readytogo)
      {
        $("#bouton_valider_sesamath").prop('disabled',true);
        $('#ajax_msg_sesamath').removeAttr("class").addClass("loader").html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_sesamath(jqXHR, textStatus, errorThrown)
    {
      $("#bouton_valider_sesamath").prop('disabled',false);
      $('#ajax_msg_sesamath').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_sesamath(responseHTML)
    {
      initialiser_compteur();
      $("#bouton_valider_sesamath").prop('disabled',false);
      if(responseHTML=='ok')
      {
        $('#ajax_msg_sesamath').removeAttr("class").addClass("valide").html("Données enregistrées !");
      }
      else
      {
        $('#ajax_msg_sesamath').removeAttr("class").addClass("alerte").html(responseHTML);
      }
    }


    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire form_contact
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_contact = $('#form_contact');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation_contact = formulaire_contact.validate
    (
      {
        rules :
        {
          f_contact_nom      : { required:function(){return CONTACT_MODIFICATION_USER!='non';} , maxlength:20 },
          f_contact_prenom   : { required:function(){return CONTACT_MODIFICATION_USER!='non';} , maxlength:20 },
          f_contact_courriel : { required:function(){return CONTACT_MODIFICATION_MAIL!='non';} , email:true , maxlength:63 }
        },
        messages :
        {
          f_contact_nom      : { required:"nom manquant" , maxlength:"20 caractères maximum" },
          f_contact_prenom   : { required:"prénom manquant" , maxlength:"20 caractères maximum" },
          f_contact_courriel : { required:"courriel manquant" , email:"courriel invalide", maxlength:"63 caractères maximum" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { element.after(error); }
        // success: function(label) {label.text("ok").removeAttr("class").addClass("valide");} Pas pour des champs soumis à vérification PHP
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_contact =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : "html",
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_contact",
      beforeSubmit : test_form_avant_envoi_contact,
      error : retour_form_erreur_contact,
      success : retour_form_valide_contact
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_contact.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_contact);
        return false;
      }
    ); 

    // Fonction précédent l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi_contact(formData, jqForm, options)
    {
      $('#ajax_msg_contact').removeAttr("class").html("&nbsp;");
      var readytogo = validation_contact.form();
      if(readytogo)
      {
        if( (CONTACT_MODIFICATION_MAIL!='oui') && (CONTACT_MODIFICATION_MAIL!='non') && ($('#f_contact_courriel').val().lastIndexOf(CONTACT_MODIFICATION_MAIL)==-1) )
        {
          $('#ajax_msg_contact').removeAttr("class").addClass("erreur").html("Adresse de courriel restreinte au domaine '"+CONTACT_MODIFICATION_MAIL+"' par le webmestre.").show(); // Ajout show() sinon ici ça disparait...
          readytogo = false;
        }
      }
      if(readytogo)
      {
        $("#bouton_valider_contact").prop('disabled',true);
        $('#ajax_msg_contact').removeAttr("class").addClass("loader").html("En cours&hellip;").show(); // Ajout show() sinon ici ça disparait...
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_contact(jqXHR, textStatus, errorThrown)
    {
      $("#bouton_valider_contact").prop('disabled',false);
      $('#ajax_msg_contact').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_contact(responseHTML)
    {
      initialiser_compteur();
      $("#bouton_valider_contact").prop('disabled',false);
     if(responseHTML=='ok')
      {
        $('#ajax_msg_contact').removeAttr("class").addClass("valide").html("Données enregistrées !");
      }
      else
      {
        $('#ajax_msg_contact').removeAttr("class").addClass("alerte").html(responseHTML);
      }
    }


    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire form_etablissement
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Alerter sur la nécessité de valider
    $("#form_etablissement input").change
    (
      function()
      {
        $('#ajax_msg_etablissement').removeAttr("class").addClass("alerte").html("Enregistrer pour confirmer.");
      }
    );

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_etablissement = $('#form_etablissement');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation_etablissement = formulaire_etablissement.validate
    (
      {
        rules :
        {
          f_etablissement_denomination : { required:true  , maxlength:50 },
          f_etablissement_adresse1     : { required:false , maxlength:50 },
          f_etablissement_adresse2     : { required:false , maxlength:50 },
          f_etablissement_adresse3     : { required:false , maxlength:50 },
          f_etablissement_telephone    : { required:false , maxlength:25 },
          f_etablissement_fax          : { required:false , maxlength:25 },
          f_etablissement_courriel     : { required:false , maxlength:63 , email:true }
        },
        messages :
        {
          f_etablissement_denomination : { maxlength:"50 caractères maximum" , required:"dénomination manquante" },
          f_etablissement_adresse1     : { maxlength:"50 caractères maximum" },
          f_etablissement_adresse2     : { maxlength:"50 caractères maximum" },
          f_etablissement_adresse3     : { maxlength:"50 caractères maximum" },
          f_etablissement_telephone    : { maxlength:"25 caractères maximum" },
          f_etablissement_fax          : { maxlength:"25 caractères maximum" },
          f_etablissement_courriel     : { maxlength:"63 caractères maximum" , email:"courriel invalide" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { element.after(error); }
        // success: function(label) {label.text("ok").removeAttr("class").addClass("valide");} Pas pour des champs soumis à vérification PHP
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_etablissement =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : "html",
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_etablissement",
      beforeSubmit : test_form_avant_envoi_etablissement,
      error : retour_form_erreur_etablissement,
      success : retour_form_valide_etablissement
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_etablissement.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_etablissement);
        return false;
      }
    ); 

    // Fonction précédent l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi_etablissement(formData, jqForm, options)
    {
      $('#ajax_msg_etablissement').removeAttr("class").html("&nbsp;");
      var readytogo = validation_etablissement.form();
      if(readytogo)
      {
        $("#bouton_valider_etablissement").prop('disabled',true);
        $('#ajax_msg_etablissement').removeAttr("class").addClass("loader").html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_etablissement(jqXHR, textStatus, errorThrown)
    {
      $("#bouton_valider_etablissement").prop('disabled',false);
      $('#ajax_msg_etablissement').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_etablissement(responseHTML)
    {
      initialiser_compteur();
      $("#bouton_valider_etablissement").prop('disabled',false);
      if(responseHTML=='ok')
      {
        $('#ajax_msg_etablissement').removeAttr("class").addClass("valide").html("Données enregistrées !");
      }
      else
      {
        $('#ajax_msg_etablissement').removeAttr("class").addClass("alerte").html(responseHTML);
      }
    }


    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire form_annee_scolaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Alerter sur la nécessité de valider et simulation de l'affichage de l'année scolaire
    function simuler_affichage_annee_scolaire()
    {
      var obj_date     = new Date();
      var mois_actuel  = obj_date.getMonth()+1;
      var mois_bascule = $('#f_mois_bascule_annee_scolaire option:selected').val();
      if(mois_bascule==1)
      {
        var affichage = obj_date.getFullYear();
      }
      else if(mois_actuel < mois_bascule)
      {
        var affichage = (obj_date.getFullYear()-1)+'/'+obj_date.getFullYear();
      }
      else
      {
        var affichage = obj_date.getFullYear()+'/'+(obj_date.getFullYear()+1);
      }
      $('#span_simulation').html(affichage);
    }

    $("#f_mois_bascule_annee_scolaire").change
    (
      function()
      {
        simuler_affichage_annee_scolaire();
        $('#ajax_msg_annee_scolaire').removeAttr("class").addClass("alerte").html("Enregistrer pour confirmer.");
      }
    );

    simuler_affichage_annee_scolaire();

    $('#bouton_valider_annee_scolaire').click
    (
      function()
      {
        $("#bouton_valider_annee_scolaire").prop('disabled',true);
        $('#ajax_msg_annee_scolaire').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_mois_bascule_annee_scolaire='+$('#f_mois_bascule_annee_scolaire option:selected').val(),
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("#bouton_valider_annee_scolaire").prop('disabled',false);
              $('#ajax_msg_annee_scolaire').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $("#bouton_valider_annee_scolaire").prop('disabled',false);
              if(responseHTML!='ok')
              {
                $('#ajax_msg_annee_scolaire').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_msg_annee_scolaire').removeAttr("class").addClass("valide").html("Donnée enregistrée !");
              }
              return false;
            }
          }
        );
      }
    );


    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Intercepter la touche entrée pour éviter une soumission d'un formulaire sans contrôle
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_communautaire').submit
    (
      function()
      {
        if( $('#f_mode_uai').attr('checked')==true )
        {
          $("#rechercher_uai").click();
        }
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger le select geo1 en ajax (appel au serveur communautaire)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_geo1()
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&action=Afficher_form_geo1',
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
          },
          success : function(responseHTML)
          {
            if(responseHTML.substring(0,26)=='<option value=""></option>')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#ajax_msg_communautaire').removeAttr("class").html("&nbsp;");
              $('#f_geo1').html(responseHTML).fadeIn('fast').focus();
              initialiser_compteur();
            }
            else
            {
              $('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html(responseHTML);
            }
          }
        }
      );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger le select geo2 en ajax (appel au serveur communautaire)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_geo2(geo1_val)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&action=Afficher_form_geo2&f_geo1='+geo1_val,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#f_recherche_geo select').prop('disabled',false);
            $('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
          },
          success : function(responseHTML)
          {
            $('#f_recherche_geo select').prop('disabled',false);
            if(responseHTML.substring(0,26)=='<option value=""></option>')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#ajax_msg_communautaire').removeAttr("class").html("&nbsp;");
              $('#f_geo2').html(responseHTML).fadeIn('fast').focus();
              initialiser_compteur();
            }
            else
            {
              $('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html(responseHTML);
            }
          }
        }
      );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger le select geo3 en ajax (appel au serveur communautaire)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_geo3(geo1_val,geo2_val)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&action=Afficher_form_geo3&f_geo1='+geo1_val+'&f_geo2='+geo2_val,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#f_recherche_geo select').prop('disabled',false);
            $('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
          },
          success : function(responseHTML)
          {
            $('#f_recherche_geo select').prop('disabled',false);
            if(responseHTML.substring(0,26)=='<option value=""></option>')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#ajax_msg_communautaire').removeAttr("class").html("&nbsp;");
              $('#f_geo3').html(responseHTML).fadeIn('fast').focus();
              initialiser_compteur();
            }
            else
            {
              $('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html(responseHTML);
            }
          }
        }
      );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Rechercher les structures à partir du select geo3 en ajax (appel au serveur communautaire)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_resultat_geo(geo3_val)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&action=Afficher_structures&f_geo3='+geo3_val,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#f_recherche_geo select').prop('disabled',false);
            $('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
          },
          success : function(responseHTML)
          {
            $('#f_recherche_geo select').prop('disabled',false);
            if(responseHTML.substring(0,3)=='<li')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#ajax_msg_communautaire').removeAttr("class").html("&nbsp;");
              $('#f_recherche_resultat').html(responseHTML).show();
              initialiser_compteur();
            }
            else
            {
              $('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html(responseHTML);
            }
          }
        }
      );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Rechercher les structures à partir du select uai en ajax (appel au serveur communautaire)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_resultat_uai(uai_val)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&action=Afficher_structures&f_uai='+uai_val,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#rechercher_uai').prop('disabled',false);
            $('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
          },
          success : function(responseHTML)
          {
            $('#rechercher_uai').prop('disabled',false);
            if(responseHTML.substring(0,3)=='<li')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#ajax_msg_communautaire').removeAttr("class").html("&nbsp;");
              $('#f_recherche_resultat').html(responseHTML).show();
              initialiser_compteur();
            }
            else
            {
              $('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html(responseHTML);
            }
          }
        }
      );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Choix du mode de recherche de la structure => demande d'actualisation éventuelle du select geo1
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_recherche_mode input').click
    (
      function()
      {
        mode = $(this).val();
        $("#f_recherche_resultat").html('<li></li>').hide();
        if(mode=='geo')
        {
          $('#ajax_msg_communautaire').removeAttr("class").addClass("loader").html("En cours&hellip;");
          $('#f_geo1').html('<option value=""></option>').fadeOut('fast'); // Ne pas utiliser "hide()" sinon pb de display block
          $('#f_geo2').html('<option value=""></option>').fadeOut('fast'); // Ne pas utiliser "hide()" sinon pb de display block
          $('#f_geo3').html('<option value=""></option>').fadeOut('fast'); // Ne pas utiliser "hide()" sinon pb de display block
          $("#f_recherche_uai").hide();
          $("#f_recherche_geo").show();
          maj_geo1();
        }
        else if(mode=='uai')
        {
          $('#ajax_msg_communautaire').removeAttr("class").html("&nbsp;");
          $("#f_recherche_geo").hide();
          $("#f_recherche_uai").show();
          $("#f_uai2").focus();
          initialiser_compteur();
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Changement du select geo1 => demande d'actualisation du select geo2
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#f_geo1").change
    (
      function()
      {
        $("#f_recherche_resultat").html('<li></li>').hide();
        $('#f_geo2').html('<option value=""></option>').fadeOut('fast');
        $('#f_geo3').html('<option value=""></option>').fadeOut('fast');
        var geo1_val = $("#f_geo1").val();
        if(geo1_val)
        {
          $('#f_recherche_geo select').prop('disabled',true);
          $('#ajax_msg_communautaire').removeAttr("class").addClass("loader").html("En cours&hellip;");
          maj_geo2(geo1_val);
        }
        else
        {
          $('#ajax_msg_communautaire').removeAttr("class").html("&nbsp;");
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Changement du select geo2 => demande d'actualisation du select geo3
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#f_geo2").change
    (
      function()
      {
        $("#f_recherche_resultat").html('<li></li>').hide();
        $('#f_geo3').html('<option value=""></option>').fadeOut('fast');
        var geo1_val = $("#f_geo1").val();
        var geo2_val = $("#f_geo2").val();
        if(geo1_val && geo2_val)
        {
          $('#f_recherche_geo select').prop('disabled',true);
          $('#ajax_msg_communautaire').removeAttr("class").addClass("loader").html("En cours&hellip;");
          maj_geo3(geo1_val,geo2_val);
        }
        else
        {
          $('#ajax_msg_communautaire').removeAttr("class").html("&nbsp;");
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Changement du select geo3 => demande d'actualisation du résultat de la recherche
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#f_geo3").change
    (
      function()
      {
        $("#f_recherche_resultat").html('<li></li>').hide();
        var geo3_val = $("#f_geo3").val();
        if(geo3_val)
        {
          $('#f_recherche_geo select').prop('disabled',true);
          $('#ajax_msg_communautaire').removeAttr("class").addClass("loader").html("En cours&hellip;");
          maj_resultat_geo(geo3_val);
        }
        else
        {
          $('#ajax_msg_communautaire').removeAttr("class").html("&nbsp;");
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Validation du numéro uai => demande d'actualisation du résultat de la recherche
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#rechercher_uai").click
    (
      function()
      {
        $("#f_recherche_resultat").html('<li></li>').hide();
        var uai_val = $("#f_uai2").val();
        // Vérifier le format du numéro UAI
        if(!test_uai_format(uai_val))
        {
          $('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html("Erreur : il faut 7 chiffres suivis d'une lettre !");
          return false;
        }
        // Vérifier la géographie du numéro UAI
        // => sans objet
        // Vérifier la clef de contrôle du numéro UAI
        if(!test_uai_clef(uai_val))
        {
          $('#ajax_msg_communautaire').removeAttr("class").addClass("alerte").html("Erreur : clef de contrôle incompatible !");
          return false;
        }
        // Si on arrive jusque là c'est que le n° UAI est valide
        $('#rechercher_uai').prop('disabled',true);
        $('#ajax_msg_communautaire').removeAttr("class").addClass("loader").html("En cours&hellip;");
        maj_resultat_uai(uai_val);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur une image pour Valider le choix d'une structure
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_recherche_resultat').on
    (
      'click',
      'q.valider',
      function()
      {
        var id_key_uai = $(this).parent().attr('id').substr(3); // id ; key ; uai séparés par '_' ; attention, le n°UAI peut être vide...
        var denomination = $(this).parent().text();
        var tab_infos = id_key_uai.split('_');
        $('#f_sesamath_id').val(tab_infos[0]);
        $('#f_sesamath_key').val(tab_infos[1]);
        $('#f_sesamath_uai').val(tab_infos[2]); // (peut être vide)
        $('#f_sesamath_type_nom').val(denomination);
        $('#ajax_msg_sesamath').removeAttr("class").addClass("alerte").html('Pensez à valider pour confirmer votre sélection !');
        initialiser_compteur();
        $('#rechercher_annuler').click();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Upload d'un fichier image avec jquery.ajaxupload.js
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Envoi du fichier avec jquery.ajaxupload.js
    new AjaxUpload
    ('#f_upload',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'action':'upload_logo'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier,
        onComplete: retourner_fichier
      }
    );

    function changer_fichier(fichier_nom,fichier_extension)
    {
      $("#f_upload").prop('disabled',true);
      $('#ajax_upload').removeAttr("class").html('&nbsp;');
      return true;
    }

    function verifier_fichier(fichier_nom,fichier_extension)
    {
      if (fichier_nom==null || fichier_nom.length<5)
      {
        $("#f_upload").prop('disabled',false);
        $('#ajax_upload').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
        return false;
      }
      else if ('.gif.jpg.jpeg.png.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1)
      {
        $("#f_upload").prop('disabled',false);
        $('#ajax_upload').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas une extension d\'image autorisée (jpg jpeg gif png).');
        return false;
      }
      else
      {
        $('#ajax_upload').removeAttr("class").addClass("loader").html("En cours&hellip;");
        return true;
      }
    }

    function retourner_fichier(fichier_nom,responseHTML)  // Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
    {
      if(responseHTML.substring(0,4)!='<li>')
      {
        $("#f_upload").prop('disabled',false);
        $('#ajax_upload').removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        initialiser_compteur();
        $("#f_upload").prop('disabled',false);
        $('#ajax_upload').removeAttr("class").addClass("valide").html('Logo ajouté');
        $('#puce_logo').html(responseHTML);
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Appel en ajax pour supprimer le logo de l'établissement
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#puce_logo').on
    (
      'click',
      'q.supprimer',
      function()
      {
        $('#ajax_upload').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&action=delete_logo',
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_upload').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              if(responseHTML!='ok')
              {
                $('#ajax_upload').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_upload').removeAttr("class").html('');
                $('#puce_logo').html('<li>Pas de logo actuellement enregistré.</li>');
              }
            }
          }
        );
      }
    );

  }
);
