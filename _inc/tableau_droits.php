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
// Profils identifiés
$tab_droits['conserver_session_active']                                        = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>1 , 'webmestre'=>1 );
$tab_droits['compte_accueil']                                                  = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>1 , 'webmestre'=>1 );
$tab_droits['compte_password']                                                 = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>1 , 'webmestre'=>1 );
// Profil public
$tab_droits['public_accueil']                                                  = array( 'public'=>1 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['public_installation']                                             = array( 'public'=>1 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['public_login_CAS']                                                = array( 'public'=>1 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['public_logout_SSO']                                               = array( 'public'=>1 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
// Profil élève
$tab_droits['eleve_eval_demande']                                              = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['eleve_eval_demande_ajout']                                        = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
// Profil professeur
$tab_droits['professeur_eval']                                                 = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['professeur_eval_demande']                                         = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['professeur_eval_groupe']                                          = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['professeur_eval_select']                                          = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['professeur_groupe']                                               = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['professeur_groupe_gestion']                                       = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['professeur_groupe_eleve']                                         = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['professeur_referentiel']                                          = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['professeur_referentiel_gestion']                                  = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['professeur_referentiel_edition']                                  = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
// Profil directeur
          /* pas de page dédiée */
// Profil administrateur
$tab_droits['administrateur_administrateur']                                   = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_algorithme_gestion']                               = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_autorisations']                                    = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_blocage']                                          = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_classe']                                           = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_classe_gestion']                                   = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_codes_couleurs']                                   = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_directeur']                                        = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_dump']                                             = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_eleve']                                            = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_eleve_classe']                                     = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_eleve_gestion']                                    = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_eleve_groupe']                                     = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_etabl_connexion']                                  = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_etabl_duree_inactivite']                           = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_etabl_identite']                                   = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_etabl_login']                                      = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_etabl_matiere']                                    = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_etabl_niveau']                                     = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_etabl_palier']                                     = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_fichier']                                          = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_fichier_force_loginmdp']                           = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_fichier_import_eleve_classe']                      = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_fichier_import_id_ent']                            = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_fichier_import_id_gepi']                           = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_fichier_import_professeur_directeur']              = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_fichier_init_loginmdp_eleve']                      = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_fichier_init_loginmdp_professeur_directeur']       = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_groupe']                                           = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_groupe_gestion']                                   = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_nettoyage']                                        = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_periode']                                          = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_periode_classe_groupe']                            = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_periode_gestion']                                  = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_professeur']                                       = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_professeur_classe']                                = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_professeur_coordonnateur']                         = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_professeur_gestion']                               = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_professeur_groupe']                                = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_professeur_matiere']                               = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_professeur_principal']                             = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_resilier']                                         = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_statut']                                           = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_statut_desactiver_eleve']                          = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_statut_desactiver_professeur_directeur']           = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_statut_reintegrer_supprimer_eleve']                = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['administrateur_statut_reintegrer_supprimer_professeur_directeur'] = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
// Profil webmestre
$tab_droits['webmestre_mdp_admin']                                             = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>1 );
$tab_droits['webmestre_geographie']                                            = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>1 );
$tab_droits['webmestre_identite_installation']                                 = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>1 );
$tab_droits['webmestre_maintenance']                                           = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>1 );
$tab_droits['webmestre_newsletter']                                            = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>1 );
$tab_droits['webmestre_resilier']                                              = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>1 );
$tab_droits['webmestre_statistiques']                                          = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>1 );
$tab_droits['webmestre_structure']                                             = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>1 );
// Au cas par cas
$tab_droits['_maj_select_eleves']                                              = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['_maj_select_eval']                                                = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['_maj_select_piliers']                                             = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['_maj_select_matieres']                                            = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['_maj_select_professeurs']                                         = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['_maj_select_professeurs_directeurs']                              = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['compte_info_serveur']                                             = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>0 , 'directeur'=>0 , 'administrateur'=>1 , 'webmestre'=>1 );
$tab_droits['consultation']                                                    = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['consultation_algorithme']                                         = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['consultation_referentiel_interne']                                = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['consultation_referentiel_externe']                                = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['date_calendrier']                                                 = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>1 , 'webmestre'=>0 );
$tab_droits['export_fichier']                                                  = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['releve']                                                          = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['releve_grille']                                                   = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['releve_matiere']                                                  = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['releve_selection']                                                = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>0 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['releve_multimatiere']                                             = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['releve_socle']                                                    = array( 'public'=>0 , 'eleve'=>1 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['validation_socle']                                                = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['validation_socle_item']                                           = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );
$tab_droits['validation_socle_pilier']                                         = array( 'public'=>0 , 'eleve'=>0 , 'professeur'=>1 , 'directeur'=>1 , 'administrateur'=>0 , 'webmestre'=>0 );

?>