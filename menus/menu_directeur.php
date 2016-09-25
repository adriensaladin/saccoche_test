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
// Menu [directeur] à mettre en session
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Attention : en cas de changement d'indice d'un menu, répercuter la modif dans la partie Adaptations (en-dessous).

$tab_menu = array
(
  'information' => Lang::_("Informations"),
  'parametrage' => Lang::_("Paramétrages"),
  'evaluation'  => Lang::_("Évaluations"),
  'validation'  => Lang::_("Validation du socle"),
  'releve'      => Lang::_("Relevés / Synthèses"),
  'officiel'    => Lang::_("Bilans officiels"),
);

$tab_sous_menu = array
(
  'information' => array
  (
    'compte_accueil'                   => array( 'texte' => Lang::_("Accueil")                                  , 'class' => 'compte_accueil'   , 'href' => 'page=compte_accueil'                   ),
    'compte_cnil'                      => array( 'texte' => Lang::_("Données personnelles")                     , 'class' => 'compte_cnil'      , 'href' => 'page=compte_cnil'                      ),
    'consultation_codes_couleurs'      => array( 'texte' => Lang::_("Codes de notation / États d'acquisition") , 'class' => 'etabl_couleurs'   , 'href' => 'page=consultation_codes_couleurs'      ),
    'consultation_algorithme'          => array( 'texte' => Lang::_("Algorithme de calcul")                     , 'class' => 'algorithme_voir'  , 'href' => 'page=consultation_algorithme'          ),
    'consultation_date_connexion'      => array( 'texte' => Lang::_("Date de dernière connexion")               , 'class' => 'date_connexion'   , 'href' => 'page=consultation_date_connexion'      ),
    'consultation_groupe_periode'      => array( 'texte' => Lang::_("Dates des périodes")                       , 'class' => 'periode_groupe'   , 'href' => 'page=consultation_groupe_periode'      ),
    'export_fichier'                   => array( 'texte' => Lang::_("Export de données")                        , 'class' => 'fichier_export'   , 'href' => 'page=export_fichier'                   ),
    'consultation_notifications'       => array( 'texte' => Lang::_("Notifications reçues")                     , 'class' => 'newsletter'       , 'href' => 'page=consultation_notifications'       ),
    'consultation_referentiel_interne' => array( 'texte' => Lang::_("Référentiels en place")                    , 'class' => 'referentiel_voir' , 'href' => 'page=consultation_referentiel_interne' ),
    'consultation_referentiel_externe' => array( 'texte' => Lang::_("Référentiels partagés")                    , 'class' => 'referentiel_voir' , 'href' => 'page=consultation_referentiel_externe' ),
    'consultation_stats_globales'      => array( 'texte' => Lang::_("Statistiques globales")                    , 'class' => 'statistiques'     , 'href' => 'page=consultation_stats_globales'      ),
    'consultation_trombinoscope'       => array( 'texte' => Lang::_("Trombinoscope")                            , 'class' => 'trombinoscope'    , 'href' => 'page=consultation_trombinoscope'       ),
  ),
  'parametrage' => array
  (
    'compte_password'        => array( 'texte' => Lang::_("Mot de passe")                 , 'class' => 'compte_password'   , 'href' => 'page=compte_password'               ),
    'compte_email'           => array( 'texte' => Lang::_("Adresse e-mail & Abonnements") , 'class' => 'mail'              , 'href' => 'page=compte_email'                  ),
    'compte_switch'          => array( 'texte' => Lang::_("Bascule entre comptes")        , 'class' => 'compte_switch'     , 'href' => 'page=compte_switch'                 ),
    'compte_daltonisme'      => array( 'texte' => Lang::_("Daltonisme")                   , 'class' => 'compte_daltonisme' , 'href' => 'page=compte_daltonisme'             ),
    'compte_langue'          => array( 'texte' => Lang::_("Langue")                       , 'class' => 'compte_langue'     , 'href' => 'page=compte_langue'                 ),
    'compte_message'         => array( 'texte' => Lang::_("Messages d'accueil")           , 'class' => 'message_accueil'   , 'href' => 'page=compte_message'                ),
    'compte_selection_items' => array( 'texte' => Lang::_("Regroupements d'items")        , 'class' => 'item_selection'    , 'href' => 'page=compte_selection_items'        ),
    'officiel_reglages'      => array( 'texte' => Lang::_("Synthèses / Bilans")           , 'class' => 'officiel_reglages' , 'href' => 'page=officiel&amp;section=reglages' ),
  ),
  'evaluation' => array
  (
    'consultation_nombre_saisies' => array( 'texte' => Lang::_("Nombre de saisies")     , 'class' => 'statistiques'    , 'href' => 'page=consultation_nombre_saisies' ),
    'evaluation_voir'             => array( 'texte' => Lang::_("Liste des évaluations") , 'class' => 'evaluation_voir' , 'href' => 'page=evaluation_voir' ),
  ),
  'validation' => array
  (
    'administrateur_eleve_langue'     => array( 'texte' => Lang::_("Choisir la langue étrangère")      , 'class' => 'socle_langue'  , 'href' => 'page=validation_socle&amp;section=langue'         ),
    'validation_socle_item'           => array( 'texte' => Lang::_("Valider les items du socle")       , 'class' => 'socle_item'    , 'href' => 'page=validation_socle&amp;section=item'           ),
    'validation_socle_pilier'         => array( 'texte' => Lang::_("Valider les compétences du socle") , 'class' => 'socle_pilier'  , 'href' => 'page=validation_socle&amp;section=pilier'         ),
    'validation_socle_pilier_annuler' => array( 'texte' => Lang::_("Annuler une compétence validée")   , 'class' => 'socle_annuler' , 'href' => 'page=validation_socle&amp;section=pilier_annuler' ),
    'validation_socle_fichier'        => array( 'texte' => Lang::_("Import / Export des validations")  , 'class' => 'socle_fichier' , 'href' => 'page=validation_socle&amp;section=fichier'        ),
  ),
  'releve' => array
  (
    'releve_recherche'          => array( 'texte' => Lang::_("Recherche ciblée")                , 'class' => 'releve_recherche'      , 'href' => 'page=releve&amp;section=recherche'           ),
    'releve_grille_referentiel' => array( 'texte' => Lang::_("Grille d'items d'un référentiel") , 'class' => 'releve_grille'         , 'href' => 'page=releve&amp;section=grille_referentiel'  ),
    'releve_items'              => array( 'texte' => Lang::_("Relevé d'items")                  , 'class' => 'releve_items'          , 'href' => 'page=releve&amp;section=items'               ),
    'releve_synthese'           => array( 'texte' => Lang::_("Synthèse d'items")                , 'class' => 'releve_synthese'       , 'href' => 'page=releve&amp;section=synthese'            ),
    'releve_chronologique'      => array( 'texte' => Lang::_("Bilan chronologique")             , 'class' => 'releve_chrono'         , 'href' => 'page=releve&amp;section=bilan_chronologique' ),
    'releve_socle2016'          => array( 'texte' => Lang::_("Maîtrise du socle").' (2016)'     , 'class' => 'releve_socle2016'      , 'href' => 'page=releve&amp;section=socle2016'           ),
 // 'releve_socle'              => array( 'texte' => Lang::_("Relevé de maîtrise du socle")     , 'class' => 'releve_socle'          , 'href' => 'page=releve&amp;section=socle'               ),
 // 'releve_synthese_socle'     => array( 'texte' => Lang::_("Synthèse de maîtrise du socle")   , 'class' => 'releve_synthese_socle' , 'href' => 'page=releve&amp;section=synthese_socle'      ),
  ),
  'officiel' => array
  (
    'officiel_assiduite'        => array( 'texte' => Lang::_("Absences / Retards")      , 'class' => 'officiel_assiduite' , 'href' => 'page=officiel&amp;section=assiduite'        ),
    'officiel_accueil_releve'   => array( 'texte' => Lang::_("Relevé d'évaluations")    , 'class' => 'officiel_releve'    , 'href' => 'page=officiel&amp;section=accueil_releve'   ),
    'officiel_accueil_bulletin' => array( 'texte' => Lang::_("Bulletin scolaire")       , 'class' => 'officiel_bulletin'  , 'href' => 'page=officiel&amp;section=accueil_bulletin' ),
 // 'officiel_accueil_palier1'  => array( 'texte' => Lang::_("Maîtrise du palier 1")    , 'class' => 'officiel_palier1'   , 'href' => 'page=officiel&amp;section=accueil_palier1'  ),
 // 'officiel_accueil_palier2'  => array( 'texte' => Lang::_("Maîtrise du palier 2")    , 'class' => 'officiel_palier2'   , 'href' => 'page=officiel&amp;section=accueil_palier2'  ),
 // 'officiel_accueil_palier3'  => array( 'texte' => Lang::_("Maîtrise du palier 3")    , 'class' => 'officiel_palier3'   , 'href' => 'page=officiel&amp;section=accueil_palier3'  ),
    'livret_accueil'            => array( 'texte' => Lang::_("Livret Scolaire")         , 'class' => 'marianne'           , 'href' => 'page=livret&amp;section=accueil'            ),
    'brevet_accueil'            => array( 'texte' => Lang::_("Notanet & Fiches brevet") , 'class' => 'officiel_brevet'    , 'href' => 'page=brevet&amp;section=accueil'            ),
  ),
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Adaptations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Voir le paramètrage des codes et des états d'acquisition.
if(!Outil::test_user_droit_specifique($_SESSION['DROIT_VOIR_PARAM_NOTES_ACQUIS']))
{
  $tab_sous_menu['information']['consultation_codes_couleurs']['class'] .= ' disabled';
}

// Voir et simuler l'algorithme de calcul.
if(!Outil::test_user_droit_specifique($_SESSION['DROIT_VOIR_PARAM_ALGORITHME']))
{
  $tab_sous_menu['information']['consultation_algorithme']['class'] .= ' disabled';
}

// Voir les référentiels en place (dans l'établissement) (pas de restriction pour le profil [administrateur]).
if(!Outil::test_user_droit_specifique($_SESSION['DROIT_VOIR_REFERENTIELS']))
{
  $tab_sous_menu['information']['consultation_referentiel_interne']['class'] .= ' disabled';
}

// Consulter les référentiels partagés (serveur communautaire)
if( !$_SESSION['SESAMATH_ID'] || !$_SESSION['SESAMATH_KEY'] )
{
  $tab_sous_menu['information']['consultation_referentiel_externe']['class'] .= ' disabled';
}

// Changer son mot de passe (pas de restriction pour les profils [administrateur] et [webmestre]).
if(!Outil::test_user_droit_specifique($_SESSION['DROIT_MODIFIER_MDP']))
{
  $tab_sous_menu['parametrage']['compte_password']['class'] .= ' disabled';
}

// Changer son adresse e-mail (pas de restriction pour le profil [administrateur].
if(!Outil::test_user_droit_specifique($_SESSION['DROIT_MODIFIER_EMAIL']))
{
  $tab_sous_menu['parametrage']['compte_email']['class'] .= ' disabled';
}

// Choisir la langue étrangère pour le socle commun (profils [professeur] et [directeur] uniquement).
if(!$_SESSION['LISTE_PALIERS_ACTIFS'] || !Outil::test_user_droit_specifique( $_SESSION['DROIT_AFFECTER_LANGUE'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ))
{
  $tab_sous_menu['validation']['administrateur_eleve_langue']['class'] .= ' disabled';
}

// Valider les items du socle (profils [professeur] et [directeur] uniquement).
if(!$_SESSION['LISTE_PALIERS_ACTIFS'] || !Outil::test_user_droit_specifique( $_SESSION['DROIT_VALIDATION_ENTREE'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ))
{
  $tab_sous_menu['validation']['validation_socle_item']['class'] .= ' disabled';
}

// Valider les piliers du socle (profils [professeur] et [directeur] uniquement).
if(!$_SESSION['LISTE_PALIERS_ACTIFS'] || !Outil::test_user_droit_specifique( $_SESSION['DROIT_VALIDATION_PILIER'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ))
{
  $tab_sous_menu['validation']['validation_socle_pilier']['class'] .= ' disabled';
}

// Annuler une compétence validée du socle (profils [professeur] et [directeur] uniquement).
if(!$_SESSION['LISTE_PALIERS_ACTIFS'] || !Outil::test_user_droit_specifique( $_SESSION['DROIT_ANNULATION_PILIER'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ))
{
  $tab_sous_menu['validation']['validation_socle_pilier_annuler']['class'] .= ' disabled';
}

// Import / Export des validations (profil [directeur] uniquement).
if(!$_SESSION['LISTE_PALIERS_ACTIFS'])
{
  $tab_sous_menu['validation']['validation_socle_fichier']['class'] .= ' disabled';
}

// Grille d'items d'un référentiel.
if(!Outil::test_user_droit_specifique($_SESSION['DROIT_VOIR_GRILLES_ITEMS']))
{
  $tab_sous_menu['releve']['releve_grille_referentiel']['class'] .= ' disabled';
}

// Relevé de maîtrise du socle & Synthèse de maîtrise du socle
if(!$_SESSION['LISTE_PALIERS_ACTIFS'])
{
  $tab_sous_menu['releve']['releve_socle']['class'] .= ' disabled';
  // $tab_sous_menu['releve']['releve_synthese_socle']['class'] .= ' disabled';
}

// Import des absences / retards sur les bilans officiels (profils [professeur] et [directeur] uniquement).
if(!Outil::test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_SAISIR_ASSIDUITE'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ))
{
  $tab_sous_menu['officiel']['officiel_assiduite']['class'] .= ' disabled';
}

// Bilans officiels relatifs aux paliers du socle restreint aux paliers en vigueur dans l'établissement
/* TODO A RETIRER
$tab_paliers_actifs = explode(',',$_SESSION['LISTE_PALIERS_ACTIFS']);
for( $palier_id=1 ; $palier_id<4 ; $palier_id++ )
{
  if(!in_array($palier_id,$tab_paliers_actifs))
  {
    $tab_sous_menu['officiel']['officiel_accueil_palier'.$palier_id]['class'] .= ' disabled';
  }
}
*/

?>