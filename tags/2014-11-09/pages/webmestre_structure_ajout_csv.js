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

    var listing_id     = new Array();
    var courriel_envoi = -1 ;
    var courriel_copie = -1 ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Réagir au clic sur un bouton pour uploader un fichier csv à importer
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

    function changer_fichier_csv(fichier_nom,fichier_extension)
    {
      $('#ajax_msg_csv').removeAttr("class").html('&nbsp;');
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

    function retourner_fichier_csv(fichier_nom,responseHTML)  // Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
    {
      $('button').prop('disabled',false);
      var tab_infos = responseHTML.split(']¤[');
      if( (tab_infos.length!=2) || (tab_infos[0]!='') )
      {
        $('#ajax_msg_csv').removeAttr("class").addClass("alerte").html(responseHTML);
        $('#div_import , #div_info_import , #structures').hide('fast');
      }
      else
      {
        initialiser_compteur();
        $('#ajax_msg_csv').removeAttr("class").addClass("valide").html("Fichier bien reçu ; "+tab_infos[1]+".");
        $('#div_info_import , #structures').hide('fast');
        $('#ajax_msg_import').removeAttr("class").html('&nbsp;');
        $('#div_import').show('fast');
        $('#ajax_import_num').html(1);
        $('#ajax_import_max').html(parseInt(tab_infos[1]),10);
      }
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Demande d'import du csv => soumission du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_importer').click
    (
      function()
      {
        courriel_envoi = $('#f_courriel_envoi').is(':checked') ? 1 : 0 ;
        courriel_copie = $('#f_courriel_copie').is(':checked') ? 1 : 0 ;
        if(courriel_envoi)
        {
          envoyer_demande_import_confirmee();
        }
        else
        {
          $.prompt(prompt_etapes_confirmer_import);
        }
      }
    );

    var prompt_etapes_confirmer_import = {
      etape_1: {
        title   : 'Demande de confirmation',
        html    : "Le mot de passe du premier administrateur, non récupérable ultérieurement, ne sera pas transmis !<br />Souhaitez-vous vraiment ne pas vouloir envoyer le courriel d'inscription ?",
        buttons : {
          "Non, c'est une erreur !" : false ,
          "Oui, je confirme !" : true
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            envoyer_demande_import_confirmee();
          }
        }
      }
    };

    function envoyer_demande_import_confirmee()
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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Demande d'import du csv => étapes du traitement
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
          data : 'csrf='+CSRF+'&f_action=ajouter'+'&num='+num+'&max='+max+'&f_courriel_envoi='+courriel_envoi+'&f_courriel_copie='+courriel_copie,
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
              $('#structures tbody').append(tab_infos[1]);
              if(num > max)  // Utilisation de parseInt obligatoire sinon la comparaison des valeurs pose ici pb
              {
                $('#ajax_msg_import').removeAttr("class").addClass("valide").html('');
                $('#puce_info_import').html('<li>Import terminé !</li>');
                $('#ajax_msg_csv , #ajax_msg_import').removeAttr("class").html('&nbsp;');
                $('#div_import').hide('fast');
                $('#structures').show('fast');
                $("button").prop('disabled',false);
              }
              else
              {
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
        num = $('#ajax_import_num').html();
        max = $('#ajax_import_max').html();
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
      $('#ajax_supprimer').removeAttr("class").addClass("loader").html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=supprimer'+'&f_listing_id='+listing_id,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_supprimer').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
            $("button").prop('disabled',false);
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            if(responseHTML!='<ok>')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#ajax_supprimer').removeAttr("class").addClass("alerte").html(responseHTML);
            }
            else
            {
              $("input[type=checkbox]:checked").each
              (
                function()
                {
                  $('#f_base option[value='+$(this).val()+']').remove();
                  $(this).parent().parent().remove();
                }
              );
              $('#ajax_supprimer').removeAttr("class").html('&nbsp;');
              $("button").prop('disabled',false);
            }
          }
        }
      );
    };

    $('#zone_actions button').click
    (
      function()
      {
        // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
        listing_id = [];
        $("#table_action input[type=checkbox]:checked").each(function(){listing_id.push($(this).val());});
        if(!listing_id.length)
        {
          $('#ajax_supprimer').removeAttr("class").addClass("erreur").html("Aucune structure cochée !");
          return false;
        }
        $('#ajax_supprimer').removeAttr("class").html('&nbsp;');
        var id = $(this).attr('id');
        if(id=='bouton_supprimer')
        {
          $.prompt(prompt_etapes_supprimer_cochees);
        }
        else
        {
          $('#listing_ids').val(listing_id);
          var tab = new Array;
          tab['bouton_newsletter'] = "webmestre_newsletter";
          // tab['bouton_stats']      = "webmestre_statistiques";
          // tab['bouton_transfert']  = "webmestre_structure_transfert";
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
