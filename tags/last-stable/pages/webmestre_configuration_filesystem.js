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
    // Droits du système de fichiers - Choix UMASK
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_umask').click
    (
      function()
      {
        $('button').prop('disabled',true);
        $('#ajax_umask').removeAttr("class").addClass("loader").html("En cours&hellip;");
        var umask = $('#select_umask option:selected').val();
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=choix_umask'+'&f_umask='+umask,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button').prop('disabled',false);
              $('#ajax_umask').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              $('button').prop('disabled',false);
              if(responseHTML=='ok')
              {
                var tab_chmod = new Array();
                tab_chmod['000'] = '777 / 666';
                tab_chmod['002'] = '775 / 664';
                tab_chmod['022'] = '755 / 644';
                tab_chmod['026'] = '751 / 640';
                $(info_chmod).html(tab_chmod[umask]);
                $('#ajax_umask').removeAttr("class").addClass("valide").html('Choix enregistré !');
                initialiser_compteur();
              }
              else
              {
                $('#ajax_umask').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              return false;
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Droits du système de fichiers - Appliquer CHMOD
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_chmod').click
    (
      function()
      {
        $('button').prop('disabled',true);
        $('#ajax_chmod').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=appliquer_chmod',
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button').prop('disabled',false);
              $('#ajax_chmod').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              $('button').prop('disabled',false);
              var tab_infos = responseHTML.split(']¤[');
              if( (tab_infos.length!=2) || (tab_infos[0]!='') )
              {
                $('#ajax_chmod').removeAttr("class").addClass("alerte").html(tab_infos[0]);
                return false;
              }
              else
              {
                $('#ajax_chmod').removeAttr("class").addClass("valide").html('Procédure terminée !');
                $.fancybox( { 'href':tab_infos[1] , 'type':'iframe' , 'width':'80%' , 'height':'80%' , 'centerOnScroll':true } );
                initialiser_compteur();
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Vérification des droits en écriture
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_droit').click
    (
      function()
      {
        $('button').prop('disabled',true);
        $('#ajax_droit').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=verif_droits',
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button').prop('disabled',false);
              $('#ajax_droit').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              $('button').prop('disabled',false);
              var tab_infos = responseHTML.split(']¤[');
              if( (tab_infos.length!=2) || (tab_infos[0]!='') )
              {
                $('#ajax_droit').removeAttr("class").addClass("alerte").html(tab_infos[0]);
                return false;
              }
              else
              {
                $('#ajax_droit').removeAttr("class").addClass("valide").html('Vérification terminée !');
                $.fancybox( { 'href':tab_infos[1] , 'type':'iframe' , 'width':'80%' , 'height':'80%' , 'centerOnScroll':true } );
                initialiser_compteur();
              }
            }
          }
        );
      }
    );

  }
);
