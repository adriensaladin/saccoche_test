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

		// Initialisation
		$("#f_admin").hide();

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Enlever le message ajax et le résultat précédent au changement d'un select
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('select').change
		(
			function()
			{
				$('#ajax_msg').removeAttr("class").html("&nbsp;");
				$('#bilan').html("&nbsp;");
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Charger le select f_admin en ajax
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		function maj_admin(base)
		{
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'f_action=maj_admin&f_base='+base,
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_maj').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez essayer de nouveau.");
					},
					success : function(responseHTML)
					{
						maj_clock(1);
						if(responseHTML.substring(0,7)=='<option')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#ajax_maj').removeAttr("class").html('');
							$('#f_admin').html(responseHTML).show();
						}
					else
						{
							$('#ajax_maj').removeAttr("class").addClass("alerte").html(responseHTML);
						}
					}
				}
			);
		}
		$("#f_base").change
		(
			function()
			{
				$("#f_admin").html('<option value=""></option>').hide();
				var base_val = $("#f_base").val();
				if(base_val)
				{
					$('#ajax_maj').removeAttr("class").addClass("loader").html("Actualisation en cours... Veuillez patienter.");
					maj_admin(base_val);
				}
				else
				{
					$('#ajax_maj').removeAttr("class").html("&nbsp;");
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('form');

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					f_base  : { required:true },
					f_admin : { required:true }
				},
				messages :
				{
					f_base  : { required:"établissement manquant" },
					f_admin : { required:"administrateur manquant" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element) { element.after(error); }
			}
		);

		// Options d'envoi du formulaire (avec jquery.form.js)
		var ajaxOptions =
		{
			url : 'ajax.php?page='+PAGE,
			type : 'POST',
			dataType : "html",
			clearForm : false,
			resetForm : false,
			target : "#ajax_msg",
			beforeSubmit : test_form_avant_envoi,
			error : retour_form_erreur,
			success : retour_form_valide
		};

		// Envoi du formulaire (avec jquery.form.js)
		formulaire.submit
		(
			function()
			{
				if (!please_wait)
				{
					$(this).ajaxSubmit(ajaxOptions);
					return false;
				}
				else
				{
					return false;
				}
			}
		); 

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form_avant_envoi(formData, jqForm, options)
		{
			$('#ajax_msg').removeAttr("class").html("&nbsp;");
			var readytogo = validation.form();
			if(readytogo)
			{
				please_wait = true;
				$('#ajax_msg').parent().children('q').hide();
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			please_wait = false;
			$('#ajax_msg').parent().children('q').show();
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			maj_clock(1);
			please_wait = false;
			$('#ajax_msg').parent().children('q').show();
			if(responseHTML.substring(0,17)!='<ul class="puce">')
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
