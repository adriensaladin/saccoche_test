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
if($_SESSION['SESAMATH_ID']==ID_DEMO){exit('Action désactivée pour la démo...');}

$f_objet = (isset($_POST['f_objet'])) ? clean_texte($_POST['f_objet']) : '';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Demandes d'évaluations des élèves
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($f_objet=='eleve_demandes')
{
	$nb_demandes = (isset($_POST['f_demandes'])) ? clean_entier($_POST['f_demandes']) : -1;

	if( ($nb_demandes!=-1) && ($nb_demandes<10) )
	{
		DB_STRUCTURE_modifier_parametres( array('droit_eleve_demandes'=>$nb_demandes) );
		// ne pas oublier de mettre aussi à jour la session
		$_SESSION['DROIT_ELEVE_DEMANDES'] = $nb_demandes;
		exit('ok');
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Profils autorisés à valider le socle
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($f_objet=='validation_socle')
{
	$f_entree_options = (isset($_POST['f_entree'])) ? clean_texte($_POST['f_entree']) : 'erreur';
	$f_pilier_options = (isset($_POST['f_pilier'])) ? clean_texte($_POST['f_pilier']) : 'erreur';

	// f_entree_options et f_pilier_options ne peuvent être vides, et doivent contenir la chaine 'prof'
	$nettoyage = str_replace( array('directeur','aucunprof','profprincipal','professeur') , '*' , $f_entree_options.','.$f_pilier_options );
	$nettoyage = str_replace( '*,' , '' , $nettoyage.',' );
	$test_options = ( ($nettoyage=='') && (strpos($f_entree_options,'prof')!==false) && (strpos($f_pilier_options,'prof')!==false) ) ? true : false ;

	if($test_options)
	{
		DB_STRUCTURE_modifier_parametres( array('droit_validation_entree'=>$f_entree_options,'droit_validation_pilier'=>$f_pilier_options) );
		// ne pas oublier de mettre aussi à jour la session
		$_SESSION['DROIT_VALIDATION_ENTREE'] = $f_entree_options;
		$_SESSION['DROIT_VALIDATION_PILIER'] = $f_pilier_options;
		exit('ok');
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Profils autorisés à consulter tous les référentiels de l'établissement
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($f_objet=='voir_referentiels')
{
	$f_options = (isset($_POST['f_options'])) ? clean_texte($_POST['f_options']) : 'erreur';

	if($f_options=='')
	{
		$test_options = true;
	}
	else
	{
		$nettoyage = str_replace( array('directeur','professeur','eleve') , '*' , $f_options );
		$nettoyage = str_replace( '*,' , '' , $nettoyage.',' );
		$test_options = ($nettoyage=='') ? true : false;
	}

	if($test_options)
	{
		DB_STRUCTURE_modifier_parametres( array('droit_voir_referentiels'=>$f_options) );
		// ne pas oublier de mettre aussi à jour la session
		$_SESSION['DROIT_VOIR_REFERENTIELS'] = $f_options;
		exit('ok');
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Profils autorisés à modifier leur mot de passe
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($f_objet=='modifier_mdp')
{
	$f_options = (isset($_POST['f_options'])) ? clean_texte($_POST['f_options']) : 'erreur';

	if($f_options=='')
	{
		$test_options = true;
	}
	else
	{
		$nettoyage = str_replace( array('directeur','professeur','eleve') , '*' , $f_options );
		$nettoyage = str_replace( '*,' , '' , $nettoyage.',' );
		$test_options = ($nettoyage=='') ? true : false;
	}

	if($test_options)
	{
		DB_STRUCTURE_modifier_parametres( array('droit_modifier_mdp'=>$f_options) );
		// ne pas oublier de mettre aussi à jour la session
		$_SESSION['DROIT_MODIFIER_MDP'] = $f_options;
		exit('ok');
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Environnement élève - Bilan sur une matière
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($f_objet=='eleve_bilans')
{
	$f_eleve_options = (isset($_POST['f_options'])) ? clean_texte($_POST['f_options']) : 'erreur';

	if($f_eleve_options=='')
	{
		$test_options = true;
	}
	else
	{
		$nettoyage = str_replace( array('BilanMoyenneScore','BilanPourcentageAcquis','BilanNoteSurVingt') , '*' , $f_eleve_options );
		$nettoyage = str_replace( '*,' , '' , $nettoyage.',' );
		$test_options = ($nettoyage=='') ? true : false;
	}

	if($test_options)
	{
		DB_STRUCTURE_modifier_parametres( array('droit_eleve_bilans'=>$f_eleve_options) );
		// ne pas oublier de mettre aussi à jour la session
		$_SESSION['DROIT_ELEVE_BILANS'] = $f_eleve_options;
		exit('ok');
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Environnement élève - Attestation de socle
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($f_objet=='eleve_socle')
{
	$f_eleve_options = (isset($_POST['f_options'])) ? clean_texte($_POST['f_options']) : 'erreur';

	if($f_eleve_options=='')
	{
		$test_options = true;
	}
	else
	{
		$nettoyage = str_replace( array('SocleAcces','SoclePourcentageAcquis','SocleEtatValidation') , '*' , $f_eleve_options );
		$nettoyage = str_replace( '*,' , '' , $nettoyage.',' );
		$test_options = ($nettoyage=='') ? true : false;
	}

	if($test_options)
	{
		DB_STRUCTURE_modifier_parametres( array('droit_eleve_socle'=>$f_eleve_options) );
		// ne pas oublier de mettre aussi à jour la session
		$_SESSION['DROIT_ELEVE_SOCLE'] = $f_eleve_options;
		exit('ok');
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	On ne devrait pas arriver ici
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');

?>
