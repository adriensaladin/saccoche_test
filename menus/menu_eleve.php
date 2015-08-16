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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Menu [eleve] à mettre en session
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Attention : en cas de changement d'indice d'un menu, répercuter la modif dans la partie Adaptations (en-dessous).

$tab_menu = array
(
  'information' => Lang::_("Informations"),
  'parametrage' => Lang::_("Paramétrages"),
  'evaluation'  => Lang::_("Évaluations"),
  'releve'      => Lang::_("Relevés / Synthèses"),
  'officiel'    => Lang::_("Bilans officiels"),
);

$tab_sous_menu = array
(
  'information' => array
  (
    'compte_accueil'                   => array( 'texte' => Lang::_("Accueil")               , 'class' => 'compte_accueil'   , 'href' => 'page=compte_accueil'                   ),
    'compte_cnil'                      => array( 'texte' => Lang::_("Données personnelles")  , 'class' => 'compte_cnil'      , 'href' => 'page=compte_cnil'                      ),
    'consultation_algorithme'          => array( 'texte' => Lang::_("Algorithme de calcul")  , 'class' => 'algorithme_voir'  , 'href' => 'page=consultation_algorithme'          ),
    'consultation_groupe_periode'      => array( 'texte' => Lang::_("Dates des périodes")    , 'class' => 'periode_groupe'   , 'href' => 'page=consultation_groupe_periode'      ),
    'consultation_notifications'       => array( 'texte' => Lang::_("Notifications reçues")  , 'class' => 'newsletter'       , 'href' => 'page=consultation_notifications'       ),
    'consultation_referentiel_interne' => array( 'texte' => Lang::_("Référentiels en place") , 'class' => 'referentiel_voir' , 'href' => 'page=consultation_referentiel_interne' ),
  ),
  'parametrage' => array
  (
    'compte_password'   => array( 'texte' => Lang::_("Mot de passe")                 , 'class' => 'compte_password'   , 'href' => 'page=compte_password'   ),
    'compte_email'      => array( 'texte' => Lang::_("Adresse e-mail & Abonnements") , 'class' => 'mail'              , 'href' => 'page=compte_email'      ),
    'compte_switch'     => array( 'texte' => Lang::_("Bascule entre comptes")        , 'class' => 'compte_switch'     , 'href' => 'page=compte_switch'     ),
    'compte_daltonisme' => array( 'texte' => Lang::_("Daltonisme")                   , 'class' => 'compte_daltonisme' , 'href' => 'page=compte_daltonisme' ),
    'compte_langue'     => array( 'texte' => Lang::_("Langue")                       , 'class' => 'compte_langue'     , 'href' => 'page=compte_langue'     ),
  ),
  'evaluation' => array
  (
    'evaluation_voir'          => array( 'texte' => Lang::_("Liste des évaluations")            , 'class' => 'evaluation_voir'    , 'href' => 'page=evaluation_voir'          ),
    'evaluation_demande_eleve' => array( 'texte' => Lang::_("Demandes d'évaluations formulées") , 'class' => 'evaluation_demande' , 'href' => 'page=evaluation_demande_eleve' ),
  ),
  'releve' => array
  (
    'releve_grille_referentiel'    => array( 'texte' => Lang::_("Grille d'items d'un référentiel")   , 'class' => 'releve_grille'   , 'href' => 'page=releve&amp;section=grille_referentiel'    ),
    'releve_items_matiere'         => array( 'texte' => Lang::_("Relevé d'items d'une matière")      , 'class' => 'releve_items'    , 'href' => 'page=releve&amp;section=items_matiere'         ),
    'releve_items_multimatiere'    => array( 'texte' => Lang::_("Relevé d'items pluridisciplinaire") , 'class' => 'releve_items'    , 'href' => 'page=releve&amp;section=items_multimatiere'    ),
    'releve_items_professeur'      => array( 'texte' => Lang::_("Relevé d'items d'un enseignant")    , 'class' => 'releve_items'    , 'href' => 'page=releve&amp;section=items_professeur'      ),
 // 'releve_bilan_chronologique'   => array( 'texte' => Lang::_("Bilan chronologique")               , 'class' => 'releve_chrono'   , 'href' => 'page=releve&amp;section=bilan_chronologique'   ),
 // Penser aussi aux restrictions d'accès
    'releve_synthese_matiere'      => array( 'texte' => Lang::_("Synthèse d'une matière")            , 'class' => 'releve_synthese' , 'href' => 'page=releve&amp;section=synthese_matiere'      ),
    'releve_synthese_multimatiere' => array( 'texte' => Lang::_("Synthèse pluridisciplinaire")       , 'class' => 'releve_synthese' , 'href' => 'page=releve&amp;section=synthese_multimatiere' ),
    'releve_socle'                 => array( 'texte' => Lang::_("Relevé de maîtrise du socle")       , 'class' => 'releve_socle'    , 'href' => 'page=releve&amp;section=socle'                 ),
  ),
  'officiel' => array
  (
    'officiel_voir_archive' => array( 'texte' => Lang::_("Archives consultables") , 'class' => 'officiel_voir_archive' , 'href' => 'page=officiel_voir_archive' ),
  ),
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Adaptations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Voir et simuler l'algorithme de calcul.
if(!test_user_droit_specifique($_SESSION['DROIT_VOIR_ALGORITHME']))
{
  $tab_sous_menu['information']['consultation_algorithme']['class'] .= ' disabled';
}

// Voir les référentiels en place (dans l'établissement) (pas de restriction pour le profil [administrateur]).
if(!test_user_droit_specifique($_SESSION['DROIT_VOIR_REFERENTIELS']))
{
  $tab_sous_menu['information']['consultation_referentiel_interne']['class'] .= ' disabled';
}

// Changer son mot de passe (pas de restriction pour les profils [administrateur] et [webmestre]).
if(!test_user_droit_specifique($_SESSION['DROIT_MODIFIER_MDP']))
{
  $tab_sous_menu['parametrage']['compte_password']['class'] .= ' disabled';
}

// Grille d'items d'un référentiel.
if(!test_user_droit_specifique($_SESSION['DROIT_VOIR_GRILLES_ITEMS']))
{
  $tab_sous_menu['releve']['releve_grille_referentiel']['class'] .= ' disabled';
}

// Relevé de maîtrise du socle (profils [parent] et [eleve] uniquement).
if(!$_SESSION['LISTE_PALIERS_ACTIFS'] || !test_user_droit_specifique($_SESSION['DROIT_SOCLE_ACCES']))
{
  $tab_sous_menu['releve']['releve_socle']['class'] .= ' disabled';
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
    $tab_sous_menu['officiel']['officiel_voir_archive']['class'] .= ' disabled';
}

?>