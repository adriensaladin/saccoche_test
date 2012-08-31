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

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Afficher masquer des thèmes ou des domaines
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('#form_synthese input').click
		(
			function()
			{
				var ids = $(this).attr('name').substr(2);
				var option_valeur = $(this).val();
				switch (option_valeur)
				{
					case 'domaine':
						$('#domaine_'+ids).removeAttr("class");
						$('#theme_'+ids).addClass("hide");
						break;
					case 'theme':
						$('#domaine_'+ids).addClass("hide");
						$('#theme_'+ids).removeAttr("class");
						break;
					case 'sans':
						$('#domaine_'+ids).addClass("hide");
						$('#theme_'+ids).addClass("hide");
						break;
				}
				$('#bouton_'+ids).prop('disabled',false);
				$('#label_'+ids).removeAttr("class").addClass("alerte").html("Modification non enregistrée !");
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Enregistrer une modification
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('#form_synthese button').click
		(
			function()
			{
				var ids = $(this).attr('id').substr(7);
				if( $('input[name=f_'+ids+']').is(':checked')!=true )	// normalement impossible, sauf si par exemple on triche avec la barre d'outils Web Developer...
				{
					$('#label_'+ids).removeAttr("class").addClass("erreur").html("Cocher une option !");
					return(false);
				}
				var f_methode = $('input[name=f_'+ids+']:checked').val();
				var tab_infos = ids.split('_');
				var f_matiere = tab_infos[0];
				var f_niveau  = tab_infos[1];
				$('#bouton_'+ids).prop('disabled',true);
				// $("#form_synthese button").prop('disabled',true);
				$('#label_'+ids).removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_methode='+f_methode+'&f_matiere='+f_matiere+'&f_niveau='+f_niveau,
						dataType : "html",
						error : function(jqXHR, textStatus, errorThrown)
						{
							$('#bouton_'+ids).prop('disabled',false);
							// $("#form_synthese button").prop('disabled',false);
							$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$('#bouton_'+ids).prop('disabled',false);
							// $("#form_synthese button").prop('disabled',false);
							if(responseHTML!='ok')
							{
								$('#label_'+ids).removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#label_'+ids).removeAttr("class").addClass("valide").html("Modification enregistrée !");
							}
						}
					}
				);
			}
		);

	}
);
