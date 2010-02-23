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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if(($_SESSION['STRUCTURE_ID']==ID_DEMO)&&($_GET['action']!='initialiser')){exit('Action désactivée pour la démo...');}

$action = (isset($_GET['action'])) ? $_GET['action'] : '';
$tab_select_users   = (isset($_POST['select_users']))   ? array_map('clean_entier',explode(',',$_POST['select_users']))   : array() ;

function positif($n) {return($n);}
$tab_select_users   = array_filter($tab_select_users,'positif');
$nb = count($tab_select_users);

$dossier_login_mdp = './__tmp/login-mdp/';
$fnom = 'identifiants_'.$_SESSION['STRUCTURE_ID'].'_eleves_'.time();

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Initialiser plusieurs noms d'utilisateurs élèves
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='eleve_login') && $nb )
{
	$tab_login = array();
	// Récupérer les données des utilisateurs concernés
	$DB_SQL = 'SELECT * FROM livret_user ';
	$DB_SQL.= 'LEFT JOIN livret_groupe ON livret_user.livret_eleve_classe_id=livret_groupe.livret_groupe_id ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_user.livret_structure_id=:structure_id AND livret_user_id IN('.implode(',',$tab_select_users).') ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_groupe_ref ASC, livret_user_nom ASC, livret_user_prenom ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// Mettre à jour les noms d'utilisateurs des utilisateurs concernés
	foreach($DB_TAB as $DB_ROW)
	{
		// Construire le login
		$login = fabriquer_login($DB_ROW['livret_user_prenom'] , $DB_ROW['livret_user_nom'] , $DB_ROW['livret_user_profil']);
		// Puis tester le login
		if( DB_tester_login($_SESSION['STRUCTURE_ID'],$login) )
		{
			// Login pris : en chercher un autre en remplaçant la fin par des chiffres si besoin
			$login = DB_rechercher_login_disponible($_SESSION['STRUCTURE_ID'],$login);
		}
		DB_modifier_utilisateur_identifiants($_SESSION['STRUCTURE_ID'],$DB_ROW['livret_user_id'],$login,false);
		$tab_login[$DB_ROW['livret_user_id']] = $login;
	}
	// Générer une sortie csv zippé
	$fcontenu = 'CLASSE'."\t".'N°SCONET'."\t".'REFERENCE'."\t".'NOM'."\t".'PRENOM'."\t".'LOGIN'."\t".'MOT DE PASSE'."\r\n\r\n";
	foreach($DB_TAB as $DB_ROW)
	{
		$fcontenu .= $DB_ROW['livret_groupe_nom']."\t".$DB_ROW['livret_user_num_sconet']."\t".$DB_ROW['livret_user_reference']."\t".$DB_ROW['livret_user_nom']."\t".$DB_ROW['livret_user_prenom']."\t".$tab_login[$DB_ROW['livret_user_id']]."\t".'inchangé'."\r\n";
	}
	$zip = new ZipArchive();
	if ($zip->open($dossier_login_mdp.$fnom.'.zip', ZIPARCHIVE::CREATE)===TRUE)
	{
		$zip->addFromString($fnom.'.csv',csv($fcontenu));
		$zip->close();
	}
	// Générer une sortie pdf (classe fpdf + script étiquettes)
	require_once('./_fpdf/PDF_Label.php');
	$pdf = new PDF_Label(array('paper-size'=>'A4', 'metric'=>'mm', 'marginLeft'=>5, 'marginTop'=>5, 'NX'=>3, 'NY'=>8, 'SpaceX'=>7, 'SpaceY'=>5, 'width'=>60, 'height'=>30, 'font-size'=>11));
	$pdf -> SetFont('Arial'); // Permet de mieux distinguer les "l 1" etc. que la police Times ou Courrier
	$pdf -> AddPage();
	$pdf -> SetFillColor(245,245,245);
	$pdf -> SetDrawColor(145,145,145);
	foreach($DB_TAB as $DB_ROW)
	{
		$text = $DB_ROW['livret_groupe_nom']."\r\n";
		$text.= $DB_ROW['livret_user_nom'].' '.$DB_ROW['livret_user_prenom']."\r\n";
		$text.= 'Utilisateur : '.$tab_login[$DB_ROW['livret_user_id']]."\r\n";
		$text.= 'Mot de passe : inchangé';
		$pdf -> Add_Label(pdf($text));
	}
	$pdf->Output($dossier_login_mdp.$fnom.'.pdf','F');
	// Affichage du résultat
	echo'<hr />';
	echo'<div><a class="lien_ext" href="'.$dossier_login_mdp.$fnom.'.zip">Récupérez les nouveaux identifiants dans un fichier csv tabulé pour tableur.</a></div>';
	echo'<div><a class="lien_ext" href="'.$dossier_login_mdp.$fnom.'.pdf">Récupérez les nouveaux identifiants dans un fichier pdf (étiquettes à imprimer).</a></div>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Initialiser plusieurs mots de passe élèves
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

elseif( ($action=='eleve_mdp') && $nb )
{
	$tab_password = array();
	// Mettre à jour les mots de passe des utilisateurs concernés
	foreach($tab_select_users as $user_id)
	{
		$password = fabriquer_mdp();
		DB_modifier_utilisateur_identifiants($_SESSION['STRUCTURE_ID'],$user_id,false,$password);
		$tab_password[$user_id] = $password;
	}
	// Récupérer les données des utilisateurs concernés
	$DB_SQL = 'SELECT * FROM livret_user ';
	$DB_SQL.= 'LEFT JOIN livret_groupe ON livret_user.livret_eleve_classe_id=livret_groupe.livret_groupe_id ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_user.livret_structure_id=:structure_id AND livret_user_id IN('.implode(',',$tab_select_users).') ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_groupe_ref ASC, livret_user_nom ASC, livret_user_prenom ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// Générer une sortie csv zippé
	$fcontenu = 'CLASSE'."\t".'N°SCONET'."\t".'REFERENCE'."\t".'NOM'."\t".'PRENOM'."\t".'LOGIN'."\t".'MOT DE PASSE'."\r\n\r\n";
	foreach($DB_TAB as $DB_ROW)
	{
		$fcontenu .= $DB_ROW['livret_groupe_nom']."\t".$DB_ROW['livret_user_num_sconet']."\t".$DB_ROW['livret_user_reference']."\t".$DB_ROW['livret_user_nom']."\t".$DB_ROW['livret_user_prenom']."\t".$DB_ROW['livret_user_login']."\t".$tab_password[$DB_ROW['livret_user_id']]."\r\n";
	}
	$zip = new ZipArchive();
	if ($zip->open($dossier_login_mdp.$fnom.'.zip', ZIPARCHIVE::CREATE)===TRUE)
	{
		$zip->addFromString($fnom.'.csv',csv($fcontenu));
		$zip->close();
	}
	// Générer une sortie pdf (classe fpdf + script étiquettes)
	require_once('./_fpdf/PDF_Label.php');
	$pdf = new PDF_Label(array('paper-size'=>'A4', 'metric'=>'mm', 'marginLeft'=>5, 'marginTop'=>5, 'NX'=>3, 'NY'=>8, 'SpaceX'=>7, 'SpaceY'=>5, 'width'=>60, 'height'=>30, 'font-size'=>11));
	$pdf -> SetFont('Arial'); // Permet de mieux distinguer les "l 1" etc. que la police Times ou Courrier
	$pdf -> AddPage();
	$pdf -> SetFillColor(245,245,245);
	$pdf -> SetDrawColor(145,145,145);
	foreach($DB_TAB as $DB_ROW)
	{
		$text = $DB_ROW['livret_groupe_nom']."\r\n";
		$text.= $DB_ROW['livret_user_nom'].' '.$DB_ROW['livret_user_prenom']."\r\n";
		$text.= 'Utilisateur : '.$DB_ROW['livret_user_login']."\r\n";
		$text.= 'Mot de passe : '.$tab_password[$DB_ROW['livret_user_id']];
		$pdf -> Add_Label(pdf($text));
	}
	$pdf->Output($dossier_login_mdp.$fnom.'.pdf','F');
	// Affichage du résultat
	echo'<hr />';
	echo'<div><a class="lien_ext" href="'.$dossier_login_mdp.$fnom.'.zip">Récupérez les nouveaux identifiants dans un fichier csv tabulé pour tableur.</a></div>';
	echo'<div><a class="lien_ext" href="'.$dossier_login_mdp.$fnom.'.pdf">Récupérez les nouveaux identifiants dans un fichier pdf (étiquettes à imprimer).</a></div>';
	echo'<div><label class="alerte">Attention : les mots de passe, cryptés, ne sont plus accessibles ultérieurement !</label></div>';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
