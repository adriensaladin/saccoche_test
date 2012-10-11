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

/*
 * Tableau avec les dénominations des profils, au singulier et au pluriel.
 */
$tab_profil_libelle = array();

$tab_profil_libelle['webmestre']         = array( 'court'=>array(1=>'webmestre','webmestres')              , 'long'=>array(1=>'webmestre','webmestres') );
$tab_profil_libelle['administrateur']    = array( 'court'=>array(1=>'administrateur','administrateurs')    , 'long'=>array(1=>'administrateur','administrateurs') );
$tab_profil_libelle['directeur']         = array( 'court'=>array(1=>'directeur','directeurs')              , 'long'=>array(1=>'personnel de direction','personnels de direction') );
$tab_profil_libelle['profcoordonnateur'] = array( 'court'=>array(1=>'prof.coord.','profs coords.')         , 'long'=>array(1=>'professeur coordonnateur','professeurs coordonnateurs') );
$tab_profil_libelle['profprincipal']     = array( 'court'=>array(1=>'prof.principal','profs principaux')   , 'long'=>array(1=>'professeur principal','professeurs principaux') );
$tab_profil_libelle['professeur']        = array( 'court'=>array(1=>'professeur','professeurs')            , 'long'=>array(1=>'personnel enseignant','personnels enseignants') ) ;
$tab_profil_libelle['aucunprof']         = array( 'court'=>array(1=>'aucun professeur','aucun professeur') , 'long'=>array(1=>'aucun professeur','aucun professeur') );
$tab_profil_libelle['parent']            = array( 'court'=>array(1=>'parent','parents')                    , 'long'=>array(1=>'responsable légal','responsables légaux') );
$tab_profil_libelle['eleve']             = array( 'court'=>array(1=>'élève','élèves')                      , 'long'=>array(1=>'élève','élèves') );
?>