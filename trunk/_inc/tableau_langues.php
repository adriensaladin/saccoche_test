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

// Tableau avec la liste des langues qu'il est possible de choisir pour le socle

$tab_langues[109] = array( 'valeur'=>109 , 'texte'=>'Allemand'       , 'tab_matiere_id'=>array(9902, 301, 315, 327, 339, 351, 363, 375) );
$tab_langues[132] = array( 'valeur'=>132 , 'texte'=>'Anglais'        , 'tab_matiere_id'=>array(9902, 302, 316, 328, 340, 352, 364, 376) );
$tab_langues[201] = array( 'valeur'=>201 , 'texte'=>'Arabe'          , 'tab_matiere_id'=>array(9902, 303, 317, 329, 341, 353, 365, 377) );
$tab_langues[216] = array( 'valeur'=>216 , 'texte'=>'Chinois'        , 'tab_matiere_id'=>array(9902, 304, 318, 330, 342, 354, 366, 378) );
$tab_langues[101] = array( 'valeur'=>101 , 'texte'=>'Danois'         , 'tab_matiere_id'=>array(9902, 305, 319, 331, 343, 355, 367, 379) );
$tab_langues[134] = array( 'valeur'=>134 , 'texte'=>'Espagnol'       , 'tab_matiere_id'=>array(9902, 306, 320, 332, 344, 356, 368, 380) );
$tab_langues[127] = array( 'valeur'=>127 , 'texte'=>'Italien'        , 'tab_matiere_id'=>array(9902, 309, 321, 333, 345, 357, 369, 381) );
$tab_langues[217] = array( 'valeur'=>217 , 'texte'=>'Japonais'       , 'tab_matiere_id'=>array(9902, 310, 322, 334, 346, 358, 370, 382) );
$tab_langues[135] = array( 'valeur'=>135 , 'texte'=>'Néerlandais'    , 'tab_matiere_id'=>array(9902, 312, 324, 336, 348, 360, 372, 384) );
$tab_langues[122] = array( 'valeur'=>122 , 'texte'=>'Polonais'       , 'tab_matiere_id'=>array(9902, 313, 325, 337, 349, 361, 373, 385) );
$tab_langues[139] = array( 'valeur'=>139 , 'texte'=>'Portuguais'     , 'tab_matiere_id'=>array(9902, 311, 323, 335, 347, 359, 371, 383) );
$tab_langues[123] = array( 'valeur'=>123 , 'texte'=>'Russe'          , 'tab_matiere_id'=>array(9902, 314, 326, 338, 350, 362, 374, 386) );
$tab_langues[100] = array( 'valeur'=>100 , 'texte'=>'LV non choisie' , 'tab_matiere_id'=>array_unique(array_merge($tab_langues[109]['tab_matiere_id'],$tab_langues[132]['tab_matiere_id'],$tab_langues[201]['tab_matiere_id'],$tab_langues[216]['tab_matiere_id'],$tab_langues[101]['tab_matiere_id'],$tab_langues[134]['tab_matiere_id'],$tab_langues[127]['tab_matiere_id'],$tab_langues[217]['tab_matiere_id'],$tab_langues[135]['tab_matiere_id'],$tab_langues[122]['tab_matiere_id'],$tab_langues[139]['tab_matiere_id'],$tab_langues[123]['tab_matiere_id'])) );

// Tableau avec la liste des piliers (compétences) en rapport avec la pratique d'une langue vivante étrangère.

$tab_langue_piliers = array(2,9);

?>