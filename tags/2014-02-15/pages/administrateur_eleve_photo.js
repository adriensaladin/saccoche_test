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
    // Actualiser l'affichage des vignettes élèves au changement du select
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_affichage()
    {
      $('#liste_eleves').html('');
      // On récupère le regroupement
      var groupe_val = $("#f_groupe option:selected").val();
      if(!groupe_val)
      {
        $('#ajax_msg').removeAttr("class").html("&nbsp;");
        return false
      }
      // Pour un directeur ou un administrateur, groupe_val est de la forme d3 / n2 / c51 / g44
      if(isNaN(parseInt(groupe_val,10)))
      {
        groupe_type = groupe_val.substring(0,1);
        groupe_id   = groupe_val.substring(1);
      }
      // Pour un professeur, groupe_val est un entier, et il faut récupérer la 1ère lettre du label parent
      else
      {
        groupe_type = $("#f_groupe option:selected").parent().attr('label').substring(0,1).toLowerCase();
        groupe_id   = groupe_val;
      }
      $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE+'&f_action=afficher',
          data : 'csrf='+CSRF+'&f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            if(responseHTML.substring(0,5)!='<div ')
            {
              $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
            }
            else
            {
              $('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
              $('#liste_eleves').html(responseHTML);
              // Mise en place des AjaxUpload
              $("#liste_eleves q.ajouter").each
              (
                function()
                {
                  // On boucle pour activer / desactiver les options du select.
                  var q_id = $(this).attr('id');
                  var user_id = q_id.substring(2); // "q_" + id
                  // Envoi du fichier avec jquery.ajaxupload.js
                  new AjaxUpload
                  ('#'+q_id,
                    {
                      action: 'ajax.php?page='+PAGE+'&f_action=envoyer_photo',
                      name: 'userfile',
                      data: {'csrf':CSRF,'f_user_id':user_id},
                      autoSubmit: true,
                      responseType: "html",
                      onChange: changer_fichier,
                      onSubmit: verifier_fichier,
                      onComplete: retourner_fichier
                    }
                  );
                }
              );
            }
          }
        }
      );
    }

    $("#f_groupe").change
    (
      function()
      {
        maj_affichage();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Upload d'un fichier zip avec jquery.ajaxupload.js
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Envoi du fichier avec jquery.ajaxupload.js ; on lui donne un nom afin de pouvoir changer dynamiquement le paramètre.
    var uploader_zip = new AjaxUpload
    ('#bouton_zip',
      {
        action: 'ajax.php?page='+PAGE+'&f_action=envoyer_zip',
        name: 'userfile',
        data: {'csrf':CSRF,'f_masque':'maj_plus_tard'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier_zip,
        onSubmit: verifier_fichier_zip,
        onComplete: retourner_fichier_zip
      }
    );

    function changer_fichier_zip(fichier_nom,fichier_extension)
    {
      $("button").prop('disabled',true);
      $('#ajax_msg_zip').removeAttr("class").html('');
      var masque = $("#f_masque").val();
      // Curieusement, besoin d'échapper l'échappement... (en PHP un échappement simple suffit)
      var reg_filename  = new RegExp("\\[(sconet_id|sconet_num|reference|nom|prenom|login|ent_id)\\]","g");
      var reg_extension = new RegExp("\\.(gif|jpg|jpeg|png)$","g");
      if( (!reg_filename.test(masque)) || (!reg_extension.test(masque)) )
      {
        $("button").prop('disabled',false);
        $('#ajax_msg_zip').removeAttr("class").addClass("erreur").html('Indiquer correctement la forme des noms des fichiers contenus dans l\'archive.');
        $('#f_masque').focus();
        return false;
      }
      uploader_zip['_settings']['data']['f_masque'] = masque;
      return true;
    }

    function verifier_fichier_zip(fichier_nom,fichier_extension)
    {
      if (fichier_nom==null || fichier_nom.length<5)
      {
        $("button").prop('disabled',false);
        $('#ajax_msg_zip').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
        return false;
      }
      else if(fichier_extension.toLowerCase()!='zip')
      {
        $("button").prop('disabled',false);
        $('#ajax_msg_zip').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas l\'extension zip.');
        return false;
      }
      else
      {
        $('#ajax_msg_zip').removeAttr("class").addClass("loader").html("En cours&hellip;");
        return true;
      }
    }

    function retourner_fichier_zip(fichier_nom,responseHTML)  // Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
    {
      $("button").prop('disabled',false);
      var tab_infos = responseHTML.split(']¤[');
      if( (tab_infos.length!=2) || (tab_infos[0]!='') )
      {
        $('#ajax_maj').removeAttr("class").addClass("alerte").html(tab_infos[0]);
        return false;
      }
      else
      {
        $('#ajax_msg_zip').removeAttr("class").addClass("valide").html('Demande traitée !');
        $.fancybox( { 'href':tab_infos[1] , 'type':'iframe' , 'width':'80%' , 'height':'80%' , 'centerOnScroll':true } );
        initialiser_compteur();
        maj_affichage();
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du clic sur un bouton pour envoyer une photo
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function changer_fichier(fichier_nom,fichier_extension)
    {
      afficher_masquer_images_action('hide');
      $('#ajax_msg').removeAttr("class").html('&nbsp;');
      return true;
    }

    function verifier_fichier(fichier_nom,fichier_extension)
    {
      if (fichier_nom==null || fichier_nom.length<5)
      {
        afficher_masquer_images_action('show');
        $('#ajax_msg').removeAttr("class").addClass("erreur").html('Chemin indiqué incorrect.');
        return false;
      }
      else if ('.gif.jpg.jpeg.png.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1)
      {
        afficher_masquer_images_action('show');
        $('#ajax_msg').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas une extension d\'image autorisée (gif jpg jpeg png).');
        return false;
      }
      else
      {
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        return true;
      }
    }

    function retourner_fichier(fichier_nom,responseHTML)  // Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
    {
      var tab_infos = responseHTML.split(']¤[');
      if(tab_infos[0]!='ok')
      {
        afficher_masquer_images_action('show');
        $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        initialiser_compteur();
        var user_id    = tab_infos[1];
        var img_width  = tab_infos[2];
        var img_height = tab_infos[3];
        var img_src    = tab_infos[4];
        $('#ajax_msg').removeAttr("class").html('&nbsp;');
        $('#q_'+user_id).parent().html('<img width="'+img_width+'" height="'+img_height+'" src="'+img_src+'" alt="" /><q class="supprimer" title="Supprimer cette photo (aucune confirmation ne sera demandée)."></q>');
        afficher_masquer_images_action('show');
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Appel en ajax pour supprimer un logo
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#liste_eleves').on
    (
      'click',
      'q.supprimer',
      function()
      {
        var memo_div = $(this).parent();
        var user_id = memo_div.parent().attr('id').substring(4); // "div_" + id
        afficher_masquer_images_action('hide');
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE+'&f_action=supprimer_photo',
            data : 'csrf='+CSRF+'&f_user_id='+user_id,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              afficher_masquer_images_action('show');
              if(responseHTML!='ok')
              {
                $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_msg').removeAttr("class").html('');
                memo_div.html('<q id="q_'+user_id+'" class="ajouter" title="Ajouter une photo."></q><img width="1" height="1" src="./_img/auto.gif" />');
                new AjaxUpload
                ('#q_'+user_id,
                  {
                    action: 'ajax.php?page='+PAGE+'&f_action=envoyer_photo',
                    name: 'userfile',
                    data: {'csrf':CSRF,'f_user_id':user_id},
                    autoSubmit: true,
                    responseType: "html",
                    onChange: changer_fichier,
                    onSubmit: verifier_fichier,
                    onComplete: retourner_fichier
                  }
                );
              }
            }
          }
        );
      }
    );

  }
);
