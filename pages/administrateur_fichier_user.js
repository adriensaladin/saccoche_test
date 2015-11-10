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

    var f_action = '';
    var f_mode   = '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Réagir au changement dans le premier formulaire (choix principal)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#f_choix_principal").change
    (
      function()
      {
        $('#ajax_msg').removeAttr("class").html('&nbsp;');
        // Masquer tout
        $('#span_mode , #fieldset_sconet_eleves_non , #fieldset_sconet_eleves_oui , #fieldset_sconet_parents_non , #fieldset_sconet_parents_oui , #fieldset_sconet_professeurs_directeurs_non , #fieldset_sconet_professeurs_directeurs_oui , #fieldset_base_eleves_eleves , #fieldset_base_eleves_parents , #fieldset_factos_eleves , #fieldset_factos_parents , #fieldset_tableur_professeurs_directeurs , #fieldset_tableur_eleves , #fieldset_tableur_parents').hide(0);
        // Puis afficher ce qu'il faut
        f_action = $(this).val();
        if(f_action!='')
        {
               if(f_action.indexOf('eleves')     !=-1) { $('#f_mode_'+check_eleve     ).prop('checked',true); }
          else if(f_action.indexOf('parents')    !=-1) { $('#f_mode_'+check_parent    ).prop('checked',true); }
          else if(f_action.indexOf('professeurs')!=-1) { $('#f_mode_'+check_professeur).prop('checked',true); }
          $('#span_mode').show(0);
          $('#fieldset_'+f_action).show(0);
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le lien pour revenir au formulaire principal
// ////////////////////////////////////////////////////////////////////////////////////////////////////
    $('#form_bilan').on
    (
      'click',
      '#bouton_annuler',
      function()
      {
        $('#form_choix').show();
        $('#form_bilan').html('<hr /><label id="ajax_msg">&nbsp;</label>');
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Tout cocher ou tout décocher
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_bilan').on
    (
      'click',
      'q.cocher_tout , q.cocher_rien',
      function()
      {
        var etat = ( $(this).attr('class').substring(7) == 'tout' ) ? true : false ;
        $(this).parent().parent().parent().find('input[type=checkbox]').prop('checked',etat);
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// depart -> step10     Réagir au clic sur un bouton pour envoyer un import (quel qu'il soit)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Envoi du fichier avec jquery.ajaxupload.js
    // Attention, la variable f_action n'est pas accessible dans les AjaxUpload
    var uploader_sconet_eleves = new AjaxUpload
    ('#sconet_eleves',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'f_step':10,'f_action':'sconet_eleves_oui','f_mode':'maj_plus_tard'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier_sconet,
        onComplete: retourner_fichier
      }
    );
    var uploader_sconet_parents = new AjaxUpload
    ('#sconet_parents',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'f_step':10,'f_action':'sconet_parents_oui','f_mode':'maj_plus_tard'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier_sconet,
        onComplete: retourner_fichier
      }
    );
    var uploader_sconet_professeurs_directeurs = new AjaxUpload
    ('#sconet_professeurs_directeurs',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'f_step':10,'f_action':'sconet_professeurs_directeurs_oui','f_mode':'maj_plus_tard'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier_sconet,
        onComplete: retourner_fichier
      }
    );
    var uploader_base_eleves_eleves = new AjaxUpload
    ('#base_eleves_eleves',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'f_step':10,'f_action':'base_eleves_eleves','f_mode':'maj_plus_tard'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier_tableur,
        onComplete: retourner_fichier
      }
    );
    var uploader_base_eleves_parents = new AjaxUpload
    ('#base_eleves_parents',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'f_step':10,'f_action':'base_eleves_parents','f_mode':'maj_plus_tard'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier_tableur,
        onComplete: retourner_fichier
      }
    );
    var uploader_factos_eleves = new AjaxUpload
    ('#factos_eleves',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'f_step':10,'f_action':'factos_eleves','f_mode':'maj_plus_tard'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier_tableur,
        onComplete: retourner_fichier
      }
    );
    var uploader_factos_parents = new AjaxUpload
    ('#factos_parents',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'f_step':10,'f_action':'factos_parents','f_mode':'maj_plus_tard'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier_tableur,
        onComplete: retourner_fichier
      }
    );
    var uploader_tableur_professeurs_directeurs = new AjaxUpload
    ('#tableur_professeurs_directeurs',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'f_step':10,'f_action':'tableur_professeurs_directeurs','f_mode':'maj_plus_tard'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier_tableur,
        onComplete: retourner_fichier
      }
    );
    var uploader_tableur_eleves = new AjaxUpload
    ('#tableur_eleves',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'f_step':10,'f_action':'tableur_eleves','f_mode':'maj_plus_tard'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier_tableur,
        onComplete: retourner_fichier
      }
    );
    var uploader_tableur_parents = new AjaxUpload
    ('#tableur_parents',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'f_step':10,'f_action':'tableur_parents','f_mode':'maj_plus_tard'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier_tableur,
        onComplete: retourner_fichier
      }
    );

    function changer_fichier(fichier_nom,fichier_extension)
    {
      // Ne sachant pas identifier la fonction d'appel, je mes à jour toutes les variables...
      f_mode = $('input[name=f_mode]:checked').val();
      uploader_sconet_eleves[                 '_settings']['data']['f_mode'] = f_mode;
      uploader_sconet_parents[                '_settings']['data']['f_mode'] = f_mode;
      uploader_sconet_professeurs_directeurs[ '_settings']['data']['f_mode'] = f_mode;
      uploader_base_eleves_eleves[            '_settings']['data']['f_mode'] = f_mode;
      uploader_base_eleves_parents[           '_settings']['data']['f_mode'] = f_mode;
      uploader_factos_eleves[                 '_settings']['data']['f_mode'] = f_mode;
      uploader_factos_parents[                '_settings']['data']['f_mode'] = f_mode;
      uploader_tableur_professeurs_directeurs['_settings']['data']['f_mode'] = f_mode;
      uploader_tableur_eleves[                '_settings']['data']['f_mode'] = f_mode;
      uploader_tableur_parents[               '_settings']['data']['f_mode'] = f_mode;
      // suite normale
      $('#ajax_msg').removeAttr("class").html('&nbsp;');
      $('#ajax_retour').html("&nbsp;");
      return true;
    }

    function verifier_fichier_sconet(fichier_nom,fichier_extension)
    {
      if (fichier_nom==null || fichier_nom.length<5)
      {
        $('#ajax_msg').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
        return false;
      }
      else if ('.xml.zip.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1)
      {
        $('#ajax_msg').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas une extension "xml" ou "zip".');
        return false;
      }
      else
      {
        $('button').prop('disabled',true);
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        return true;
      }
    }

    function verifier_fichier_tableur(fichier_nom,fichier_extension)
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
        $('button').prop('disabled',true);
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        return true;
      }
    }

    function retourner_fichier(fichier_nom,responseHTML)  // Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
    {
      $('button').prop('disabled',false);
      if(responseHTML.substring(0,3)!='<hr') // <hr /> transformé en <hr> ...
      {
        $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        initialiser_compteur();
        var texte1 = $('#f_choix_principal option:selected').parent('optgroup').attr('label');
        var texte2 = $('#f_choix_principal option:selected').text();
        $('#form_choix').hide();
        $('#form_bilan').html('<p><input name="report_objet" readonly size="80" value="'+texte1.substring(0,texte1.indexOf('(')-1)+' &rarr; '+texte2.substring(0,texte2.indexOf('(')-1)+'" class="b" /> <button id="bouton_annuler" class="retourner">Annuler / Retour</button></p>'+responseHTML);
        $("#step1").addClass("on");
      }
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// step10 -> step20                              Passer à l'extraction des données
// step20 -> step31                              Passer à l'analyse des données des classes
// step32 -> step41                              Passer à l'analyse des données des groupes
// step20 | step32 | step42 -> step51            Passer à l'analyse des données des utilisateurs
// step52 | step53 -> step61                     Passer aux ajouts d'affectations éventuelles (Sconet uniquement)
// step52 | step53 -> step71                     Passer aux adresses des parents
// step72 -> step81                              Passer aux liens de responsabilité des parents
// step52 | step53 | step62 | step82 -> step90   Nettoyage des fichiers temporaires
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_bilan').on
    (
      'click',
      '#passer_etape_suivante',
      function()
      {
        var hash = extract_hash( $(this).attr('href') );
        var li_step = hash.substring(4,5); // 'step' + numero
        var f_step  = hash.substring(4); // 'step' + numero
        $("#step li").removeAttr("class");
        $('#form_bilan fieldset table').remove();
        $("#step"+li_step).addClass("on");
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_step='+f_step+'&f_action='+f_action+'&f_mode='+f_mode,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              if(responseHTML.substring(0,15)!='<p><label class')
              {
                $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_msg').removeAttr("class").html('&nbsp;');
                $('#form_bilan fieldset').html(responseHTML);
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// step31 -> step32     Envoyer les actions sur les classes
// step41 -> step42     Envoyer les actions sur les groupes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_bilan').on
    (
      'click',
      '#envoyer_infos_regroupements',
      function()
      {
        nb_pb = 0;
        $("#form_bilan input:checked").each
        (
          function()
          {
            var infos = $(this).attr('id');
            var mode = infos.substring(0,3);
            if( mode == 'add' )
            {
              var ref = infos.substring(4);
              if( (!$('#'+'add_niv_'+ref).val()) || (!$('#'+'add_nom_'+ref).val()) )
              {
                nb_pb++;
              }
            }
          }
        );
        if(nb_pb)
        {
          var s = (nb_pb>1) ? 's' : '';
          $('#ajax_msg').removeAttr("class").addClass("erreur").html(nb_pb+' ligne'+s+' de formulaire à compléter.');
          return false;
        }
        else
        {
          var f_step = $(this).attr('href').substring(5);
          // Grouper les données des groupes dans un champ unique par groupe afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
          // En effet, un lycée peut avoir plus de 300 groupes, et avec 4 champs par groupe on dépasse la limitation usuelle de 1000 champs...
          var f_del = new Array();
          var f_add = new Array();
          var sep = encodeURIComponent(']¤[');
          $("#form_bilan input:checked").each
          (
            function()
            {
              var infos = $(this).attr('id');
              var mode = infos.substring(0,3);
              var id   = infos.substring(4); // add_ | del_
              if( mode == 'del' )
              {
                f_del.push(id);
              }
              else if( mode == 'add' )
              {
                var ref = $('#add_ref_'+id).val();
                var niv = $('#add_niv_'+id).val();
                var nom = $('#add_nom_'+id).val();
                f_add.push(id+sep+niv+sep+encodeURIComponent(ref)+sep+encodeURIComponent(nom));
              }
            }
          );
          $('#form_bilan fieldset table').hide(0);
          $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
          $.ajax
          (
            {
              type : 'POST',
              url : 'ajax.php?page='+PAGE,
              data : 'csrf='+CSRF+'&f_step='+f_step+'&f_action='+f_action+'&f_mode='+f_mode+'&f_del='+f_del+'&f_add='+f_add,
              dataType : "html",
              error : function(jqXHR, textStatus, errorThrown)
              {
                $('#ajax_msg').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
                return false;
              },
              success : function(responseHTML)
              {
                initialiser_compteur();
                if(responseHTML.substring(0,25)!='<p><label class="valide">')
                {
                  $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
                }
                else
                {
                  $('#ajax_msg').removeAttr("class").html('&nbsp;');
                  $('#form_bilan fieldset').html(responseHTML);
                }
              }
            }
          );
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// step51 -> step52     Envoyer les actions sur les utilisateurs
// step61 -> step62     Envoyer les actions sur les ajouts d'affectations éventuelles
// step71 -> step72     Envoyer les actions sur les ajouts d'affectations éventuelles
// step81 -> step82     Envoyer les modifications éventuelles sur les liens de responsabilité des parents
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_bilan').on
    (
      'click',
      '#envoyer_infos_utilisateurs',
      function()
      {
        var f_step = $(this).attr('href').substring(5);
        // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
        var f_check = new Array();
        $("#form_bilan input:checked").each
        (
          function()
          {
            f_check.push($(this).attr('id'));
          }
        );
        $('#form_bilan fieldset table').hide(0);
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_step='+f_step+'&f_action='+f_action+'&f_mode='+f_mode+'&f_check='+f_check,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              if(responseHTML.substring(0,25)!='<p><label class="valide">')
              {
                $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_msg').removeAttr("class").html('&nbsp;');
                $('#form_bilan fieldset').html(responseHTML);
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// step52 -> step53     Récupérer les identifiants des nouveaux utilisateurs
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_bilan').on
    (
      'click',
      'a.step53',
      function()
      {
        $('#form_bilan fieldset table').remove();
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_step=53'+'&f_action='+f_action+'&f_mode='+f_mode+'&'+$("#form_bilan").serialize(),
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              if(responseHTML.substring(0,25)!='<p><label class="alerte">')
              {
                $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_msg').removeAttr("class").html('&nbsp;');
                $('#form_bilan fieldset').html(responseHTML);
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// step90 -> step0
// Retour au départ
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_bilan').on
    (
      'click',
      '#retourner_depart',
      function()
      {
        $('#bouton_annuler').click();
      }
    );

  }
);
