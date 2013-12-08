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

// Fichier appelé pour l'affichage d'une archive PDF d'un bulletin.
// Passage en GET d'un paramètre pour savoir quelle page charger.

// Constantes / Configuration serveur / Autoload classes / Fonction de sortie
require('./_inc/_loader.php');

// Ouverture de la session et gestion des droits d'accès
if(!Session::verif_droit_acces(SACoche))
{
  exit_error( 'Droits manquants' /*titre*/ , 'Droits de la page "'.SACoche.'" manquants.<br />Les droits de cette page n\'ont pas été attribués dans le fichier "'.FileSystem::fin_chemin(CHEMIN_DOSSIER_INCLUDE.'tableau_droits.php').'".' /*contenu*/ , '' /*lien*/ );
}
Session::execute();

// Autres fonctions à charger
require(CHEMIN_DOSSIER_INCLUDE.'fonction_divers.php');

// Paramètre transmis
$FICHIER = (isset($_GET['fichier'])) ? $_GET['fichier'] : '';

// Extraction des infos
list( $eleve_id , $bilan_type , $periode_id ) = explode( '_' , $FICHIER) + Array( NULL , NULL , NULL );

$bilan_type = Clean::texte($bilan_type);
$periode_id = Clean::entier($periode_id);
$eleve_id   = Clean::entier($eleve_id);

$tab_types = array( 'releve' , 'bulletin' , 'palier1' , 'palier2' , 'palier3' , 'brevet' );

// Vérification des paramètres principaux

if(!$FICHIER)
{
  exit_error( 'Paramètre manquant' /*titre*/ , 'Page appelée sans indiquer la référence de l\'archive PDF à récupérer.' /*contenu*/ , '' /*lien*/ );
}

if( (!in_array($bilan_type,$tab_types)) || !$periode_id || !$eleve_id )
{
  exit_error( 'Paramètre incorrect' /*titre*/ , 'La valeur "'.html($FICHIER).'" transmise n\'est pas conforme.' /*contenu*/ , '' /*lien*/ );
}

// Vérifications complémentaires

if(!isset($_SESSION['tmp_droit_voir_archive'][$eleve_id.$bilan_type]))
{
  exit_error( 'Accès non autorisé' /*titre*/ , 'Cet appel n\'est valide que pour un utilisateur précis, connecté, et ayant affiché la page listant les archives disponibles.<br />Veuillez ne pas appeler ce lien dans un autre contexte (ni le transmettre à un tiers).' /*contenu*/ , '' /*lien*/ );
}

$fichier_archive = CHEMIN_DOSSIER_OFFICIEL.$_SESSION['BASE'].DS.fabriquer_nom_fichier_bilan_officiel( $eleve_id , $bilan_type , $periode_id );
if(!is_file($fichier_archive))
{
  exit_error( 'Document manquant' /*titre*/ , 'Archive non trouvée sur ce serveur.' /*contenu*/ , '' /*lien*/ );
}

// Copie du fichier pour préserver son anonymat

$fichier_copie_nom = 'officiel_'.$bilan_type.'_archive_'.$eleve_id.'_'.$periode_id.'_'.fabriquer_fin_nom_fichier__date_et_alea().'.pdf' ;
copy($fichier_archive,CHEMIN_DOSSIER_EXPORT.$fichier_copie_nom);

// Redirection du navigateur
header('Status: 302 Found', TRUE, 302);
header('Location: '.URL_DIR_EXPORT.$fichier_copie_nom);
exit();
?>
