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

		// Réagir au changement dans un select
		$('select').change
		(
			function()
			{
				$('#ajax_msg').removeAttr("class").html("&nbsp;");
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Validation du formulaire pour voir un référentiel d'un autre établissement
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#f_submit').click
		(
			function()
			{
				matiere_id  = $("#f_matiere option:selected").val();
				etabl_id    = $("#f_etabl option:selected").val();
				matiere_txt = $("#f_matiere option:selected").text();
				etabl_txt   = $("#f_etabl option:selected").text();
				if(!etabl_id)
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Il faut choisir un etablissement !");
					return false;
				}
				else if(!matiere_id)
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Il faut choisir une matière !");
					return false;
				}
				$('#zone_compet').html("&nbsp;");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'action=Voir&matiere='+matiere_id+'&etabl='+etabl_id,
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,18)!='<li class="li_m2">')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#zone_compet').html('<hr /><h2>'+etabl_txt+'</h2><ul class="ul_m1 link">'+matiere_txt+responseHTML+'</ul><p />');
								infobulle();
								$('#ajax_msg').removeAttr("class").addClass("valide").html("Contenu affiché ci-dessous !");
							}
						}
					}
				);
			}
		);

	}
);
