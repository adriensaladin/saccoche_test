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
		//	Afficher / masquer le choix du motif du blocage
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('#f_debloquer , #f_bloquer').click
		(
			function()
			{
				if($('#f_bloquer').is(':checked'))
				{
					$('#span_motif').show();
					$('#f_motif').focus();
				}
				else
				{
					$('#span_motif').hide();
				}
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Autocompléter le motif du blocage
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		tab_proposition = new Array();
		tab_proposition["rien"]         = "";
		tab_proposition["mise-a-jour"]  = "Mise à jour des fichiers en cours.";
		tab_proposition["maintenance"]  = "Maintenance sur le serveur en cours.";
		tab_proposition["demenagement"] = "Déménagement de l'application en cours.";

		$('#f_proposition').change
		(
			function()
			{
				$('#f_motif').val( tab_proposition[ $(this).val() ] ).focus();
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Mise à jour des label comparant la version installée et la version disponible
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		function maj_label_versions()
		{
			var classe = ( $('#ajax_version_installee').text() == $('#ajax_version_disponible').text() ) ? 'valide' : 'alerte' ;
			$('#ajax_version_installee').removeAttr("class").addClass(classe);
		}

		maj_label_versions();

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		// Traitement du formulaire principal
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('#form');

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					f_action : { required:true }
				},
				messages :
				{
					f_action : { required:"choix manquant" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element) { $('#ajax_msg').html(error); }
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
				$(this).ajaxSubmit(ajaxOptions);
				return false;
			}
		); 

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form_avant_envoi(formData, jqForm, options)
		{
			$('#ajax_msg').removeAttr("class").html("&nbsp;");
			var readytogo = validation.form();
			if(readytogo)
			{
				$("#bouton_valider").attr('disabled','disabled');
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Traitement de la demande en cours... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			$("#bouton_valider").removeAttr('disabled');
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			maj_clock(1);
			$("#bouton_valider").removeAttr('disabled');
			if(responseHTML.substring(0,13)=='<label class=')
			{
				
				$('#ajax_msg').removeAttr("class").addClass("valide").html("Demande prise en compte.").fadeOut(2000,function(){$(this).removeAttr("class").html("").show();});
				$('#ajax_acces_actuel').html(responseHTML);
			}
			else
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
		} 

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Mise à jour automatique des fichiers
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		var etape_numero = 0 ;

		function maj_etape(etape_info)
		{
			etape_numero++;
			if(etape_numero==6)
			{
				$('#ajax_maj').removeAttr("class").addClass("valide").html('Mise à jour terminée !');
				$('#ajax_version_installee').html(etape_info);
				maj_label_versions();
				$('button').removeAttr('disabled');
				return false;
			}
			$('#puces_maj').append('<li>Etape '+etape_numero+' : '+etape_info+'</li>');
			format_liens('#puces_maj');
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'f_action=maj_etape'+etape_numero,
					dataType : "html",
					error : function(msg,string)
					{
						$('button').removeAttr('disabled');
						$('#ajax_maj').removeAttr("class").addClass("alerte").html('Echec de la connexion !');
						return false;
					},
					success : function(responseHTML)
					{
						var tab_infos = responseHTML.split(']¤[');
						if( (tab_infos.length!=3) || (tab_infos[0]!='') )
						{
							$('button').removeAttr('disabled');
							$('#ajax_maj').removeAttr("class").addClass("alerte").html(tab_infos[0]);
							return false;
						}
						if(tab_infos[1]!='ok')
						{
							$('button').removeAttr('disabled');
							$('#ajax_maj').removeAttr("class").addClass("alerte").html(tab_infos[2]);
							return false;
						}
						maj_etape(tab_infos[2]);
					}
				}
			);
		}

		$('#bouton_maj').click
		(
			function()
			{
				etape_numero = 0 ;
				$('#puces_maj').html('').show();
				if( $('#ajax_version_installee').text() > $('#ajax_version_disponible').text() )
				{
					$('#ajax_maj').removeAttr("class").addClass("erreur").html("Version installée postérieure à la version disponible !");
					return false;
				}
				$('button').attr('disabled','disabled');
				$('#ajax_maj').removeAttr("class").addClass("loader").html("Mise à jour en cours&hellip; Veuillez patienter.");
				maj_etape("Récupération de l'archive <em>zip</em>&hellip;");
			}
		);

	}
);
