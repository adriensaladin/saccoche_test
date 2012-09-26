<?php
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

// Fichier appelé pour l'affichage d'un relevé HTML enregistré temporairement.
// Passage en GET d'un paramètre pour savoir quelle page charger.

// Atteste l'appel de cette page avant l'inclusion d'une autre
define('SACoche','releve_pdf');

// Constantes / Configuration serveur / Autoload classes / Fonction de sortie
require('./_inc/_loader.php');

header('Content-Type: text/html; charset=utf-8');

// Ouverture de la session et gestion des droits d'accès
$PAGE = 'releve_pdf';
require(CHEMIN_DOSSIER_INCLUDE.'tableau_droits.php');
if(!isset($tab_droits[$PAGE]))
{
	exit_error( 'Droits manquants' /*titre*/ , 'Droits de la page "'.$PAGE.'" manquants.<br />Les droits de cette page n\'ont pas été attribués dans le fichier'.FileSystem::fin_chemin(CHEMIN_DOSSIER_INCLUDE.'tableau_droits.php') /*contenu*/ );
}
Session::execute($tab_droits[$PAGE]);

// Autres fonctions à charger
require(CHEMIN_DOSSIER_INCLUDE.'fonction_divers.php');

// Paramètre transmis
$FICHIER = (isset($_GET['fichier'])) ? $_GET['fichier'] : '';

// Extraction des infos
list( $eleve_id , $BILAN_TYPE , $periode_id ) = explode( '_' , $FICHIER) + Array( NULL , NULL , NULL );

$BILAN_TYPE = Clean::texte($BILAN_TYPE);
$periode_id = Clean::entier($periode_id);
$eleve_id   = Clean::entier($eleve_id);

$tab_types = array( 'releve' , 'bulletin' , 'palier1' , 'palier2' , 'palier3' );

// Vérification des paramètres principaux

if( (!in_array($BILAN_TYPE,$tab_types)) || !$periode_id || !$eleve_id )
{
	exit('Erreur avec les données transmises !');
}

// Vérifications complémentaires

if(!isset($_SESSION['tmp_droit_voir_archive'][$eleve_id.$BILAN_TYPE]))
{
	exit('Erreur de droit d\'accès ! Veuillez n\'utiliser qu\'un onglet.');
}

$fichier_archive = CHEMIN_DOSSIER_OFFICIEL.$_SESSION['BASE'].DS.fabriquer_nom_fichier_bilan_officiel( $eleve_id , $BILAN_TYPE , $periode_id );
if(!is_file($fichier_archive))
{
	exit('Erreur : archive non trouvée sur ce serveur.');
}

// Copie du fichier pour préserver son anonymat

$fichier_copie_nom = 'officiel_'.$BILAN_TYPE.'_archive_'.$eleve_id.'_'.$periode_id.'_'.fabriquer_fin_nom_fichier__date_et_alea().'.pdf' ;
copy($fichier_archive,CHEMIN_DOSSIER_EXPORT.$fichier_copie_nom);

// Redirection du navigateur
header('Status: 302 Found', TRUE, 302);
header('Location: '.URL_DIR_EXPORT.$fichier_copie_nom);
exit();
?>
