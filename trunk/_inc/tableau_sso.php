<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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

// Tableau avec les différents modes d'identification possibles

$tab_sso = array();
$tab_sso['normal']                  = array('txt'=>'Mode de connexion normal avec les identifiants enregistrés dans SACoche.' , 'doc'=>false);
$tab_sso['ent-auth.ac-bordeaux.fr'] = array('doc'=>'academie_Bordeaux' , 'nom'=>3 , 'prenom'=>4 , 'id_ent'=>1 , 'txt'=>'ENT Argos de l\'académie de Bordeaux (départements 24,33,40,47).');
$tab_sso['cas.argos64.fr']          = array('doc'=>'academie_Bordeaux' , 'nom'=>3 , 'prenom'=>4 , 'id_ent'=>1 , 'txt'=>'ENT Argos64 (département des Pyrénées-Atlantiques).');
$tab_sso['cas.entmip.fr']           = array('doc'=>'academie_Toulouse' , 'nom'=>4 , 'prenom'=>3 , 'id_ent'=>1 , 'txt'=>'ENT Midi-Pyrénées K-d\'Ecole de l\'académie de Toulouse.');
$tab_sso['cas.cybercolleges42.fr']  = array('doc'=>'departement_Loire' , 'nom'=>4 , 'prenom'=>3 , 'id_ent'=>1 , 'txt'=>'ENT Cybercollège 42 du département de la Loire.');

?>