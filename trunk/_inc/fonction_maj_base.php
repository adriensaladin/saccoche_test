<?php
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

// Fonction pour mettre à jour la base. Ce script est appelé :
// + par un administrateur après une restauration de la base (automatique)
// + par un utilisateur se connectant si besoin il y a (automatique)

function maj_base()
{
	/*
		à compléter au fur et à mesure pour passer d'une version à une autre sur le modèle suivant...
		
		// Récupérer la version de la base ; si champ vide (ça ne devrait pas...), affecter la valeur minimale
		
		if($version_actuelle=='2010-05-15')
		{
			// script pour migrer vers la version suivante, y compris la mise à jour du champ "version_base" justement
			$version_actuelle = '2010-..-..';
		}
	*/
}

?>