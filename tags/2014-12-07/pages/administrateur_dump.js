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
    // Appel en ajax pour lancer une sauvegarde
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function sauvegarder(etape)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=sauvegarder'+'&etape='+etape,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $("button").prop('disabled',false);
            $('#ajax_msg_sauvegarde').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
            return false;
          },
          success : function(responseHTML)
          {
            if(responseHTML.substring(0,4)!='<li>')
            {
              $("button").prop('disabled',false);
              $('#ajax_msg_sauvegarde').removeAttr("class").addClass("alerte").html(responseHTML);
            }
            else
            {
              $('#ajax_info').append(responseHTML);
              initialiser_compteur();
              if(responseHTML.indexOf('en cours')!=-1)
              {
                etape++;
                sauvegarder(etape);
              }
              else
              {
                $("button").prop('disabled',false);
                $('#ajax_msg_sauvegarde').removeAttr("class").html('');
                $('#ajax_info').append('<li><label class="alerte">Pour des raisons de sécurité et de confidentialité, ce fichier sera effacé du serveur dans 1h.</label></li>');
              }
            }
          }
        }
      );
    }

    $('#bouton_sauvegarde').click
    (
      function()
      {
        $("button").prop('disabled',true);
        $('#ajax_msg_sauvegarde').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $('#ajax_msg_restauration').removeAttr("class").html('');
        $('#ajax_info').html('');
        initialiser_compteur();
        sauvegarder(1);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Upload d'un fichier image avec jquery.ajaxupload.js
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    new AjaxUpload
    ('#bouton_restauration',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'f_action':'uploader'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier,
        onComplete: retourner_fichier
      }
    );

    function changer_fichier(fichier_nom,fichier_extension)
    {
      $("button").prop('disabled',true);
      $("#ajax_info").html('');
      $('#ajax_msg_sauvegarde').removeAttr("class").html('');
      $('#ajax_msg_restauration').removeAttr("class").html('');
      return true;
    }

    function verifier_fichier(fichier_nom,fichier_extension)
    {
      if (fichier_nom==null || fichier_nom.length<5)
      {
        $("button").prop('disabled',false);
        $('#ajax_msg_restauration').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
        return false;
      }
      else if(fichier_extension.toLowerCase()!='zip')
      {
        $("button").prop('disabled',false);
        $('#ajax_msg_restauration').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas l\'extension zip.');
        return false;
      }
      else
      {
        $('#ajax_msg_restauration').removeAttr("class").addClass("loader").html("En cours&hellip;");
        return true;
      }
    }

    function retourner_fichier(fichier_nom,responseHTML)  // Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
    {
      if( (responseHTML.substring(0,26)!='<li><label class="valide">') && (responseHTML.substring(0,24)!='<LI><LABEL class=valide>') )
      {
        $("button").prop('disabled',false);
        $('#ajax_msg_restauration').removeAttr("class").html('');
        $('#ajax_info').html(responseHTML);
      }
      else
      {
        $.prompt(
          "Souhaitez-vous vraiment restaurer la base contenue dans le fichier "+fichier_nom+"&nbsp;?<br />Toute action effectuée depuis le moment de cette sauvegarde sera à refaire&nbsp;!!!<br />En particulier les saisies d'évaluations et les modifications de référentiels seront perdues&hellip;",
          {
            title   : 'Demande de confirmation',
            buttons : {
              "Non, c'est une erreur !" : false ,
              "Oui, je confirme !" : true
            },
            submit  : function(event, value, message, formVals) {
              if(value)
              {
                $('#ajax_msg_restauration').html('Demande traitée...');
                $('#ajax_info').html(responseHTML);
                initialiser_compteur();
                restaurer(1);
              }
              else
              {
                $("button").prop('disabled',false);
                $('#ajax_msg_restauration').removeAttr("class").addClass("alerte").html('Restauration annulée.');
              }
            }
          }
        );
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Appel en ajax pour lancer une restauration
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function restaurer(etape)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=restaurer'+'&etape='+etape,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $("button").prop('disabled',false);
            $('#ajax_msg_restauration').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
            return false;
          },
          success : function(responseHTML)
          {
            if(responseHTML.substring(0,4)!='<li>')
            {
              $("button").prop('disabled',false);
              $('#ajax_msg_restauration').removeAttr("class").addClass("alerte").html(responseHTML);
            }
            else
            {
              $('#ajax_info').append(responseHTML);
              initialiser_compteur();
              if(responseHTML.indexOf('en cours')!=-1)
              {
                etape++;
                restaurer(etape);
              }
              else
              {
                $("button").prop('disabled',false);
                $('#ajax_msg_restauration').removeAttr("class").html('');
                $('#ajax_info').append('<li><label class="alerte">Veuillez maintenant vous déconnecter / reconnecter pour mettre la session en conformité avec la base restaurée.</label></li>');
              }
            }
          }
        }
      );
    }

  }
);
