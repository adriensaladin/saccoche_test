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
//	Intercepter la touche entrée
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('input , select').keyup
		(
			function(e)
			{
				if(e.which==13)	// touche entrée
				{
					$('#bouton_valider').click();
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Alerter sur la nécessité de valider
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$("input").change
		(
			function()
			{
				$('#ajax_msg').removeAttr("class").addClass("erreur").html("Penser à valider les modifications.");
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Déplacer / afficher / masquer le formulaire CAS
// Déplacer / afficher / masquer le formulaire GEPI
// Afficher / masquer l'adresse de connexion directe
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$("input[type=radio]").click
		(
			function()
			{
				$('#cas_options , #gepi_options').hide(0);
				var valeur = $(this).val();
				var tab_infos = valeur.split('|');
				var connexion_mode = tab_infos[0];
				var connexion_nom  = tab_infos[1];
				if(connexion_mode=='cas')
				{
					var valeur = tab_param[connexion_mode][connexion_nom];
					var tab_infos = valeur.split(']¤[');
					$('#cas_serveur_host').val( tab_infos[0] );
					$('#cas_serveur_port').val( tab_infos[1] );
					$('#cas_serveur_root').val( tab_infos[2] );
					if(connexion_nom=='perso')
					{
						$(this).parent().parent().next().after( $('#cas_options') );
						$('#cas_options').show();
					}
					$('#lien_direct').show();
				}
				else if(connexion_mode=='gepi')
				{
					var valeur = tab_param[connexion_mode][connexion_nom];
					var tab_infos = valeur.split(']¤[');
					$('#gepi_saml_url').val( tab_infos[0] );
					$('#gepi_saml_rne').val( tab_infos[1] );
					$('#gepi_saml_certif').val( tab_infos[2] );
					$(this).parent().parent().next().after( $('#gepi_options') );
					$('#gepi_options').show();
					$('#lien_direct').show();
				}
				else
				{
					$('#lien_direct').hide();
				}
			}
		);

		// Initialiser son placement
		$('input[type=radio]:checked').each
		(
			function()
			{
				var valeur = $(this).val();
				var tab_infos = valeur.split('|');
				var connexion_mode = tab_infos[0];
				var connexion_nom  = tab_infos[1];
				if( (connexion_mode=='cas') && (connexion_nom=='perso') )
				{
					$(this).parent().parent().next().after( $('#cas_options') );
					$('#cas_options').show();
				}
				else if( (connexion_mode=='gepi') && (connexion_nom=='saml') )
				{
					$(this).parent().parent().next().after( $('#gepi_options') );
					$('#gepi_options').show();
				}
			}
		);


//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Mode d'identification (normal, CAS...) & paramètres associés
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#bouton_valider').click
		(
			function()
			{
				if( $('input[name=connexion_mode_nom]').is(':checked')!=true )	// normalement impossible, sauf si par exemple on triche avec la barre d'outils Web Developer...
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Cocher un mode de connexion !");
					return(false);
				}
				var connexion_mode_nom = $('input[name=connexion_mode_nom]:checked').val();
				var tab_infos = connexion_mode_nom.split('|');
				var connexion_mode = tab_infos[0];
				var connexion_nom  = tab_infos[1];
				$("#bouton_valider").prop('disabled',true);
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_connexion_mode='+connexion_mode+'&f_connexion_nom='+connexion_nom+'&'+$("form").serialize(),
						dataType : "html",
						error : function(msg,string)
						{
							$("#bouton_valider").prop('disabled',false);
							$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$("#bouton_valider").prop('disabled',false);
							if(responseHTML!='ok')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").addClass("valide").html("Mode de connexion enregistré !");
							}
						}
					}
				);
			}
		);

	}
);
