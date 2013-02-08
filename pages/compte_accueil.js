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

    // Ajout alerte si usage frame / iframe
    if(top.frames.length!=0)
    {
      $('h1').after('<hr /><div class="probleme">L\'usage de cadres (frame/iframe) pour afficher <em>SACoche</em> peut entrainer des dysfonctionnements.<br /><a href="'+location.href+'" class="lien_ext">Ouvrir <em>SACoche</em> dans un nouvel onglet.</a></div>');
      format_liens('#cadre_bas');
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur une image-lien afin d'afficher ou de masquer un élément de la page d'accueil
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('a[href=#toggle_accueil]').click
    (
      function()
      {
        var type = $(this).attr('class').substring(3); // 'to_' + type
        var src  = $(this).children('img').attr('src');
        var symb1 = ( src.indexOf("plus") > 0 ) ? 'plus' : 'moins' ;
        var symb2 = ( symb1=='moins' ) ? 'plus' : 'moins' ;
        $('#'+type+'_'+symb1).hide(0);
        $('#'+type+'_'+symb2).show(0);
        // Au passage, une requête ajax discrète pour mémoriser cette préférence
        var etat = ( symb1=='moins' ) ? 0 : 1 ;
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_type='+type+'&f_etat='+etat,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+'Échec de la connexion !\nChoix non mémorisé.'+'</label>' , {'centerOnScroll':true} );
            },
            success : function(responseHTML)
            {
              if(responseHTML!='ok')
              {
                $.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
              }
            }
          }
        );
        return false;
      }
    );

  }
);
