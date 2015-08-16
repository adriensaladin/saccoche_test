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

// Tableau avec la liste des langues pour traduire partiellement SACoche

$tab_langues_traduction = array(
  array(
    'statut'     => 0, // en attente
    'langue'     => array( 'code' => 'de' , 'nom' => 'Allemand' ),
    'pays'       => array( 'code' => 'DE' , 'nom' => 'Allemagne' ),
    'traducteur' => array(
      array( 'nom' => 'Robert Hirsch' , 'mail' => 'rohirsch@calixo.net' ),
    ),
  ),
  array(
    'statut'     => 93, // en attente
    'langue'     => array( 'code' => 'en'  , 'nom' => 'Anglais' ),
    'pays'       => array( 'code' => '150' , 'nom' => 'Europe' ),
    'traducteur' => array(
      array( 'nom' => 'Liouba Leroux'     , 'mail' => 'liouba.leroux@sesamath.net' ),
      array( 'nom' => 'James Chaboissier' , 'mail' => 'james.chaboissier@gmail.com' ),
    ),
  ),
  array(
    'statut'     => 98, // complète (% effectué)
    'langue'     => array( 'code' => 'es' , 'nom' => 'Espagnol' ),
    'pays'       => array( 'code' => 'CO' , 'nom' => 'Colombie' ),
    'traducteur' => array(
      array( 'nom' => 'Amaël Kervarrec' , 'mail' => 'amael.kervarrec@lfcali.edu.co' ),
    ),
  ),
  array(
    'statut'     => 96, // complète (% effectué)
    'langue'     => array( 'code' => 'es' , 'nom' => 'Espagnol' ),
    'pays'       => array( 'code' => 'ES' , 'nom' => 'Espagne' ),
    'traducteur' => array(
      array( 'nom' => 'Marlène Aussillou' , 'mail' => 'aussillou.marlene@ent-lfval.net' ),
    ),
  ),
  array(
    'statut'     => 96, // complète (% effectué)
    'langue'     => array( 'code' => 'es' , 'nom' => 'Espagnol' ),
    'pays'       => array( 'code' => 'MX' , 'nom' => 'Mexique' ),
    'traducteur' => array(
      array( 'nom' => 'Xavier Courrian' , 'mail' => 'xavier.courrian@ac-bordeaux.fr' ),
      array( 'nom' => 'Patricia Núñez'  , 'mail' => NULL ),
    ),
  ),
  array(
    'statut'     => -1, // langue originelle
    'langue'     => array( 'code' => 'fr' , 'nom' => 'Français' ),
    'pays'       => array( 'code' => 'FR' , 'nom' => 'France' ),
    'traducteur' => array( ),
  ),
  array(
    'statut'     => 0, // en attente
    'langue'     => array( 'code' => 'ja' , 'nom' => 'Japonais' ),
    'pays'       => array( 'code' => 'JP' , 'nom' => 'Japon' ),
    'traducteur' => array( ),
  ),
  array(
    'statut'     => 0, // en attente
    'langue'     => array( 'code' => 'pt' , 'nom' => 'Portugais' ),
    'pays'       => array( 'code' => 'BR' , 'nom' => 'Brésil' ),
    'traducteur' => array(
      array( 'nom' => 'Olivier Dagnat' , 'mail' => 'olivier.dagnat@hotmail.fr' ),
    ),
  ),
  array(
    'statut'     => 98, // complète (% effectué)
    'langue'     => array( 'code' => 'rcf' , 'nom' => 'Créole réunionnais' ),
    'pays'       => array( 'code' => 'RE'  , 'nom' => 'La Réunion' ),
    'traducteur' => array(
      array( 'nom' => 'Joël Macé' , 'mail' => 'joel.mace@ac-versailles.fr' ),
    ),
  ),
  array(
    'statut'     => 98, // complète (% effectué)
    'langue'     => array( 'code' => 'vi' , 'nom' => 'Vietnamien' ),
    'pays'       => array( 'code' => 'VN' , 'nom' => 'Vietnam' ),
    'traducteur' => array(
      array( 'nom' => 'Gilles Claudel'  , 'mail' => 'claudelgilles0768@gmail.com' ),
      array( 'nom' => 'Duc Hien Nguyen' , 'mail' => 'nguyen.duchien@lfay.com.vn' ),
    ),
  ),
);

?>