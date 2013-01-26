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

    var id_periode_import = $('#f_periode_import option:selected').val();

    $('#f_periode_import').change
    (
      function()
      {
        id_periode_import = $('#f_periode_import option:selected').val();
        uploader_fichier['_settings']['data']['f_periode'] = id_periode_import;
      }
    );


// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Changement de classe
// -> choisir automatiquement la meilleure période si un changement manuel de période n'a jamais été effectué
// -> afficher le formulaire de périodes s'il est masqué
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var autoperiode = true; // Tant qu'on ne modifie pas manuellement le choix des périodes, modification automatique du formulaire

    $('#f_periode').change
    (
      function()
      {
        autoperiode = false;
      }
    );

    function selectionner_periode_adaptee(id_groupe)
    {
      if(typeof(tab_groupe_periode[id_groupe])!='undefined')
      {
        for(var id_periode in tab_groupe_periode[id_groupe]) // Parcourir un tableau associatif...
        {
          var tab_split = tab_groupe_periode[id_groupe][id_periode].split('_');
          if( (date_mysql>=tab_split[0]) && (date_mysql<=tab_split[1]) )
          {
            $("#f_periode option[value="+id_periode+"]").prop('selected',true);
            break;
          }
        }
      }
    }

    $('#f_groupe').change
    (
      function()
      {
        var id_groupe = $('#f_groupe option:selected').val();
        // Modification automatique du formulaire : périodes
        if(autoperiode && id_groupe)
        {
          // Rechercher automatiquement la meilleure période
          selectionner_periode_adaptee(id_groupe);
        }
        // Afficher la zone de choix des périodes
        if(id_groupe)
        {
          $('#f_periode').removeAttr("class");
        }
        else
        {
          $('#f_periode').addClass("hide");
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Upload d'un fichier avec jquery.ajaxupload.js
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Envoi du fichier avec jquery.ajaxupload.js ; on lui donne un nom afin de pouvoir changer dynamiquement le paramètre.
    var uploader_fichier = new AjaxUpload
    ('#import_siecle',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'f_action':'import_siecle','f_periode':'maj_plus_tard'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier,
        onComplete: retourner_fichier
      }
    );

    function changer_fichier(fichier_nom,fichier_extension)
    {
      $('#ajax_msg_import').removeAttr("class").html("&nbsp;");
      return true;
    }

    function verifier_fichier(fichier_nom,fichier_extension)
    {
      if (!id_periode_import)
      {
        $('#ajax_msg_import').removeAttr("class").addClass("erreur").html("Choisissez d'abord la période concernée.");
        return false;
      }
      else if (fichier_nom==null || fichier_nom.length<5)
      {
        $('#ajax_msg_import').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
        return false;
      }
      else if ('.xml.zip.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1)
      {
        $('#ajax_msg_import').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas une extension "xml" ou "zip".');
        return false;
      }
      else
      {
        $('button').prop('disabled',true);
        $('#ajax_msg_import').removeAttr("class").addClass("loader").html("En cours&hellip;");
        return true;
      }
    }

    function retourner_fichier(fichier_nom,responseHTML)  // Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
    {
      $('button').prop('disabled',false);
      if( (responseHTML.substring(0,18)!='<p class="astuce">') && (responseHTML.substring(0,16)!='<P class=astuce>') )
      {
        $('#ajax_msg_import').removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        $('#ajax_msg_import').removeAttr("class").html('');
        var confirmation_question = '<p>Confirmez-vous vouloir importer ces données dans <em>SACoche</em> pour la période <b>'+$('#f_periode_import option:selected').text()+'</b> ?</p>';
        var confirmation_boutons  = '<form action="#" method="post"><p><span class="tab"></span><button id="confirmer_manuel" type="button" class="valider">Confirmer.</button> <button id="fermer_zone" type="button" class="annuler">Annuler.</button><label id="ajax_msg_confirm">&nbsp;</label></p></form>';
        $.fancybox( responseHTML+confirmation_question+confirmation_boutons , { 'modal':true , 'centerOnScroll':true } );
        initialiser_compteur();
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Confirmation du traitement du fichier
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#confirmer_manuel').live // live est utilisé pour prendre en compte les nouveaux éléments créés
    ('click',
      function()
      {
        $('#ajax_msg_confirm').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+'traitement_siecle'+'&f_periode='+id_periode_import,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg_confirm').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              if(responseHTML.substring(0,7)!='<tbody>')
              {
                $('#ajax_msg_confirm').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                var resultat = '<b>Résultat du traitement :</b>'
                  +'<table class="bilan"><thead><tr><th>Élève</th><th>Absences<br />nb &frac12; journées</th><th>dont &frac12; journées<br />non justifiées</th><th>Nb retards</th></tr></thead>'+responseHTML+'</table>'
                  +'<form><p><span class="tab"></span><button id="fermer_zone" type="button" class="retourner">Retour.</button></p></form>';
                $.fancybox( resultat , { 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
                initialiser_compteur();
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Afficher le formulaire de saisie manuel
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $("#form_manuel");

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_groupe        : { required:true },
          f_periode       : { required:true }
        },
        messages :
        {
          f_groupe        : { required:"classe manquante" },
          f_periode       : { required:"période manquante" }
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
      dataType : "html",
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
        $(this).ajaxSubmit(ajaxOptions);
        return false;
      }
    ); 

    // Fonction précédent l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg_manuel').removeAttr("class").html("&nbsp;");
      var readytogo = validation.form();
      if(readytogo)
      {
        $('button').prop('disabled',true);
        $('#ajax_msg_manuel').removeAttr("class").addClass("loader").html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $('button').prop('disabled',false);
      $('#ajax_msg_manuel').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseHTML)
    {
      $('button').prop('disabled',false);
      if(responseHTML.substring(0,7)!='<tbody>')
      {
        $('#ajax_msg_manuel').removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        $('#ajax_msg_manuel').removeAttr("class").html('');
        var resultat = '<b>Saisie des absences et retards | '+$('#f_periode option:selected').text()+' | '+$('#f_groupe option:selected').text()+'</b>'
          +'<table id="tableau_saisie" class="bilan"><thead><tr><th>Élève</th><th>Absences<br />nb &frac12; journées</th><th>dont &frac12; journées<br />non justifiées</th><th>Nb retards</th></tr></thead>'+responseHTML+'</table>'
          +'<form><p><button id="Enregistrer_saisies" type="button" class="valider">Enregistrer les saisies</button> <button id="fermer_zone" type="button" class="retourner">Retour</button><label id="ajax_msg_enregistrer"></label></p></form>';
        $.fancybox( resultat , { 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
        initialiser_compteur();
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Modification d'une saisie : alerter besoin d'enregistrer
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('input[type=text]').live // live est utilisé pour prendre en compte les nouveaux éléments créés
    ('change',
      function()
      {
        $('#ajax_msg_enregistrer').removeAttr("class").addClass("alerte").html('Penser à enregistrer les modifications !');
        $('#fermer_zone').removeAttr("class").addClass("annuler").html('Annuler / Retour');
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Intercepter la touche entrée
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('input[type=text]').live // live est utilisé pour prendre en compte les nouveaux éléments créés
    ('keyup',
      function(e)
      {
        if(e.which==13)  // touche entrée
        {
          $('#Enregistrer_saisies').click();
        }
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour envoyer les saisies
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#Enregistrer_saisies').live // live est utilisé pour prendre en compte les nouveaux éléments créés
    ('click',
      function()
      {
        $("button").prop('disabled',true);
        // Récupérer les infos
        var tab_infos = new Array();
        $("#tableau_saisie tbody tr").each
        (
          function()
          {
            var user_id = $(this).attr('id').substring(3);
            tab_infos.push( user_id + '.' + $('#td1_'+user_id).val() + '.' + $('#td2_'+user_id).val() + '.' + $('#td3_'+user_id).val() );
          }
        );
        $('#ajax_msg_enregistrer').removeAttr("class").addClass("loader").html("En cours&hellip;");
        // Les envoyer en ajax
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=enregistrer_saisies'+'&f_periode='+$('#f_periode option:selected').val()+'&f_data='+tab_infos.join('_'),
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("button").prop('disabled',false);
              $('#ajax_msg_enregistrer').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $("button").prop('disabled',false);
              if(responseHTML!='ok')
              {
                $('#ajax_msg_enregistrer').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_msg_enregistrer').removeAttr("class").addClass("valide").html("Saisies enregistrées !");
                $('#fermer_zone').removeAttr("class").addClass("retourner").html('Retour');
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour fermer le cadre
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#fermer_zone').live // live est utilisé pour prendre en compte les nouveaux éléments créés
    ('click',
      function()
      {
        $.fancybox.close();
        return(false);
      }
    );

  }
);
