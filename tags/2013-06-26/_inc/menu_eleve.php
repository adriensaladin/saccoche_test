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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Menu [eleve] à mettre en session
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Le menu complet ; attention : en cas de changement du nom d'un menu, répercuter la modif dans tout le fichier (§ Adaptations).

$tab_menu = array
(
  "Informations" => array
  (
    "Accueil"               => array( 'class' => 'compte_accueil'   , 'href' => 'page=compte_accueil'                   ),
    "Données personnelles"  => array( 'class' => 'compte_cnil'      , 'href' => 'page=compte_cnil'                      ),
    "Algorithme de calcul"  => array( 'class' => 'algorithme_voir'  , 'href' => 'page=consultation_algorithme'          ),
    "Dates des périodes"    => array( 'class' => 'periode_groupe'   , 'href' => 'page=consultation_groupe_periode'      ),
    "Référentiels en place" => array( 'class' => 'referentiel_voir' , 'href' => 'page=consultation_referentiel_interne' )
  ),
  "Paramétrages" => array
  (
    "Mot de passe" => array( 'class' => 'compte_password'   , 'href' => 'page=compte_password'   ),
    "Daltonisme"   => array( 'class' => 'compte_daltonisme' , 'href' => 'page=compte_daltonisme' )
  ),
  "Évaluations" => array
  (
    "Liste des évaluations"            => array( 'class' => 'evaluation_voir'    , 'href' => 'page=evaluation_voir'          ),
    "Demandes d'évaluations formulées" => array( 'class' => 'evaluation_demande' , 'href' => 'page=evaluation_demande_eleve' )
  ),
  "Relevés / Synthèses" => array
  (
    "Grille d'items d'un référentiel"   => array( 'class' => 'releve_grille'   , 'href' => 'page=releve&amp;section=grille_referentiel'    ),
    "Relevé d'items d'une matière"      => array( 'class' => 'releve_items'    , 'href' => 'page=releve&amp;section=items_matiere'         ),
    "Relevé d'items pluridisciplinaire" => array( 'class' => 'releve_items'    , 'href' => 'page=releve&amp;section=items_multimatiere'    ),
 // "Bilan chronologique"               => array( 'class' => 'releve_chrono'   , 'href' => 'page=releve&amp;section=bilan_chronologique'   ),
 // Penser aussi aux restrictions d'accès
    "Synthèse d'une matière"            => array( 'class' => 'releve_synthese' , 'href' => 'page=releve&amp;section=synthese_matiere'      ),
    "Synthèse pluridisciplinaire"       => array( 'class' => 'releve_synthese' , 'href' => 'page=releve&amp;section=synthese_multimatiere' ),
    "Relevé de maîtrise du socle"       => array( 'class' => 'releve_socle'    , 'href' => 'page=releve&amp;section=socle'                 )
  ),
  "Bilans officiels" => array
  (
    "Archives consultables" => array( 'class' => 'officiel_voir_archive' , 'href' => 'page=officiel_voir_archive' )
  )
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Adaptations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Voir et simuler l'algorithme de calcul.
if(!test_user_droit_specifique($_SESSION['DROIT_VOIR_ALGORITHME']))
{
  $tab_menu["Informations"]["Algorithme de calcul"]['class'] .= ' disabled';
}

// Voir les référentiels en place (dans l'établissement) (pas de restriction pour le profil [administrateur]).
if(!test_user_droit_specifique($_SESSION['DROIT_VOIR_REFERENTIELS']))
{
  $tab_menu["Informations"]["Référentiels en place"]['class'] .= ' disabled';
}

// Changer son mot de passe (pas de restriction pour les profils [administrateur] et [webmestre]).
if(!test_user_droit_specifique($_SESSION['DROIT_MODIFIER_MDP']))
{
  $tab_menu["Paramétrages"]["Mot de passe"]['class'] .= ' disabled';
}

// Grille d'items d'un référentiel.
if(!test_user_droit_specifique($_SESSION['DROIT_VOIR_GRILLES_ITEMS']))
{
  $tab_menu["Relevés / Synthèses"]["Grille d'items d'un référentiel"]['class'] .= ' disabled';
}

// Relevé de maîtrise du socle (profils [parent] et [eleve] uniquement).
if(!$_SESSION['LISTE_PALIERS_ACTIFS'] || !test_user_droit_specifique($_SESSION['DROIT_SOCLE_ACCES']))
{
  $tab_menu["Relevés / Synthèses"]["Relevé de maîtrise du socle"]['class'] .= ' disabled';
}

// Archives consultables des bilans officiels (profils [parent] et [eleve] uniquement).
$tab_droits = array( 'FICHE_BREVET' , 'OFFICIEL_RELEVE' , 'OFFICIEL_BULLETIN' , 'OFFICIEL_SOCLE' );
$droit_voir_archives_pdf = FALSE;
foreach($tab_droits as $droit)
{
  $droit_voir_archives_pdf = $droit_voir_archives_pdf || test_user_droit_specifique($_SESSION['DROIT_'.$droit.'_VOIR_ARCHIVE']) ;
}
if(!$droit_voir_archives_pdf)
{
    $tab_menu["Bilans officiels"]["Archives consultables"]['class'] .= ' disabled';
}

?>