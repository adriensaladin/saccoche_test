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
    // Alerter sur la nécessité de valider
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#form_debug input").change
    (
      function()
      {
        $('#ajax_debug').removeAttr("class").addClass("alerte").html("Enregistrer pour confirmer.");
      }
    );

    $("#form_phpCAS input").change
    (
      function()
      {
        $('#ajax_phpCAS').removeAttr("class").addClass("alerte").html("Enregistrer pour confirmer.");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Modifier les paramètres de debug
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_debug').click
    (
      function()
      {
        $('#bouton_debug').prop('disabled',true);
        $('#ajax_debug').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=modifier_debug'+'&'+$("form").serialize(),
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#bouton_debug').prop('disabled',false);
              $('#ajax_debug').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              $('#bouton_debug').prop('disabled',false);
              if(responseHTML=='ok')
              {
                $('#ajax_debug').removeAttr("class").addClass("valide").html('Choix enregistrés.');
                initialiser_compteur();
              }
              else
              {
                $('#ajax_debug').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              return false;
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Modifier les paramètres des logs phpCAS
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_phpCAS').click
    (
      function()
      {
        $('#bouton_phpCAS').prop('disabled',true);
        $('#ajax_phpCAS').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=modifier_phpCAS'+'&'+$('#form_phpCAS').serialize(),
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#bouton_phpCAS').prop('disabled',false);
              $('#ajax_phpCAS').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              $('#bouton_phpCAS').prop('disabled',false);
              if(responseHTML=='ok')
              {
                $('#ajax_phpCAS').removeAttr("class").addClass("valide").html('Choix enregistrés.');
                initialiser_compteur();
              }
              else
              {
                $('#ajax_phpCAS').removeAttr("class").addClass("alerte").html(responseHTML);
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

    $('#form_phpCAS').submit
    (
      function()
      {
        $("#bouton_phpCAS").click();
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Voir | Effacer un fichier de logs de phpCAS
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#fichiers_logs q').click
    (
      function()
      {
        var f_action  = $(this).attr('class');
        var f_fichier = $(this).parent().attr('id');
        $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+f_action+'&f_fichier='+f_fichier,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+'Échec de la connexion !'+'</label>' , {'centerOnScroll':true} );
              return false;
            },
            success : function(responseHTML)
            {
              if( (f_action=='supprimer') && (responseHTML=='ok') )
              {
                initialiser_compteur();
                $('#'+f_fichier).remove();
                $.fancybox.close();
              }
              else if( (f_action=='voir') && (responseHTML.substring(0,4)=='<ul ') )
              {
                initialiser_compteur();
                // Mis dans le div bilan et pas balancé directement dans le fancybox sinon le format_lien() nécessite un peu plus de largeur que le fancybox ne recalcule pas (et $.fancybox.update(); ne change rien).
                // Malgré tout, pour Chrome par exemple, la largeur est mal clculée et provoque des retours à la ligne, d'où le minWidth ajouté.
                $('#bilan').html(responseHTML);
                format_liens('#bilan');
                $.fancybox( { 'href':'#bilan' , onClosed:function(){$('#bilan').html("");} , 'centerOnScroll':true , 'minWidth':300 } );
              }
              else
              {
                $.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
              }
              return false;
            }
          }
        );
      }
    );

  }
);
