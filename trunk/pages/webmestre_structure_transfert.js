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
// Initialisation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var listing_id = new Array();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Demande d'export des bases => soumission du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_exporter').click
    (
      function()
      {
        if( $("#f_base input:checked").length==0 )
        {
          $('#ajax_msg_export').removeAttr("class").addClass("erreur").html("structure(s) manquante(s)");
          return false;
        }
        // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
        var bases = new Array(); $("#f_base input:checked").each(function(){bases.push($(this).val());});
        $("button").prop('disabled',true);
        $('#ajax_msg_export').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=exporter'+'&f_listing_id='+bases,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("button").prop('disabled',false);
              $('#ajax_msg_export').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $("button").prop('disabled',false);
              var tab_infos = responseHTML.split(']¤[');
              if( (tab_infos.length!=2) || (tab_infos[0]!='') )
              {
                $('#ajax_msg_export').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                var max = tab_infos[1];
                $('#ajax_msg_export').removeAttr("class").addClass("loader").html('Export en cours : étape 1 sur ' + max + '...');
                $('#puce_info_export').html('<li>Ne pas interrompre la procédure avant la fin du traitement !</li>');
                $('#ajax_export_num').html(1);
                $('#ajax_export_max').html(max);
                $('#div_info_export').show('fast');
                $('#zone_actions_export').hide('fast');
                exporter();
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Demande d'export des bases => étapes du traitement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function exporter()
    {
      var num = parseInt( $('#ajax_export_num').html() , 10 );
      var max = parseInt( $('#ajax_export_max').html() , 10 );
      // Appel en ajax
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=exporter'+'&num='+num+'&max='+max,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_export').removeAttr("class").addClass("alerte").html('Échec lors de la connexion au serveur !');
            $('#puce_info_export').html('<li><a id="a_reprise_export" href="#">Reprendre la procédure à l\'étape ' + num + ' sur ' + max + '.</a></li>');
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            var tab_infos = responseHTML.split(']¤[');
            if(tab_infos[1]=='ok')
            {
              num++;
              if(num > max)  // Utilisation de parseInt obligatoire sinon la comparaison des valeurs pose ici pb
              {
                var fichier_csv = tab_infos[2];
                var fichier_zip = tab_infos[3];
                $('#ajax_msg_export').removeAttr("class").addClass("valide").html('Export terminé.');
                var li1 = '<li><a target="_blank" href="'+fichier_csv+'">Récupérer le listing des bases exportées au format <em>CSV</em>.</a></li>';
                var li2 = '<li><a target="_blank" href="'+fichier_zip+'">Récupérer le fichier des bases sauvegardées au format <em>ZIP</em>.</a></li>';
                $('#puce_info_export').html(li1+li2);
                $('#zone_actions_export').show('fast');
                $("button").prop('disabled',false);
              }
              else
              {
                $('#ajax_export_num').html(num);
                $('#ajax_msg_export').removeAttr("class").addClass("loader").html('Export en cours : étape ' + num + ' sur ' + max + '...');
                $('#puce_info_export').html('<li>Ne pas interrompre la procédure avant la fin du traitement !</li>');
                exporter();
              }
            }
            else
            {
              $('#ajax_msg_export').removeAttr("class").addClass("alerte").html(tab_infos[0]);
              $('#puce_info_export').html('<li><a id="a_reprise_export" href="#">Reprendre la procédure à l\'étape ' + num + ' sur ' + max + '.</a></li>');
            }
          }
        }
      );
    }

    // live est utilisé pour prendre en compte les nouveaux éléments html créés

    $('#puce_info_export').on
    (
      'click',
      '#a_reprise_export',
      function()
      {
        num = $('#ajax_export_num').html();
        max = $('#ajax_export_max').html();
        $('#ajax_msg_export').removeAttr("class").addClass("loader").html('Export en cours : étape ' + num + ' sur ' + max + '...');
        $('#puce_info_export').html('<li>Ne pas interrompre la procédure avant la fin du traitement !</li>');
        exporter();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    if( $('#f_base input:checked').length )
    {
      $('#bouton_exporter').click();
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Réagir au clic sur un bouton pour uploader un fichier csv ou zip à importer
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Envoi du fichier avec jquery.ajaxupload.js
    new AjaxUpload
    ('#bouton_form_csv',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'f_action':'importer_csv'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier_csv,
        onSubmit: verifier_fichier_csv,
        onComplete: retourner_fichier_csv
      }
    );
    new AjaxUpload
    ('#bouton_form_zip',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'f_action':'importer_zip'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier_zip,
        onSubmit: verifier_fichier_zip,
        onComplete: retourner_fichier_zip
      }
    );

    function changer_fichier_csv(fichier_nom,fichier_extension)
    {
      $('#ajax_msg_csv').removeAttr("class").html('&nbsp;');
      return true;
    }

    function changer_fichier_zip(fichier_nom,fichier_extension)
    {
      $('#ajax_msg_zip').removeAttr("class").html('&nbsp;');
      return true;
    }

    function verifier_fichier_csv(fichier_nom,fichier_extension)
    {
      if (fichier_nom==null || fichier_nom.length<5)
      {
        $('#ajax_msg_csv').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
        return false;
      }
      else if ('.csv.txt.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1)
      {
        $('#ajax_msg_csv').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas une extension "csv" ou "txt".');
        return false;
      }
      else
      {
        $('button').prop('disabled',true);
        $('#ajax_msg_csv').removeAttr("class").addClass("loader").html("En cours&hellip;");
        return true;
      }
    }

    function verifier_fichier_zip(fichier_nom,fichier_extension)
    {
      if (fichier_nom==null || fichier_nom.length<5)
      {
        $('#ajax_msg_zip').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
        return false;
      }
      else if (fichier_extension!='zip')
      {
        $('#ajax_msg_zip').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas une extension "zip".');
        return false;
      }
      else
      {
        $('button').prop('disabled',true);
        $('#ajax_msg_zip').removeAttr("class").addClass("loader").html("En cours&hellip;");
        return true;
      }
    }

    function retourner_fichier_csv(fichier_nom,responseHTML)  // Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
    {
      $('button').prop('disabled',false);
      var tab_infos = responseHTML.split(']¤[');
      if( (tab_infos.length!=2) || (tab_infos[0]!='') )
      {
        $('#ajax_msg_csv').removeAttr("class").addClass("alerte").html(responseHTML);
        $('#div_zip , #div_import , #div_info_import , #structures').hide('fast');
      }
      else
      {
        initialiser_compteur();
        $('#ajax_msg_csv').removeAttr("class").addClass("valide").html("Fichier bien reçu ; "+tab_infos[1]+".");
        $('#div_import , #div_info_import , #structures').hide('fast');
        $('#ajax_msg_zip').removeAttr("class").html('&nbsp;');
        $('#div_zip').show('fast');
      }
    }

    function retourner_fichier_zip(fichier_nom,responseHTML)  // Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
    {
      $('button').prop('disabled',false);
      var tab_infos = responseHTML.split(']¤[');
      if( (tab_infos.length!=2) || (tab_infos[0]!='') )
      {
        $('#ajax_msg_zip').removeAttr("class").addClass("alerte").html(responseHTML);
        $('#div_import , #div_info_import , #structures').hide('fast');
      }
      else
      {
        initialiser_compteur();
        $('#ajax_msg_zip').removeAttr("class").addClass("valide").html("Fichier bien reçu ; sauvegarde(s) extraite(s).");
        $('#div_info_import , #structures').hide('fast');
        $('#div_import').show('fast');
        $('#ajax_import_num').html(1);
        $('#ajax_import_max').html(tab_infos[1]);
      }
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Demande d'import des bases => soumission du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_importer').click
    (
      function()
      {
        $("button").prop('disabled',true);
        var num = $('#ajax_import_num').html();
        var max = $('#ajax_import_max').html();
        $('#ajax_msg_import').removeAttr("class").addClass("loader").html('Import en cours : étape ' + num + ' sur ' + max + '...');
        $('#puce_info_import').html('<li>Ne pas interrompre la procédure avant la fin du traitement !</li>');
        $('#div_info_import').show('fast');
        $('#structures').hide('fast').children('#table_action').children('tbody').html('');
        importer();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Demande d'import des bases => étapes du traitement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function importer()
    {
      var num = parseInt( $('#ajax_import_num').html() , 10 );
      var max = parseInt( $('#ajax_import_max').html() , 10 );
      // Appel en ajax
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=importer'+'&num='+num+'&max='+max,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_import').removeAttr("class").addClass("alerte").html('Échec lors de la connexion au serveur !');
            $('#puce_info_import').html('<li><a id="a_reprise_import" href="#">Reprendre la procédure à l\'étape ' + num + ' sur ' + max + '.</a></li>');
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            var tab_infos = responseHTML.split(']¤[');
            if( (tab_infos.length==2) && (tab_infos[0]=='') )
            {
              num++;
              if(num > max)  // Utilisation de parseInt obligatoire sinon la comparaison des valeurs pose ici pb
              {
                $('#ajax_msg_import').removeAttr("class").addClass("valide").html('');
                $('#puce_info_import').html('<li>Import terminé !</li>');
                $('#ajax_msg_csv , #ajax_msg_zip , #ajax_msg_import').removeAttr("class").html('&nbsp;');
                $('#div_zip , #div_import').hide('fast');
                $('#structures').show('fast');
                $("button").prop('disabled',false);
              }
              else
              {
                $('#table_action tbody').append(tab_infos[1]);
                $('#ajax_import_num').html(num);
                $('#ajax_msg_import').removeAttr("class").addClass("loader").html('Import en cours : étape ' + num + ' sur ' + max + '...');
                $('#puce_info_import').html('<li>Ne pas interrompre la procédure avant la fin du traitement !</li>');
                importer();
              }
            }
            else
            {
              $('#ajax_msg_import').removeAttr("class").addClass("alerte").html(tab_infos[0]);
              $('#puce_info_import').html('<li><a id="a_reprise_import" href="#">Reprendre la procédure à l\'étape ' + num + ' sur ' + max + '.</a></li>');
            }
          }
        }
      );
    }

    $('#puce_info_import').on
    (
      'click',
      '#a_reprise_import',
      function()
      {
        var num = $('#ajax_import_num').html();
        var max = $('#ajax_import_max').html();
        $('#ajax_msg_import').removeAttr("class").addClass("loader").html('Import en cours : étape ' + num + ' sur ' + max + '...');
        $('#puce_info_import').html('<li>Ne pas interrompre la procédure avant la fin du traitement !</li>');
        importer();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Tout cocher ou tout décocher
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'click',
      'q.cocher_tout , q.cocher_rien',
      function()
      {
        var etat = ( $(this).attr('class').substring(7) == 'tout' ) ? true : false ;
        $('#table_action td.nu input[type=checkbox]').prop('checked',etat);
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un bouton pour effectuer une action sur les structures sélectionnées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var prompt_etapes_supprimer_selectionnees = {
      etape_1: {
        title   : 'Demande de confirmation (1/2)',
        html    : "Souhaitez-vous vraiment supprimer les bases des structures sélectionnées ?",
        buttons : {
          "Non, c'est une erreur !" : false ,
          "Oui, je confirme !" : true
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            event.preventDefault(); 
            $.prompt.goToState('etape_2');
            return false;
          }
        }
      },
      etape_2: {
        title   : 'Demande de confirmation (2/2)',
        html    : "Êtes-vous bien certain de vouloir supprimer ces bases ?<br /><b>Est-ce définitivement votre dernier mot ???</b>",
        buttons : {
          "Oui, j'insiste !" : true ,
          "Non, surtout pas !" : false
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            supprimer_structures_selectionnees(listing_id);
            return true;
          }
        }
      }
    };

    var supprimer_structures_selectionnees = function(listing_id)
    {
      $("button").prop('disabled',true);
      $('#ajax_supprimer_export').removeAttr("class").addClass("loader").html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=supprimer'+'&f_listing_id='+listing_id,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_supprimer_export').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
            $("button").prop('disabled',false);
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            if(responseHTML!='<ok>')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#ajax_supprimer_export').removeAttr("class").addClass("alerte").html(responseHTML);
            }
            else
            {
              $("#f_base input:checked").each
              (
                function()
                {
                  $(this).parent().remove();
                }
              );
              $('#ajax_supprimer_export').removeAttr("class").addClass("valide").html('Demande réalisée !');
              $("button").prop('disabled',false);
            }
          }
        }
      );
    };

    $('#zone_actions_export button').click
    (
      function()
      {
        // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
        listing_id = [];
        $("#f_base input:checked").each(function(){listing_id.push($(this).val());});
        if(!listing_id.length)
        {
          $('#ajax_supprimer_export').removeAttr("class").addClass("erreur").html("Aucune structure sélectionnée !");
          return false;
        }
        $('#ajax_supprimer_export').removeAttr("class").html('&nbsp;');
        var id = $(this).attr('id');
        if(id=='bouton_supprimer_export')
        {
          $.prompt(prompt_etapes_supprimer_selectionnees);
        }
        else
        {
          $('#listing_ids').val(listing_id);
          var tab = new Array;
          tab['bouton_newsletter_export'] = "webmestre_newsletter";
          tab['bouton_stats_export']      = "webmestre_statistiques";
          // tab['bouton_transfert_export']  = "webmestre_structure_transfert";
          var page = tab[id];
          var form = document.getElementById('structures');
          form.action = './index.php?page='+page;
          form.method = 'post';
          // form.target = '_blank';
          form.submit();
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un bouton pour effectuer une action sur les structures cochées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var prompt_etapes_supprimer_cochees = {
      etape_1: {
        title   : 'Demande de confirmation (1/2)',
        html    : "Souhaitez-vous vraiment supprimer les bases des structures cochées ?<br />Toutes les données associées seront perdues !",
        buttons : {
          "Non, c'est une erreur !" : false ,
          "Oui, je confirme !" : true
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            event.preventDefault(); 
            $.prompt.goToState('etape_2');
            return false;
          }
        }
      },
      etape_2: {
        title   : 'Demande de confirmation (2/2)',
        html    : "Êtes-vous bien certain de vouloir supprimer ces bases ?<br />Est-ce définitivement votre dernier mot ???",
        buttons : {
          "Oui, j'insiste !" : true ,
          "Non, surtout pas !" : false
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            supprimer_structures_cochees(listing_id);
            return true;
          }
        }
      }
    };

    var supprimer_structures_cochees = function(listing_id)
    {
      $("button").prop('disabled',true);
      $('#ajax_supprimer_import').removeAttr("class").addClass("loader").html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=supprimer'+'&f_listing_id='+listing_id,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_supprimer_import').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
            $("button").prop('disabled',false);
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            if(responseHTML!='<ok>')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#ajax_supprimer_import').removeAttr("class").addClass("alerte").html(responseHTML);
            }
            else
            {
              $("#table_action input[type=checkbox]:checked").each
              (
                function()
                {
                  $(this).parent().parent().remove();
                }
              );
              $('#ajax_supprimer_import').removeAttr("class").addClass("valide").html('Demande réalisée !');
              $("button").prop('disabled',false);
            }
          }
        }
      );
    };

    $('#zone_actions_import button').click
    (
      function()
      {
        // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
        listing_id = [];
        $("#table_action input[type=checkbox]:checked").each(function(){listing_id.push($(this).val());});
        if(!listing_id.length)
        {
          $('#ajax_supprimer_import').removeAttr("class").addClass("erreur").html("Aucune structure cochée !");
          return false;
        }
        $('#ajax_supprimer_import').removeAttr("class").html('&nbsp;');
        var id = $(this).attr('id');
        if(id=='bouton_supprimer_import')
        {
          $.prompt(prompt_etapes_supprimer_cochees);
        }
        else
        {
          $('#listing_ids').val(listing_id);
          var tab = new Array;
          tab['bouton_newsletter_import'] = "webmestre_newsletter";
          tab['bouton_stats_import']      = "webmestre_statistiques";
          // tab['bouton_transfert_import']  = "webmestre_structure_transfert";
          var page = tab[id];
          var form = document.getElementById('structures');
          form.action = './index.php?page='+page;
          form.method = 'post';
          // form.target = '_blank';
          form.submit();
        }
      }
    );

  }
);
