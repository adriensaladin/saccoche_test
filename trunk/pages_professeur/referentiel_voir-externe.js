/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Inspection de l'URL : l'ajout d'un hash indique un retour de l'iframe pour maj le compteur de session
//	Pour les explications : http://softwareas.com/cross-domain-communication-with-iframes (démo 1 : http://ajaxify.com/run/crossframe/ )
//	Attention, seule la 1e méthode fonctionne, la 2nde avec les iframes ajouté n'est pas compatible avec tous les navigateurs.
//	Voir aussi cette librairie : http://easyxdm.net/wp/
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function surveiller_url_et_hauteur()
		{
			$("body").everyTime
			('1ds', 'surveillance', function()
				{
					// Surveillance de l'URL
					var hashVal = window.location.hash.substr(1);
					if(hashVal!="")
					{
						window.location.hash='';
						if(hashVal=='maj_clock')
						{
							maj_clock(1);
						}
					}
					// Surveillance du redimensionnement
					var hauteur_entete = 200;
					var hauteur_object_mini = 350;
					var hauteur_document = hauteur_entete+hauteur_object_mini;
					// hauteur_document = $(document).height() pose problème si on retrécit la fenêtre en hauteur : il s'adapte très lentement...
					// D'où la procédure suivante récupérée à l'adresse http://www.howtocreate.co.uk/tutorials/javascript/browserwindow
					if( typeof( window.innerHeight ) == 'number' )
					{
						hauteur_document = window.innerHeight;	//Non-IE
					}
					else if( document.documentElement && document.documentElement.clientHeight )
					{
						hauteur_document = document.documentElement.clientHeight;	//IE 6+ in 'standards compliant mode'
					}
					else if( document.body && document.body.clientHeight )
					{
						hauteur_document = document.body.clientHeight;	//IE 4 compatible
					}
					var hauteur_object = Math.max(hauteur_document-hauteur_entete,hauteur_object_mini);
					$('#cadre').css('height',hauteur_object);
				}
			);
		}
		// Appel au chargement de la page
		surveiller_url_et_hauteur();
		// Et appeler l'objet
		adresse = url_debut + '?mode=object' + '&fichier=referentiel_voir' + '&structure_id=' + structure_id + '&structure_key=' + structure_key + '&adresse_retour=' + encodeURIComponent(document.location.href);	// Mettre href sinon c'est le dernier appel ajax (non visible dans la barre d'adresse) qui compte...
		if($('#object_container object').length)
		{
			$('#cadre').attr('data',adresse);
		}
		else
		{
			$('#cadre').attr('src',adresse);
		}
	}
);
