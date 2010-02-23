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

// jQuery !
$(document).ready
(
	function()
	{

		// Réagir au clic dans un select
		$('select').click
		(
			function()
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html("Pensez à valider vos modifications !");
			}
		);

		// Réagir au clic sur un bouton (soumission du formulaire)
		$('input').click
		(
			function()
			{
				id = $(this).attr('id');
				if( $("#select_professeurs option:selected").length==0 || $("#select_groupes option:selected").length==0 )
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Sélectionnez dans les deux listes !");
					return(false);
				}
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				// grouper les select multiples => normalement pas besoin si name de la forme nom[], mais ça plante curieusement sur le serveur competences.sesamath.net
				// alors j'ai remplacé le $("form").serialize() par les tableaux maison et mis un explode dans le fichier ajax
				var select_professeurs = new Array(); $("#select_professeurs option:selected").each(function(){select_professeurs.push($(this).val());});
				var select_groupes = new Array(); $("#select_groupes option:selected").each(function(){select_groupes.push($(this).val());});
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER+'&action='+id,
						data : 'select_professeurs=' + select_professeurs + '&select_groupes=' + select_groupes,
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,6)!='<hr />')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
								$('#bilan').html(responseHTML);
							}
						}
					}
				);
			}
		);

		// Initialisation : charger au chargement l'affichage du bilan
		$('#ajax_msg').addClass("loader").html("Chargement en cours... Veuillez patienter.");
		$.ajax
		(
			{
				type : 'GET',
				url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER+'&action=initialiser',
				data : '',
				dataType : "html",
				error : function(msg,string)
				{
					$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
					return false;
				},
				success : function(responseHTML)
				{
					maj_clock(1);
					if(responseHTML.substring(0,6)!='<hr />')
					{
						$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
					}
					else
					{
						$('#ajax_msg').removeAttr("class").html("&nbsp;");
						$('#bilan').html(responseHTML);
					}
				}
			}
		);

	}
);
