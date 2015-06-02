<?php
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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if(!isset($STEP))       {exit('Ce fichier ne peut être appelé directement !');}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 10 - Récupération du fichier (tous les cas)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Nom du fichier à extraire si c'est un fichier zippé
$alerte = '';
$nom_fichier_extrait = '';
if($import_origine=='sconet')
{
  if($import_profil=='eleve')
  {
    $nom_fichier_extrait = 'ElevesSansAdresses.xml';
    if( (isset($_FILES['userfile']['name'])) && (strpos($_FILES['userfile']['name'],'ElevesAvecAdresses')) )
    {
      $nom_fichier_extrait = 'ElevesAvecAdresses.xml';
      $alerte = '<p class="danger">Vous avez fourni le fichier <span class="u b">avec</span> adresses ! Vous pouvez toutefois poursuivre&hellip;</p>'.NL;
    }
  }
  else if($import_profil=='parent')
  {
    $nom_fichier_extrait = 'ResponsablesAvecAdresses.xml';
    if( (isset($_FILES['userfile']['name'])) && (strpos($_FILES['userfile']['name'],'ResponsablesSansAdresses')) )
    {
      $nom_fichier_extrait = 'ResponsablesSansAdresses.xml';
      $alerte = '<p class="danger">Vous avez fourni le fichier <span class="u b">sans</span> adresses ! Si vous poursuivez, sachez que les adresses ne seront pas trouvées&hellip;</p>'.NL;
    }
  }
  else if($import_profil=='professeur')
  {
    $annee_scolaire  = (date('n')>7) ? date('Y') : date('Y')-1 ;
    $nom_fichier_extrait = 'sts_emp_'.$_SESSION['WEBMESTRE_UAI'].'_'.$annee_scolaire.'.xml';
  }
}
$result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_dest /*fichier_nom*/ , $tab_extensions_autorisees , NULL /*tab_extensions_interdites*/ , NULL /*taille_maxi*/ , $nom_fichier_extrait /*filename_in_zip*/ );
if($result!==TRUE)
{
  exit('Erreur : '.$result);
}
// On affiche le bilan et les puces des étapes
echo'<hr />'.NL;
echo ($mode=='complet') ? '<p class="astuce">Affichage complet des analyses et des comptes-rendus.</p>' : '<p class="astuce">Analyse restreinte aux seules différences trouvées et comptes-rendus non détaillés.</p>'.NL;
echo afficher_etapes($import_origine,$import_profil);
echo'<hr />'.NL;
echo'<fieldset>'.NL;
echo  '<div><label class="valide">Votre fichier a été correctement réceptionné.</label></div>'.NL;
echo  $alerte;
echo  '<ul class="puce p"><li><a href="#step20" id="passer_etape_suivante">Passer à l\'étape 2.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL;
echo'</fieldset>'.NL;

?>
