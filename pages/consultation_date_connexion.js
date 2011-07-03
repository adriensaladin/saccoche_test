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

		// tri du tableau (avec jquery.tablesorter.js).
		var sorting = [[1,1]];
		$('table#bilan').tablesorter({ headers:{} });
		function trier_tableau()
		{
			if($('table#bilan tbody tr td').length>1)
			{
				$('table#bilan').trigger('update');
				$('table#bilan').trigger('sorton',[sorting]);
			}
		}

		$("#f_groupe").change
		(
			function()
			{
				var groupe_val = $("#f_groupe").val();
				if(!groupe_val)
				{
					$('#ajax_msg').removeAttr("class").html("&nbsp;");
					$('#bilan').addClass("hide");
					return false
				}
				// Pour un directeur ou un administrateur, groupe_val est de la forme d3 / n2 / c51 / g44
				if(isNaN(parseInt(groupe_val)))
				{
					groupe_type = groupe_val.substring(0,1);
					groupe_id   = groupe_val.substring(1);
				}
				// Pour un professeur, groupe_val est un entier, et il faut récupérer la 1ère lettre du label parent
				else
				{
					groupe_type = $("#f_groupe option:selected").parent().attr('label').substring(0,1).toLowerCase();
					groupe_id   = groupe_val;
				}
				$('button').prop('disabled',true);
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Veuillez patienter...");
				$('#bilan tbody').html('');
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type,
						dataType : "html",
						error : function(msg,string)
						{
							$('button').prop('disabled',false);
							$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$('button').prop('disabled',false);
							if( (responseHTML.substring(0,4)!='<tr>') && (responseHTML!='') )
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
								$('#bilan').addClass("hide");
							}
							else
							{
								$('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
								$('#bilan tbody').html(responseHTML);
								trier_tableau();
								$('#bilan').removeAttr("class");
							}
						}
					}
				);
			}
		);

	}
);
