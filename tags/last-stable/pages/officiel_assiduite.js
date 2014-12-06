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

    var id_periode_import = $('#f_periode_import option:selected').val();
    var f_action = '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Réagir au changement de période ou d'origine du fichier
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_upload()
    {
      // Masquer tout
      $('#puce_import_sconet , #puce_import_siecle , #puce_import_gepi , #puce_import_pronote').hide(0);
        // Puis afficher ce qu'il faut
      if( id_periode_import && f_action )
      {
        $('#ajax_msg_'+f_action).removeAttr("class").html('&nbsp;');
        $('#puce_'+f_action).show(0);
      }
    }

    $('#f_periode_import').change
    (
      function()
      {
        id_periode_import = $('#f_periode_import option:selected').val();
        uploader_fichier_sconet[ '_settings']['data']['f_periode'] = id_periode_import;
        uploader_fichier_siecle[ '_settings']['data']['f_periode'] = id_periode_import;
        uploader_fichier_gepi[   '_settings']['data']['f_periode'] = id_periode_import;
        uploader_fichier_pronote['_settings']['data']['f_periode'] = id_periode_import;
        afficher_upload();
      }
    );

    $("#f_choix_principal").change
    (
      function()
      {
        f_action = $(this).val();
        afficher_upload();
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
    // Attention, la variable f_action n'est pas accessible dans les AjaxUpload

    if( $('#form_fichier').length ) // Indéfini si pas de droit d'accès à cette fonctionnalité.
    {
      var uploader_fichier_sconet = new AjaxUpload
      ('#import_sconet',
        {
          action: 'ajax.php?page='+PAGE,
          name: 'userfile',
          data: {'csrf':CSRF,'f_action':'import_sconet','f_periode':'maj_plus_tard'},
          autoSubmit: true,
          responseType: "html",
          onChange: changer_fichier,
          onSubmit: verifier_fichier,
          onComplete: retourner_fichier
        }
      );
      var uploader_fichier_siecle = new AjaxUpload
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
      var uploader_fichier_gepi = new AjaxUpload
      ('#import_gepi',
        {
          action: 'ajax.php?page='+PAGE,
          name: 'userfile',
          data: {'csrf':CSRF,'f_action':'import_gepi','f_periode':'maj_plus_tard'},
          autoSubmit: true,
          responseType: "html",
          onChange: changer_fichier,
          onSubmit: verifier_fichier,
          onComplete: retourner_fichier
        }
      );
      var uploader_fichier_pronote = new AjaxUpload
      ('#import_pronote',
        {
          action: 'ajax.php?page='+PAGE,
          name: 'userfile',
          data: {'csrf':CSRF,'f_action':'import_pronote','f_periode':'maj_plus_tard'},
          autoSubmit: true,
          responseType: "html",
          onChange: changer_fichier,
          onSubmit: verifier_fichier,
          onComplete: retourner_fichier
        }
      );
    }

    function changer_fichier(fichier_nom,fichier_extension)
    {
      $('#ajax_msg_'+f_action).removeAttr("class").html("&nbsp;");
      return true;
    }

    function verifier_fichier(fichier_nom,fichier_extension)
    {
      if (!id_periode_import)
      {
        $('#ajax_msg_'+f_action).removeAttr("class").addClass("erreur").html("Choisir d'abord la période concernée.");
        return false;
      }
      else if (fichier_nom==null || fichier_nom.length<5)
      {
        $('#ajax_msg_'+f_action).removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
        return false;
      }
      else if ( (f_action=='import_sconet') && ('.xml.zip.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1) )
      {
        $('#ajax_msg_'+f_action).removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas une extension "xml" ou "zip".');
        return false;
      }
      else if ( (f_action=='import_siecle') && ('.xml.zip.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1) )
      {
        $('#ajax_msg_'+f_action).removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas une extension "xml" ou "zip".');
        return false;
      }
      else if ( (f_action=='import_gepi') && ('.csv.txt.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1) )
      {
        $('#ajax_msg_'+f_action).removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas une extension "csv" ou "txt".');
        return false;
      }
      else if ( (f_action=='import_pronote') && ('.xml.zip.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1) )
      {
        $('#ajax_msg_'+f_action).removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas une extension "xml" ou "zip".');
        return false;
      }
      else
      {
        $('button').prop('disabled',true);
        $('#ajax_msg_'+f_action).removeAttr("class").addClass("loader").html("En cours&hellip;");
        return true;
      }
    }

    function retourner_fichier(fichier_nom,responseHTML)  // Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
    {
      $('button').prop('disabled',false);
      var tab_infos = responseHTML.split(']¤[');
      if(tab_infos[0]!='ok')
      {
        $('#ajax_msg_'+f_action).removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        $('#comfirm_import_sconet , #comfirm_import_siecle , #comfirm_import_gepi , #comfirm_import_pronote').hide(0);
        if(f_action=='import_sconet')
        {
          $('#sconet_date_export').html(tab_infos[1]);
          $('#sconet_libelle'    ).html(tab_infos[2]);
          $('#sconet_date_debut' ).html(tab_infos[3]);
          $('#sconet_date_fin'   ).html(tab_infos[4]);
        }
        else if(f_action=='import_siecle')
        {
        }
        else if(f_action=='import_gepi')
        {
          $('#gepi_eleves_nb').html(tab_infos[1]);
        }
        else if(f_action=='import_pronote')
        {
          $('#pronote_objet'     ).html(tab_infos[1]);
          $('#pronote_eleves_nb' ).html(tab_infos[2]);
          $('#pronote_date_debut').html(tab_infos[3]);
          $('#pronote_date_fin'  ).html(tab_infos[4]);
        }
        $('#periode_import').html($('#f_periode_import option:selected').text());
        $('#ajax_msg_'+f_action).removeAttr("class").html('');
        $('#ajax_msg_confirm').removeAttr("class").html('');
        $('#comfirm_'+f_action).show(0);
        $.fancybox( { 'href':'#zone_confirmer' , onStart:function(){$('#zone_confirmer').css("display","block");} , onClosed:function(){$('#zone_confirmer').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
        initialiser_compteur();
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Confirmation du traitement du fichier issu de SIÈCLE ou de GEPI
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#confirmer_import').click
    (
      function()
      {
        $('#zone_confirmer button').prop('disabled',true);
        $('#ajax_msg_confirm').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+'traitement_'+f_action+'&f_periode='+id_periode_import,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#zone_confirmer button').prop('disabled',false);
              $('#ajax_msg_confirm').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              $('#zone_confirmer button').prop('disabled',false);
              if(responseHTML.substring(0,4)!='<tr>')
              {
                $('#ajax_msg_confirm').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#zone_saisir h2').html('Résultat du traitement');
                $('#titre_saisir').html('');
                $('#table_saisir tbody').html(responseHTML);
                $('#zone_saisir form').hide(0);
                $.fancybox( { 'href':'#zone_saisir' , onStart:function(){$('#zone_saisir').css("display","block");} , onClosed:function(){$('#zone_saisir').css("display","none");} , 'minWidth':600 , 'centerOnScroll':true } );
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
      if(responseHTML.substring(0,4)!='<tr ')
      {
        $('#ajax_msg_manuel').removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        $('#ajax_msg_manuel').removeAttr("class").html('');
        $('#zone_saisir h2').html('Saisie des absences et retards');
        $('#titre_saisir').html($('#f_periode option:selected').text()+' | '+$('#f_groupe option:selected').text());
        $('#table_saisir tbody').html(responseHTML);
        $('#ajax_msg_saisir').removeAttr("class").html('&nbsp;');
        $('#zone_saisir form').show(0);
        $.fancybox( { 'href':'#zone_saisir' , onStart:function(){$('#zone_saisir').css("display","block");} , onClosed:function(){$('#zone_saisir').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
        initialiser_compteur();
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Modification d'une saisie : alerter besoin d'enregistrer
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_saisir').on
    (
      'change',
      'input[type=text]',
      function()
      {
        $('#ajax_msg_saisir').removeAttr("class").addClass("alerte").html('Penser à enregistrer les modifications !');
        $('#fermer_zone_saisir').removeAttr("class").addClass("annuler").html('Annuler / Retour');
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Intercepter la touche entrée
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_saisir').on
    (
      'keyup',
      'input[type=text]',
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

    $('#Enregistrer_saisies').click
    (
      function()
      {
        $('#zone_saisir button').prop('disabled',true);
        // Récupérer les infos
        var tab_infos = new Array();
        $("#table_saisir tbody tr").each
        (
          function()
          {
            var user_id = $(this).attr('id').substring(3);
            tab_infos.push( user_id + '.' + $('#td1_'+user_id).val() + '.' + $('#td2_'+user_id).val() + '.' + $('#td3_'+user_id).val() + '.' + $('#td4_'+user_id).val() );
          }
        );
        $('#ajax_msg_saisir').removeAttr("class").addClass("loader").html("En cours&hellip;");
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
              $('#zone_saisir button').prop('disabled',false);
              $('#ajax_msg_saisir').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $('#zone_saisir button').prop('disabled',false);
              if(responseHTML!='ok')
              {
                $('#ajax_msg_saisir').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_msg_saisir').removeAttr("class").addClass("valide").html("Saisies enregistrées !");
                $('#fermer_zone_saisir').removeAttr("class").addClass("retourner").html('Retour');
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur un bouton pour fermer un cadre
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_confirmer').on( 'click' , '#fermer_zone_confirmer' , function(){ $.fancybox.close(); return false; } );
    $('#zone_saisir'   ).on( 'click' , '#fermer_zone_saisir'    , function(){ $.fancybox.close(); return false; } );

  }
);
