<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
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
// Menu [directeur] à mettre en session
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Le menu complet ; attention : en cas de changement du nom d'un menu, répercuter la modif dans tout le fichier (§ Adaptations).

$tab_menu = array
(
  "Informations" => array
  (
    "Accueil"                    => array( 'class' => 'compte_accueil'   , 'href' => 'page=compte_accueil'                   ),
    "Données personnelles"       => array( 'class' => 'compte_cnil'      , 'href' => 'page=compte_cnil'                      ),
    "Algorithme de calcul"       => array( 'class' => 'algorithme_voir'  , 'href' => 'page=consultation_algorithme'          ),
    "Dates des périodes"         => array( 'class' => 'periode_groupe'   , 'href' => 'page=consultation_groupe_periode'      ),
    "Référentiels en place"      => array( 'class' => 'referentiel_voir' , 'href' => 'page=consultation_referentiel_interne' ),
    "Référentiels partagés"      => array( 'class' => 'referentiel_voir' , 'href' => 'page=consultation_referentiel_externe' ),
    "Date de dernière connexion" => array( 'class' => 'date_connexion'   , 'href' => 'page=consultation_date_connexion'      ),
    "Export de données"          => array( 'class' => 'fichier_export'   , 'href' => 'page=export_fichier'                   ),
    "Trombinoscope"              => array( 'class' => 'trombinoscope'    , 'href' => 'page=consultation_trombinoscope'       ),
  ),
  "Paramétrages" => array
  (
    "Mot de passe"          => array( 'class' => 'compte_password'   , 'href' => 'page=compte_password'               ),
    "Adresse e-mail"        => array( 'class' => 'mail'              , 'href' => 'page=compte_email'                  ),
    "Daltonisme"            => array( 'class' => 'compte_daltonisme' , 'href' => 'page=compte_daltonisme'             ),
    "Messages d'accueil"    => array( 'class' => 'message_accueil'   , 'href' => 'page=compte_message'                ),
    "Regroupements d'items" => array( 'class' => 'item_selection'    , 'href' => 'page=compte_selection_items'        ),
    "Synthèses / Bilans"    => array( 'class' => 'officiel_reglages' , 'href' => 'page=officiel&amp;section=reglages' ),
  ),
  "Évaluations" => array
  (
    "Nombre de saisies"     => array( 'class' => 'statistiques'    , 'href' => 'page=consultation_statistiques' ),
    "Liste des évaluations" => array( 'class' => 'evaluation_voir' , 'href' => 'page=evaluation_voir'           ),
  ),
  "Validation du socle" => array
  (
    "Choisir la langue étrangère"      => array( 'class' => 'socle_langue'  , 'href' => 'page=administrateur_eleve_langue'                 ),
    "Valider les items du socle"       => array( 'class' => 'socle_item'    , 'href' => 'page=validation_socle&amp;section=item'           ),
    "Valider les compétences du socle" => array( 'class' => 'socle_pilier'  , 'href' => 'page=validation_socle&amp;section=pilier'         ),
    "Annuler une compétence validée"   => array( 'class' => 'socle_annuler' , 'href' => 'page=validation_socle&amp;section=pilier_annuler' ),
    "Import / Export des validations"  => array( 'class' => 'socle_fichier' , 'href' => 'page=validation_socle&amp;section=fichier'        ),
  ),
  "Relevés / Synthèses" => array
  (
    "Recherche ciblée"                  => array( 'class' => 'releve_recherche'      , 'href' => 'page=releve&amp;section=recherche'             ),
    "Grille d'items d'un référentiel"   => array( 'class' => 'releve_grille'         , 'href' => 'page=releve&amp;section=grille_referentiel'    ),
    "Relevé d'items d'une matière"      => array( 'class' => 'releve_items'          , 'href' => 'page=releve&amp;section=items_matiere'         ),
    "Relevé d'items pluridisciplinaire" => array( 'class' => 'releve_items'          , 'href' => 'page=releve&amp;section=items_multimatiere'    ),
   // "Bilan chronologique"               => array( 'class' => 'releve_chrono'         , 'href' => 'page=releve&amp;section=bilan_chronologique'   ),
   // Penser aussi aux restrictions d'accès
    "Synthèse d'une matière"            => array( 'class' => 'releve_synthese'       , 'href' => 'page=releve&amp;section=synthese_matiere'      ),
    "Synthèse pluridisciplinaire"       => array( 'class' => 'releve_synthese'       , 'href' => 'page=releve&amp;section=synthese_multimatiere' ),
    "Relevé de maîtrise du socle"       => array( 'class' => 'releve_socle'          , 'href' => 'page=releve&amp;section=socle'                 ),
    "Synthèse de maîtrise du socle"     => array( 'class' => 'releve_synthese_socle' , 'href' => 'page=releve&amp;section=synthese_socle'        ),
  ),
  "Bilans officiels" => array
  (
    "Absences / Retards"          => array( 'class' => 'officiel_assiduite' , 'href' => 'page=officiel&amp;section=assiduite'        ),
    "Relevé d'évaluations"        => array( 'class' => 'officiel_releve'    , 'href' => 'page=officiel&amp;section=accueil_releve'   ),
    "Bulletin scolaire"           => array( 'class' => 'officiel_bulletin'  , 'href' => 'page=officiel&amp;section=accueil_bulletin' ),
    "Maîtrise du palier 1"        => array( 'class' => 'officiel_palier1'   , 'href' => 'page=officiel&amp;section=accueil_palier1'  ),
    "Maîtrise du palier 2"        => array( 'class' => 'officiel_palier2'   , 'href' => 'page=officiel&amp;section=accueil_palier2'  ),
    "Maîtrise du palier 3"        => array( 'class' => 'officiel_palier3'   , 'href' => 'page=officiel&amp;section=accueil_palier3'  ),
    "Notanet &amp; Fiches brevet" => array( 'class' => 'officiel_brevet'    , 'href' => 'page=brevet&amp;section=accueil'            ),
  ),
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

// Consulter les référentiels partagés (serveur communautaire)
if( !$_SESSION['SESAMATH_ID'] || !$_SESSION['SESAMATH_KEY'] )
{
  $tab_menu["Informations"]["Référentiels partagés"]['class'] .= ' disabled';
}

// Changer son mot de passe (pas de restriction pour les profils [administrateur] et [webmestre]).
if(!test_user_droit_specifique($_SESSION['DROIT_MODIFIER_MDP']))
{
  $tab_menu["Paramétrages"]["Mot de passe"]['class'] .= ' disabled';
}

// Changer son adresse e-mail (pas de restriction pour le profil [administrateur].
if(!test_user_droit_specifique($_SESSION['DROIT_MODIFIER_EMAIL']))
{
  $tab_menu["Paramétrages"]["Adresse e-mail"]['class'] .= ' disabled';
}

// Choisir la langue étrangère pour le socle commun (profils [professeur] et [directeur] uniquement).
if(!$_SESSION['LISTE_PALIERS_ACTIFS'] || !test_user_droit_specifique( $_SESSION['DROIT_AFFECTER_LANGUE'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ))
{
  $tab_menu["Validation du socle"]["Choisir la langue étrangère"]['class'] .= ' disabled';
}

// Valider les items du socle (profils [professeur] et [directeur] uniquement).
if(!$_SESSION['LISTE_PALIERS_ACTIFS'] || !test_user_droit_specifique( $_SESSION['DROIT_VALIDATION_ENTREE'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ))
{
  $tab_menu["Validation du socle"]["Valider les items du socle"]['class'] .= ' disabled';
}

// Valider les piliers du socle (profils [professeur] et [directeur] uniquement).
if(!$_SESSION['LISTE_PALIERS_ACTIFS'] || !test_user_droit_specifique( $_SESSION['DROIT_VALIDATION_PILIER'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ))
{
  $tab_menu["Validation du socle"]["Valider les compétences du socle"]['class'] .= ' disabled';
}

// Annuler une compétence validée du socle (profils [professeur] et [directeur] uniquement).
if(!$_SESSION['LISTE_PALIERS_ACTIFS'] || !test_user_droit_specifique( $_SESSION['DROIT_ANNULATION_PILIER'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ))
{
  $tab_menu["Validation du socle"]["Annuler une compétence validée"]['class'] .= ' disabled';
}

// Import / Export des validations (profil [directeur] uniquement).
if(!$_SESSION['LISTE_PALIERS_ACTIFS'])
{
  $tab_menu["Validation du socle"]["Import / Export des validations"]['class'] .= ' disabled';
}

// Grille d'items d'un référentiel.
if(!test_user_droit_specifique($_SESSION['DROIT_VOIR_GRILLES_ITEMS']))
{
  $tab_menu["Relevés / Synthèses"]["Grille d'items d'un référentiel"]['class'] .= ' disabled';
}

// Relevé de maîtrise du socle & Synthèse de maîtrise du socle
if(!$_SESSION['LISTE_PALIERS_ACTIFS'])
{
  $tab_menu["Relevés / Synthèses"]["Relevé de maîtrise du socle"]['class'] .= ' disabled';
  $tab_menu["Relevés / Synthèses"]["Synthèse de maîtrise du socle"]['class'] .= ' disabled';
}

// Import des absences / retards sur les bilans officiels (profils [professeur] et [directeur] uniquement).
if(!test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_SAISIR_ASSIDUITE'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ))
{
  $tab_menu["Bilans officiels"]["Absences / Retards"]['class'] .= ' disabled';
}

// Bilans officiels relatifs aux paliers du socle restreint aux paliers en vigueur dans l'établissement
$tab_paliers_actifs = explode(',',$_SESSION['LISTE_PALIERS_ACTIFS']);
for( $palier_id=1 ; $palier_id<4 ; $palier_id++ )
{
  if(!in_array($palier_id,$tab_paliers_actifs))
  {
    $tab_menu["Bilans officiels"]["Maîtrise du palier ".$palier_id]['class'] .= ' disabled';
  }
}

?>