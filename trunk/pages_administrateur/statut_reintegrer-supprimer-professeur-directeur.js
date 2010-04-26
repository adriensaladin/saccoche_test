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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Charger le select_professeurs_directeurs en ajax
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function maj_professeur_directeur()
		{
			$('#ajax_retour').html("&nbsp;");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?dossier='+DOSSIER+'&fichier=_maj_select_professeurs_directeurs',
					data : 'f_statut=0',
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez essayer de nouveau.");
					},
					success : function(responseHTML)
					{
						maj_clock(1);
						if(responseHTML.substring(0,4)=='<opt')	// option ou optgroup !
						{
							$('#ajax_msg').removeAttr("class").addClass("valide").html("Affichage actualisé !");
							$('#select_professeurs_directeurs').html(responseHTML);
						}
					else
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
						}
					}
				}
			);
		}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Réagir au clic dans un select multiple
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('select[multiple]').click
		(
			function()
			{
				$('#ajax_msg').removeAttr("class").html("&nbsp;");
				$('#ajax_retour').html("&nbsp;");
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Soumission du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('input').click
		(
			function()
			{
				id = $(this).attr('id');
				// grouper les select multiples => normalement pas besoin si name de la forme nom[], mais ça plante curieusement sur le serveur competences.sesamath.net
				// alors j'ai remplacé le $("form").serialize() par les tableaux maison et mis un explode dans le fichier ajax
				if( $("#select_professeurs_directeurs option:selected").length==0 )
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Sélectionnez au moins un professeur ou un directeur !");
					return(false);
				}
				else
				{
					var select_users = new Array(); $("#select_professeurs_directeurs option:selected").each(function(){select_users.push($(this).val());});
				}
				// On demande confirmation pour la suppression
				if(id=='supprimer')
				{
					continuer = (confirm("Attention : les associations des groupes et des matières seront perdues !\nConfirmez-vous la suppression irréversible des comptes professeurs et/ou directeurs sélectionnés ?")) ? true : false ;
				}
				else
				{
					continuer = true ;
				}
				if(continuer)
				{
					$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
					$.ajax
					(
						{
							type : 'POST',
							url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER+'&action='+id,
							data : 'select_users=' + select_users,
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
									$('#ajax_retour').html(responseHTML);
									format_liens('#ajax_retour');
									maj_professeur_directeur();
								}
							}
						}
					);
				}
			}
		);

	}
);
