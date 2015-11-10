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

    var user_id    = 0;
    var user_texte = 'Tampon de l\'établissement';
    var partie     = '';

    // Réagir au changement du select
    $('#f_user').change
    (
      function()
      {
        $('#ajax_upload').removeAttr("class").html('&nbsp;');
        user_id    = $('#f_user option:selected').val();
        user_texte = $('#f_user option:selected').text();
        // maj du paramètre AjaxUpload (les paramètres n'étant pas directement modifiables...)
        uploader_signature['_settings']['data']['f_user_id']    = user_id;
        uploader_signature['_settings']['data']['f_user_texte'] = user_texte;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire form_mise_en_page
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var form_partie = {
      'f_coordonnees_adresse'   : 'coordonnees',
      'f_coordonnees_telephone' : 'coordonnees',
      'f_coordonnees_fax'       : 'coordonnees',
      'f_coordonnees_courriel'  : 'coordonnees',
      'f_coordonnees_url'       : 'coordonnees',
      'f_coordonnees_logo'      : 'coordonnees',
      'f_infos_responsables' : 'responsables',
      'f_nombre_exemplaires' : 'responsables',
      'f_horizontal_gauche' : 'positionnement',
      'f_horizontal_milieu' : 'positionnement',
      'f_horizontal_droite' : 'positionnement',
      'f_vertical_haut'     : 'positionnement',
      'f_vertical_milieu'   : 'positionnement',
      'f_vertical_bas'      : 'positionnement',
      'f_marge_gauche'      : 'positionnement',
      'f_marge_droite'      : 'positionnement',
      'f_marge_haut'        : 'positionnement',
      'f_marge_bas'         : 'positionnement',
      'f_archive_ajout_message_copie'      : 'archive',
      'f_archive_retrait_tampon_signature' : 'archive',
      'f_tampon_signature' : 'signature'
    };
    // Alerter sur la nécessité de valider
    $("#form_mise_en_page input , #form_mise_en_page select").change
    (
      function()
      {
        $('#ajax_msg_'+form_partie[$(this).attr('id')]).removeAttr("class").addClass("alerte").html("Enregistrer pour confirmer.");
      }
    );

    // Afficher / masquer p_enveloppe
    $("#f_infos_responsables").change
    (
      function()
      {
        if( $('#f_infos_responsables option:selected').val() == 'oui_force' )
        {
          $("#p_enveloppe").show();
        }
        else
        {
          $("#p_enveloppe").hide();
        }
      }
    );

    $('button.parametre').click
    (
      function()
      {
        partie = $(this).attr('id').substr(15); // bouton_valider_...
        if( (partie=='positionnement') && ( $('#f_infos_responsables option:selected').val() == 'oui_force' ) )
        {
          // Vérifier les dimensions de l'enveloppe
          var enveloppe_largeur = parseInt($('#f_horizontal_gauche').val(),10) + parseInt($('#f_horizontal_milieu').val(),10) + parseInt($('#f_horizontal_droite').val(),10) ;
          var enveloppe_hauteur = parseInt($('#f_vertical_haut'    ).val(),10) + parseInt($('#f_vertical_milieu'  ).val(),10) + parseInt($('#f_vertical_bas'     ).val(),10) ;
          if( (enveloppe_largeur<215) || (enveloppe_largeur>235) )
          {
            $('#ajax_msg_'+partie).removeAttr("class").addClass("erreur").html("Dimensions incorrectes : la longueur de l'enveloppe doit être comprise entre 21,5cm et 23,5cm.");
            return false;
          }
          if( (enveloppe_hauteur<105) || (enveloppe_hauteur>125) )
          {
            $('#ajax_msg_'+partie).removeAttr("class").addClass("erreur").html("Dimensions incorrectes : la hauteur de l'enveloppe doit être comprise entre 10,5cm et 12,5cm.");
            return false;
          }
        }
        $("button.parametre").prop('disabled',true);
        $('#ajax_msg_'+partie).removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+partie+'&'+$('#form_mise_en_page').serialize(),
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("button.parametre").prop('disabled',false);
              $('#ajax_msg_'+partie).removeAttr("class").addClass("alerte").html("Échec de la connexion !");
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $("button.parametre").prop('disabled',false);
              if(responseHTML!='ok')
              {
                $('#ajax_msg_'+partie).removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_msg_'+partie).removeAttr("class").addClass("valide").html("Données enregistrées !");
              }
              return false;
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Upload d'un fichier image avec jquery.ajaxupload.js
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Envoi du fichier avec jquery.ajaxupload.js ; on lui donne un nom afin de pouvoir changer dynamiquement le paramètre.
    var uploader_signature = new AjaxUpload
    ('#f_upload',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'f_action':'upload_signature','f_user_id':user_id,'f_user_texte':user_texte},
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
      if(responseHTML.substring(0,4)!='<li ')
      {
        $("#f_upload").prop('disabled',false);
        $('#ajax_upload').removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        initialiser_compteur();
        $("#f_upload").prop('disabled',false);
        $('#ajax_upload').removeAttr("class").addClass("valide").html('Image ajoutée');
        if($('#sgn_'+user_id).length)
        {
          $('#sgn_'+user_id).replaceWith(responseHTML);
        }
        else
        {
          $('#listing_signatures').prepend(responseHTML);
        }
        $('#sgn_none').remove();
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Appel en ajax pour supprimer le tampon de l'établissement | une signature
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#listing_signatures').on
    (
      'click',
      'q.supprimer',
      function()
      {
        var sgn_id = $(this).parent().attr('id').substr(4);
        $('#ajax_upload').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=delete_signature'+'&f_user_id='+sgn_id,
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
                $('#sgn_'+sgn_id).remove();
              }
            }
          }
        );
      }
    );

  }
);
