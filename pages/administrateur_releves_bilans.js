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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	ORDONNER => Clic sur une image pour modifier l'ordre des matières
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		var modification = false;

		$('#form_ordonner input[type=image]').click
		(
			function()
			{
				para_clic = $(this).parent();
				para_prev = para_clic.prev('em');
				para_next = para_clic.next('em');
				para_clic.before(para_next);
				para_clic.after(para_prev);
				$('#ajax_msg_ordre').removeAttr("class").addClass("alerte").html("Ordre non enregistré !");
				modification = true;
				return false;
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	ORDONNER => Clic sur le lien pour mettre à jour l'ordre des matières
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#Enregistrer_ordre').click
		(
			function()
			{
				if(!modification)
				{
					$('#ajax_msg_ordre').removeAttr("class").addClass("alerte").html("Aucune modification effectuée !");
				}
				else
				{
					// On récupère la liste des matières dans l'ordre de la page
					var tab_id = new Array();
					$('#form_ordonner fieldset').children('em').each
					(
						function()
						{
							var test_id = $(this).attr('id').substring(2);
							if(test_id)
							{
								tab_id.push(test_id);
							}
						}
					);
					$('#form_ordonner button').attr('disabled','disabled');
					$('#ajax_msg_ordre').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
					$.ajax
					(
						{
							type : 'POST',
							url : 'ajax.php?page='+PAGE,
							data : 'f_action=enregistrer_ordre&tab_id='+tab_id,
							dataType : "html",
							error : function(msg,string)
							{
								$('#form_ordonner button').removeAttr('disabled');
								$('#ajax_msg_ordre').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
								return false;
							},
							success : function(responseHTML)
							{
								maj_clock(1);
								$('#form_ordonner button').removeAttr('disabled');
								if(responseHTML!='ok')
								{
									$('#ajax_msg_ordre').removeAttr("class").addClass("alerte").html(responseHTML);
								}
								else
								{
									modification = false;
									$('#ajax_msg_ordre').removeAttr("class").addClass("valide").html("Ordre enregistré !");
								}
							}
						}
					);
				}
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	PARAMÉTRAGE => Afficher masquer des thèmes ou des domaines
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
				$('#bouton_'+ids).removeAttr("class");
				$('#label_'+ids).removeAttr("class").addClass("alerte").html("Modification non enregistrée !");
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	PARAMÉTRAGE => Enregistrer une modification
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('#form_synthese button').click
		(
			function()
			{
				var ids = $(this).attr('id').substr(7);
				if( $('input[name=f_'+ids+']').is(":checked")!=true )	// normalement impossible, sauf si par exemple on triche avec la barre d'outils Web Developer...
				{
					$('#label_'+ids).removeAttr("class").addClass("erreur").html("Cocher une option !");
					return(false);
				}
				var f_methode = $('input[name=f_'+ids+']:checked').val();
				var tab_infos = ids.split('_');
				var f_matiere = tab_infos[0];
				var f_niveau  = tab_infos[1];
				$("#form_synthese button").attr('disabled','disabled');
				$('#label_'+ids).removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_action='+'modifier_mode_synthese'+'&f_methode='+f_methode+'&f_matiere='+f_matiere+'&f_niveau='+f_niveau,
						dataType : "html",
						error : function(msg,string)
						{
							$("#form_synthese button").removeAttr('disabled');
							$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							$("#form_synthese button").removeAttr('disabled');
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
