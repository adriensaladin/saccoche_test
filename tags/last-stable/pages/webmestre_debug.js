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
		// Alerter sur la nécessité de valider
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$("#form_debug input").change
		(
			function()
			{
				$('#ajax_debug').removeAttr("class").addClass("alerte").html("Enregistrer pour confirmer.");
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Modifier les paramètres de debug
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#bouton_debug').click
		(
			function()
			{
				$('#bouton_debug').prop('disabled',true);
				$('#ajax_debug').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'csrf='+CSRF+'&f_action=modifier_debug'+'&'+$("form").serialize(),
						dataType : "html",
						error : function(jqXHR, textStatus, errorThrown)
						{
							$('#bouton_debug').prop('disabled',false);
							$('#ajax_debug').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
							return false;
						},
						success : function(responseHTML)
						{
							$('#bouton_debug').prop('disabled',false);
							if(responseHTML=='ok')
							{
								$('#ajax_debug').removeAttr("class").addClass("valide").html('Choix enregistrés.');
								initialiser_compteur();
							}
							else
							{
								$('#ajax_debug').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							return false;
						}
					}
				);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Effacer le fichier de logs de phpCAS
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#bouton_effacer').click
		(
			function()
			{
				$('#bouton_effacer').prop('disabled',true);
				$('#ajax_effacer').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'csrf='+CSRF+'&f_action=effacer_logs_phpCAS',
						dataType : "html",
						error : function(jqXHR, textStatus, errorThrown)
						{
							$('#bouton_effacer').prop('disabled',false);
							$('#ajax_effacer').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
							return false;
						},
						success : function(responseHTML)
						{
							if(responseHTML=='ok')
							{
								$('#form_phpCAS p.astuce').html('Ce fichier de logs n\'est pas présent.');
								$('#ajax_effacer').removeAttr("class").addClass("valide").html('Fichier effacé.');
								initialiser_compteur();
							}
							else
							{
								$('#bouton_effacer').prop('disabled',false);
								$('#ajax_effacer').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							return false;
						}
					}
				);
			}
		);

	}
);
