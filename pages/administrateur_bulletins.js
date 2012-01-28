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

// jQuery !
$(document).ready
(
	function()
	{

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		// Traitement du formulaire form_mise_en_page
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		// Alerter sur la nécessité de valider
		$("#form_mise_en_page input , #form_mise_en_page select").change
		(
			function()
			{
				$('#ajax_msg_mise_en_page').removeAttr("class").addClass("alerte").html("Enregistrer pour confirmer.");
			}
		);

		// Afficher / masquer p_enveloppe
		$("#f_infos_responsables").change
		(
			function()
			{
				if( $('#f_infos_responsables option:selected').val() == 'oui_force' )
				{
					$("#p_enveloppe").show('slow');
				}
				else
				{
					$("#p_enveloppe").hide('slow');
				}
			}
		);

		$('#bouton_valider_mise_en_page').click
		(
			function()
			{
				if( $('#f_infos_responsables option:selected').val() == 'oui_force' )
				{
					// Vérifier les dimensions de l'enveloppe
					var enveloppe_largeur = parseInt($('#f_horizontal_gauche').val(),10) + parseInt($('#f_horizontal_milieu').val(),10) + parseInt($('#f_horizontal_droite').val(),10) ;
					var enveloppe_hauteur = parseInt($('#f_vertical_haut').val(),10)     + parseInt($('#f_vertical_milieu').val(),10)   + parseInt($('#f_vertical_bas').val(),10) ;
					if( (enveloppe_largeur<215) || (enveloppe_largeur>235) )
					{
						$('#ajax_msg_mise_en_page').removeAttr("class").addClass("erreur").html("Dimensions incorrectes : la longueur de l'enveloppe doit être comprise entre 21,5cm et 23,5cm.");
						return false;
					}
					if( (enveloppe_hauteur<105) || (enveloppe_hauteur>125) )
					{
						$('#ajax_msg_mise_en_page').removeAttr("class").addClass("erreur").html("Dimensions incorrectes : la hauteur de l'enveloppe doit être comprise entre 10,5cm et 12,5cm.");
						return false;
					}
				}
				$("#bouton_valider_mise_en_page").prop('disabled',true);
				$('#ajax_msg_mise_en_page').removeAttr("class").addClass("loader").html("Soumission du formulaire en cours...");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'action=mise_en_page&'+$('#form_mise_en_page').serialize(),
						dataType : "html",
						error : function(msg,string)
						{
							$("#bouton_valider_mise_en_page").prop('disabled',false);
							$('#ajax_msg_mise_en_page').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$("#bouton_valider_mise_en_page").prop('disabled',false);
							if(responseHTML!='ok')
							{
								$('#ajax_msg_mise_en_page').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg_mise_en_page').removeAttr("class").addClass("valide").html("Données enregistrées !");
							}
							return false;
						}
					}
				);
			}
		);

	}
);
