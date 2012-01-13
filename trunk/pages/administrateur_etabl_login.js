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
// Alerter sur la nécessité de valider
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$("input").change
		(
			function()
			{
				$('#ajax_msg_login').removeAttr("class").addClass("erreur").html("Penser à valider les modifications.");
			}
		);

		$("select").change
		(
			function()
			{
				$('#ajax_msg_mdp_mini').removeAttr("class").addClass("erreur").html("Penser à valider la modification.");
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Format des noms d'utilisateurs
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function test_format_login(format)
		{
			var reg1 = new RegExp("^p+[._-]?n+$","g");
			var reg2 = new RegExp("^n+[._-]?p+$","g");
			test = ( reg1.test(format) || reg2.test(format) ) ? true : false ;
			return test;
		}

		$('#bouton_valider_login').click
		(
			function()
			{
				var tab_profil = new Array('directeur','professeur','eleve','parent');
				var tab_value  = new Array();
				var datas = 'action=login';
				var imax = tab_profil.length;
				for ( var i=0 ; i<imax ; i++ )
				{
					tab_value[i] = $('#f_login_'+tab_profil[i]).val();
					if( test_format_login(tab_value[i])==false )
					{
						$('#ajax_msg_login').removeAttr("class").addClass("erreur").html("Le format du nom d'utilisateur "+tab_profil[i]+" est incorrect !");
						return(false);
					}
					datas += '&f_login_'+tab_profil[i]+'='+tab_value[i];
				}
				$("#bouton_valider_login").prop('disabled',true);
				$('#ajax_msg_login').removeAttr("class").addClass("loader").html("Demande envoyée...");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : datas,
						dataType : "html",
						error : function(msg,string)
						{
							$("#bouton_valider_login").prop('disabled',false);
							$('#ajax_msg_login').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$("#bouton_valider_login").prop('disabled',false);
							if(responseHTML!='ok')
							{
								$('#ajax_msg_login').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg_login').removeAttr("class").addClass("valide").html("Formats enregistrés !");
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Longueur minimale d'un mot de passe
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#bouton_valider_mdp_mini').click
		(
			function()
			{
				$("#bouton_valider_mdp_mini").prop('disabled',true);
				$('#ajax_msg_mdp_mini').removeAttr("class").addClass("loader").html("Demande envoyée...");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'action=mdp_mini&f_mdp_mini='+$('#f_mdp_mini option:selected').val(),
						dataType : "html",
						error : function(msg,string)
						{
							$("#bouton_valider_mdp_mini").prop('disabled',false);
							$('#ajax_msg_mdp_mini').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$("#bouton_valider_mdp_mini").prop('disabled',false);
							if(responseHTML!='ok')
							{
								$('#ajax_msg_mdp_mini').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg_mdp_mini').removeAttr("class").addClass("valide").html("Valeur enregistrée !");
							}
						}
					}
				);
			}
		);

	}
);
