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

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic sur la checkbox et état du bouton d'enregistrement
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#confirmation_cnil').click
		(
			function()
			{
				if($(this).is(':checked'))
				{
					$('#f_enregistrer').prop('disabled',false);
				}
				else
				{
					$('#f_enregistrer').prop('disabled',true);
				}
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic sur le bouton d'enregistrement
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#f_enregistrer').click
		(
			function()
			{
				$('#ajax_msg_enregistrer').removeAttr("class").addClass("loader").html("Envoi en cours&hellip;");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'csrf='+CSRF+'&f_action=Valider_CNIL',
						dataType : "html",
						error : function(jqXHR, textStatus, errorThrown)
						{
							$('#ajax_msg_enregistrer').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
							return false;
						},
						success : function(responseHTML)
						{
							if(responseHTML!='ok')
							{
								$('#ajax_msg_enregistrer').removeAttr("class").addClass("alerte").html(responseHTML);
								return false;
							}
							else
							{
								$('#ajax_msg_enregistrer').removeAttr("class").addClass("valide").html("Compte activé.");
								document.location.href = './index.php?page=compte_accueil';
							}
						}
					}
				);
			}
		);

	}
);
