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

// Tableau avec les différents droits d'accès aux pages suivant le profil
// Il faut aussi indiquer le format page_section pour les appels ajax

$tab_droits = array();

$tab_droits_profil_identifie      = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>1 , 'webmestre'=>1 );
$tab_droits_profil_public         = array( 'public'=>1 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits_profil_eleve          = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits_profil_professeur     = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits_profil_directeur      = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits_profil_administrateur = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits_profil_webmestre      = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>1 );

// Profils identifiés
$tab_droits['conserver_session_active']                = $tab_droits_profil_identifie;
$tab_droits['compte_accueil']                          = $tab_droits_profil_identifie;
$tab_droits['compte_password']                         = $tab_droits_profil_identifie;
// Profil public
$tab_droits['public_accueil']                          = $tab_droits_profil_public;
$tab_droits['public_installation']                     = $tab_droits_profil_public;
$tab_droits['public_logout_SSO']                       = $tab_droits_profil_public;
// Profil élève
$tab_droits['eleve_eval_bilan']                        = $tab_droits_profil_eleve;
$tab_droits['eleve_eval_demande']                      = $tab_droits_profil_eleve;
$tab_droits['eleve_eval_demande_ajout']                = $tab_droits_profil_eleve;
// Profil professeur
$tab_droits['professeur_eval']                         = $tab_droits_profil_professeur;
$tab_droits['professeur_eval_demande']                 = $tab_droits_profil_professeur;
$tab_droits['professeur_eval_groupe']                  = $tab_droits_profil_professeur;
$tab_droits['professeur_eval_select']                  = $tab_droits_profil_professeur;
$tab_droits['professeur_groupe']                       = $tab_droits_profil_professeur;
$tab_droits['professeur_groupe_gestion']               = $tab_droits_profil_professeur;
$tab_droits['professeur_groupe_eleve']                 = $tab_droits_profil_professeur;
$tab_droits['professeur_referentiel']                  = $tab_droits_profil_professeur;
$tab_droits['professeur_referentiel_gestion']          = $tab_droits_profil_professeur;
$tab_droits['professeur_referentiel_edition']          = $tab_droits_profil_professeur;
// Profil directeur
$tab_droits['consultation_statistiques']               = $tab_droits_profil_directeur;
// Profil administrateur
$tab_droits['administrateur_administrateur']           = $tab_droits_profil_administrateur;
$tab_droits['administrateur_algorithme_gestion']       = $tab_droits_profil_administrateur;
$tab_droits['administrateur_autorisations']            = $tab_droits_profil_administrateur;
$tab_droits['administrateur_blocage']                  = $tab_droits_profil_administrateur;
$tab_droits['administrateur_classe']                   = $tab_droits_profil_administrateur;
$tab_droits['administrateur_classe_gestion']           = $tab_droits_profil_administrateur;
$tab_droits['administrateur_codes_couleurs']           = $tab_droits_profil_administrateur;
$tab_droits['administrateur_directeur']                = $tab_droits_profil_administrateur;
$tab_droits['administrateur_dump']                     = $tab_droits_profil_administrateur;
$tab_droits['administrateur_eleve']                    = $tab_droits_profil_administrateur;
$tab_droits['administrateur_eleve_classe']             = $tab_droits_profil_administrateur;
$tab_droits['administrateur_eleve_gestion']            = $tab_droits_profil_administrateur;
$tab_droits['administrateur_eleve_groupe']             = $tab_droits_profil_administrateur;
$tab_droits['administrateur_etabl_connexion']          = $tab_droits_profil_administrateur;
$tab_droits['administrateur_etabl_duree_inactivite']   = $tab_droits_profil_administrateur;
$tab_droits['administrateur_etabl_identite']           = $tab_droits_profil_administrateur;
$tab_droits['administrateur_etabl_login']              = $tab_droits_profil_administrateur;
$tab_droits['administrateur_etabl_matiere']            = $tab_droits_profil_administrateur;
$tab_droits['administrateur_etabl_niveau']             = $tab_droits_profil_administrateur;
$tab_droits['administrateur_etabl_palier']             = $tab_droits_profil_administrateur;
$tab_droits['administrateur_fichier_identifiant']      = $tab_droits_profil_administrateur;
$tab_droits['administrateur_fichier_user']             = $tab_droits_profil_administrateur;
$tab_droits['administrateur_groupe']                   = $tab_droits_profil_administrateur;
$tab_droits['administrateur_groupe_gestion']           = $tab_droits_profil_administrateur;
$tab_droits['administrateur_log_actions']              = $tab_droits_profil_administrateur;
$tab_droits['administrateur_nettoyage']                = $tab_droits_profil_administrateur;
$tab_droits['administrateur_periode']                  = $tab_droits_profil_administrateur;
$tab_droits['administrateur_periode_classe_groupe']    = $tab_droits_profil_administrateur;
$tab_droits['administrateur_periode_gestion']          = $tab_droits_profil_administrateur;
$tab_droits['administrateur_professeur']               = $tab_droits_profil_administrateur;
$tab_droits['administrateur_professeur_classe']        = $tab_droits_profil_administrateur;
$tab_droits['administrateur_professeur_coordonnateur'] = $tab_droits_profil_administrateur;
$tab_droits['administrateur_professeur_gestion']       = $tab_droits_profil_administrateur;
$tab_droits['administrateur_professeur_groupe']        = $tab_droits_profil_administrateur;
$tab_droits['administrateur_professeur_matiere']       = $tab_droits_profil_administrateur;
$tab_droits['administrateur_professeur_principal']     = $tab_droits_profil_administrateur;
$tab_droits['administrateur_releves_bilans']           = $tab_droits_profil_administrateur;
$tab_droits['administrateur_resilier']                 = $tab_droits_profil_administrateur;
$tab_droits['administrateur_statut_desactiver']        = $tab_droits_profil_administrateur;
$tab_droits['administrateur_statut_traiter']           = $tab_droits_profil_administrateur;
// Profil webmestre
$tab_droits['webmestre_configuration_proxy']           = $tab_droits_profil_webmestre;
$tab_droits['webmestre_database_test']                 = $tab_droits_profil_webmestre;
$tab_droits['webmestre_mdp_admin']                     = $tab_droits_profil_webmestre;
$tab_droits['webmestre_geographie']                    = $tab_droits_profil_webmestre;
$tab_droits['webmestre_identite_installation']         = $tab_droits_profil_webmestre;
$tab_droits['webmestre_maintenance']                   = $tab_droits_profil_webmestre;
$tab_droits['webmestre_newsletter']                    = $tab_droits_profil_webmestre;
$tab_droits['webmestre_resilier']                      = $tab_droits_profil_webmestre;
$tab_droits['webmestre_statistiques']                  = $tab_droits_profil_webmestre;
$tab_droits['webmestre_structure_ajout_csv']           = $tab_droits_profil_webmestre;
$tab_droits['webmestre_structure_gestion']             = $tab_droits_profil_webmestre;
$tab_droits['webmestre_structure_transfert']           = $tab_droits_profil_webmestre;
// Profils particuliers à gérer au cas par cas
$tab_droits['_maj_select_domaines']                    = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['_maj_select_eleves']                      = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['_maj_select_eval']                        = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['_maj_select_piliers']                     = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['_maj_select_matieres']                    = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['_maj_select_professeurs']                 = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['_maj_select_professeurs_directeurs']      = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['compte_daltonisme']                       = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['compte_info_serveur']                     = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>1 );
$tab_droits['consultation_algorithme']                 = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['consultation_date_connexion']             = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['consultation_groupe_periode']             = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['consultation_referentiel_interne']        = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['consultation_referentiel_externe']        = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['date_calendrier']                         = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['export_fichier']                          = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['public_login_CAS']                        = array( 'public'=>1 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 ); // Particulier ! Un échange est encore effecué avec ce fichier après enregistrement des données de session...
$tab_droits['releve']                                  = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['releve_grille_referentiel']               = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['releve_items_matiere']                    = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['releve_items_selection']                  = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['releve_items_multimatiere']               = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['releve_socle']                            = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['releve_synthese_matiere']                 = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['releve_synthese_multimatiere']            = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['releve_synthese_socle']                   = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['validation_socle']                        = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['validation_socle_item']                   = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['validation_socle_pilier']                 = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['validation_socle_pilier_annuler']         = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );

?>