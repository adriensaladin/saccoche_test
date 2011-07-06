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
		// Appel en ajax pour lancer un nettoyage
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('#bouton_numeroter , #bouton_nettoyer , #bouton_purger , #bouton_supprimer , #bouton_effacer').click
		(
			function()
			{
				var action = $(this).attr('id').substring(7); // "nettoyer" ou "purger" ou "supprimer" ou "effacer"
				if(action=='purger')
				{
					var continuer = (confirm("Attention : les scores déjà saisis ne seront plus modifiables !\nConfirmez-vous l'initialisation annuelle des données ?")) ? true : false ;
				}
				else if(action=='supprimer')
				{
					var continuer = (confirm("Attention : toutes les notes des élèves seront effacées !\nConfirmez-vous la suppression des scores et des validations ?")) ? true : false ;
				}
				else
				{
					var continuer = true;
				}
				if(continuer)
				{
					$("button").prop('disabled',true);
					$("label").removeAttr("class").html('');
					$("#ajax_info").html('<li></li>').hide();
					$('#ajax_msg_'+action).addClass("loader").html("Demande envoyée... Veuillez patienter.");
					$.ajax
					(
						{
							type : 'POST',
							url : 'ajax.php?page='+PAGE,
							data : 'f_action='+action,
							dataType : "html",
							error : function(msg,string)
							{
								$("button").prop('disabled',false);
								$('#ajax_msg_'+action).removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
								return false;
							},
							success : function(responseHTML)
							{
								$("button").prop('disabled',false);
								if(responseHTML.substring(0,4)!='<li>')
								{
									$('#ajax_msg_'+action).removeAttr("class").addClass("alerte").html(responseHTML);
								}
								else
								{
									$('#ajax_msg_'+action).removeAttr("class").html('');
									$('#ajax_info').html(responseHTML).show().appendTo('#form_'+action);
									initialiser_compteur();
								}
							}
						}
					);
				}
			}
		);

	}
);
