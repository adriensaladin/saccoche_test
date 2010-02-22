<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
$TITRE = "Options de l'environnement élève";
?>

<p><span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_options_eleve">DOC : Gestion des options de l'environnement élève</a></span></p>

<form action=""><fieldset>
	<?php
	$tab_options = array( 'ms'=>'Bilan sur une matière : ligne avec la moyenne des scores d\'acquisitions.' , 'pv'=>'Bilan sur une matière : ligne avec le pourcentage d\'items validés.' , 'as'=>'Menu : accès à l\'attestation de socle.' );
	$tab_check = explode(',',$_SESSION['ELEVE_OPTIONS']);
	$i_id = 0;	// Pour donner des ids aux checkbox et radio
	foreach($tab_options as $option_code => $option_txt)
	{
		$i_id++;
		$checked = (in_array($option_code,$tab_check)) ? ' checked="checked"' : '' ;
		echo'<label for="input_'.$i_id.'"><input type="checkbox" id="input_'.$i_id.'" name="eleve_options" value="'.$option_code.'"'.$checked.' /> '.$option_txt.'</label><br />'."\r\n";
	}
	?>
	<span class="tab"></span><input id="f_submit" type="button" value="Valider." /><label id="ajax_msg">&nbsp;</label>
</fieldset></form>
