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
// Menu [administrateur] à mettre en session
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Le menu complet ; attention : en cas de changement du nom d'un menu, répercuter la modif dans tout le fichier (§ Adaptations).

$tab_menu = array
(
  "Informations" => array
  (
    "Accueil"                     => array( 'class' => 'compte_accueil'   , 'href' => 'page=compte_accueil'                   ),
    "Référentiels en place"       => array( 'class' => 'referentiel_voir' , 'href' => 'page=consultation_referentiel_interne' ),
    "Date de dernière connexion"  => array( 'class' => 'date_connexion'   , 'href' => 'page=consultation_date_connexion'      ),
    "Export de données"           => array( 'class' => 'fichier_export'   , 'href' => 'page=export_fichier'                   ),
    "Log des actions sensibles"   => array( 'class' => 'log_actions'      , 'href' => 'page=administrateur_log_actions'       ),
    "Caractéristiques du serveur" => array( 'class' => 'serveur_info'     , 'href' => 'page=compte_info_serveur'              ),
  ),
  "Paramétrages établissement" => array
  (
    "Identité de l'établissement"          => array( 'class' => 'etabl_identite'     , 'href' => 'page=administrateur_etabl_identite'     ),
    "Mode d'identification"                => array( 'class' => 'etabl_connexion'    , 'href' => 'page=administrateur_etabl_connexion'    ),
    "Niveaux"                              => array( 'class' => 'etabl_choix'        , 'href' => 'page=administrateur_etabl_niveau'       ),
    "Matières"                             => array( 'class' => 'etabl_choix'        , 'href' => 'page=administrateur_etabl_matiere'      ),
    "Paliers du socle"                     => array( 'class' => 'etabl_choix'        , 'href' => 'page=administrateur_etabl_palier'       ),
    "Notation : codes, couleurs, légendes" => array( 'class' => 'etabl_couleurs'     , 'href' => 'page=administrateur_codes_couleurs'     ),
    "Algorithme de calcul"                 => array( 'class' => 'algorithme_edition' , 'href' => 'page=administrateur_algorithme_gestion' ),
  ),
  "Paramétrages utilisateurs" => array
  (
    "Choix des profils utilisateurs"       => array( 'class' => 'directeur'           , 'href' => 'page=administrateur_etabl_profils'          ),
    "Réglage des autorisations"            => array( 'class' => 'etabl_autorisations' , 'href' => 'page=administrateur_autorisations'          ),
    "Format des identifiants de connexion" => array( 'class' => 'etabl_login'         , 'href' => 'page=administrateur_etabl_login'            ),
    "Délai avant déconnexion"              => array( 'class' => 'etabl_duree'         , 'href' => 'page=administrateur_etabl_duree_inactivite' ),
    "Changer mon mot de passe"             => array( 'class' => 'compte_password'     , 'href' => 'page=compte_password'                       ),
    "Changer mon adresse e-mail"           => array( 'class' => 'mail'                , 'href' => 'page=compte_email'                          ),
  ),
  "Administration générale" => array
  (
    "Sauvegarder / Restaurer la base"      => array( 'class' => 'dump'            , 'href' => 'page=administrateur_dump'                ),
    "Nettoyer / Initialiser la base"       => array( 'class' => 'nettoyage'       , 'href' => 'page=administrateur_nettoyage'           ),
    "Importer des fichiers d'utilisateurs" => array( 'class' => 'fichier_import'  , 'href' => 'page=administrateur_fichier_user'        ),
    "Importer / Imposer des identifiants"  => array( 'class' => 'fichier_import'  , 'href' => 'page=administrateur_fichier_identifiant' ),
    "Import / Export des validations"      => array( 'class' => 'socle_fichier'   , 'href' => 'page=validation_socle_fichier'           ),
    "Messages d'accueil"                   => array( 'class' => 'message_accueil' , 'href' => 'page=compte_message'                     ),
    "Blocage des connexions"               => array( 'class' => 'blocage'         , 'href' => 'page=administrateur_blocage'             ),
    "Résilier l'inscription"               => array( 'class' => 'resilier'        , 'href' => 'page=administrateur_resilier'            ),
  ),
  "Gestion courante" => array
  (
    "Périodes"                    => array( 'class' => 'periode'           , 'href' => 'page=administrateur_periode'        ),
    "Synthèses / Bilans"          => array( 'class' => 'officiel_reglages' , 'href' => 'page=officiel&amp;section=reglages' ),
    "Notanet &amp; Fiches brevet" => array( 'class' => 'officiel_brevet'   , 'href' => 'page=brevet&amp;section=accueil'    ),
    "Classes"                     => array( 'class' => 'groupe'            , 'href' => 'page=administrateur_classe'         ),
    "Groupes"                     => array( 'class' => 'groupe'            , 'href' => 'page=administrateur_groupe'         ),
    "Élèves"                      => array( 'class' => 'eleve'             , 'href' => 'page=administrateur_eleve'          ),
    "Parents"                     => array( 'class' => 'parent'            , 'href' => 'page=administrateur_parent'         ),
    "Professeurs / Personnels"    => array( 'class' => 'professeur'        , 'href' => 'page=administrateur_professeur'     ),
    "Administrateurs"             => array( 'class' => 'administrateur'    , 'href' => 'page=administrateur_administrateur' ),
    "Rechercher un utilisateur"   => array( 'class' => 'user_recherche'    , 'href' => 'page=administrateur_user_recherche' ),
  ),
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Adaptations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// RAS !

?>