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
// Clic sur l'image pour Voir un référentiel de compétences
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'click',
      'q.voir',
      function()
      {
        ids = $(this).parent().attr('id');
        $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&ids='+ids,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+'Échec de la connexion !'+'</label>' , {'centerOnScroll':true} );
            },
            success : function(responseHTML)
            {
              if(responseHTML.substring(0,18)!='<ul class="ul_m1">')
              {
                $.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
              }
              else
              {
                initialiser_compteur();
                $.fancybox( '<p class="noprint">Afin de préserver l\'environnement, n\'imprimer qu\'en cas de nécessité !</p>'+responseHTML.replace('<ul class="ul_m2">','<q class="imprimer_arbre" title="Imprimer le référentiel." />'+'<ul class="ul_m2">') , {'centerOnScroll':true} );
              }
            }
          }
        );
      }
    );

  }
);
