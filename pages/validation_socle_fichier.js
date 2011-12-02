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

		$("#select_eleves").hide();

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		// Charger le select f_eleve en ajax
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function maj_eleve(groupe_id,groupe_type)
		{
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page=_maj_select_eleves',
					data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_statut=1',
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_msg_groupe').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						if(responseHTML.substring(0,7)=='<option')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#ajax_msg_groupe').removeAttr("class").addClass("valide").html("Affichage actualisé !");
							$('#select_eleves').html(responseHTML).show();
						}
						else
						{
							$('#ajax_msg_groupe').removeAttr("class").addClass("alerte").html(responseHTML);
						}
					}
				}
			);
		}
		function changer_groupe()
		{
			$("#select_eleves").html('<option value=""></option>').hide();
			var groupe_val = $("#f_groupe").val();
			if(groupe_val)
			{
				// type = $("#f_groupe option:selected").parent().attr('label');
				groupe_type = groupe_val.substring(0,1);
				groupe_id   = groupe_val.substring(1);
				$('#ajax_msg_groupe').removeAttr("class").addClass("loader").html("Actualisation en cours...");
				maj_eleve(groupe_id,groupe_type);
			}
			else
			{
				$('#ajax_msg_groupe').removeAttr("class").html("&nbsp;");
			}
		}
		$("#f_groupe").change
		(
			function()
			{
				changer_groupe();
			}
		);

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		// Réagir au changement dans le premier formulaire (choix principal)
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$("#f_choix_principal").change
		(
			function()
			{
				// Masquer tout
				$('fieldset[id^=fieldset]').hide(0);
				$('#ajax_msg').removeAttr("class").html("&nbsp;");
				$('#ajax_info').html("");
				// Puis afficher ce qu'il faut
				var objet = $(this).val();
				if(objet)
				{
					var tab_infos = objet.split('_');
					var mode = tab_infos[0];
					$('#fieldset_'+mode).show();
					$('#fieldset_'+objet).show();
				}
			}
		);

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		// Exporter un fichier de validations
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#export_lpc , #export_sacoche').click
		(
			function()
			{
				var action = $(this).attr('id');
				// grouper le select multiple
				if( $("#select_eleves option:selected").length==0 )
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Sélectionnez au moins un élève !");
					return(false);
				}
				else
				{
					var select_eleves = new Array(); $("#select_eleves option:selected").each(function(){select_eleves.push($(this).val());});
				}
				// on envoie
				$('button.enabled').prop('disabled',true);
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée...");
				$('#ajax_info').html("");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_action='+action + '&' + 'select_eleves=' + select_eleves,
						dataType : "html",
						error : function(msg,string)
						{
							$('button.enabled').prop('disabled',false);
							$('#import_lpc_disabled').prop('disabled',true);
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion !');
							return false;
						},
						success : function(responseHTML)
						{
							$('button.enabled').prop('disabled',false);
							$('#import_lpc_disabled').prop('disabled',true);
							if(responseHTML.substring(0,4)!='<li>')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('');
								$('#ajax_info').html(responseHTML);
								format_liens('#ajax_info');
								initialiser_compteur();
							}
						}
					}
				);
			}
		);

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		// Importer un fichier de validations avec jquery.ajaxupload.js
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		if($('#import_sacoche').length)
		{
			new AjaxUpload
			('#import_sacoche',
				{
					action: 'ajax.php?page='+PAGE,
					name: 'userfile',
					data: {'f_action':'import_sacoche'},
					autoSubmit: true,
					responseType: "html",
					onChange: changer_fichier,
					onSubmit: verifier_fichier,
					onComplete: retourner_fichier
				}
			);
		}

		function changer_fichier(fichier_nom,fichier_extension)
		{
			$('button.enabled').prop('disabled',true);
			$("#ajax_info").html('');
			$('#ajax_msg').removeAttr("class").html('');
			return true;
		}

		function verifier_fichier(fichier_nom,fichier_extension)
		{
			if (fichier_nom==null || fichier_nom.length<5)
			{
				$('button.enabled').prop('disabled',false);
				$('#import_lpc_disabled').prop('disabled',true);
				$('#ajax_msg').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
				return false;
			}
			else if ('.xml.zip.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1)
			{
				$('button.enabled').prop('disabled',false);
				$('#import_lpc_disabled').prop('disabled',true);
				$('#ajax_msg').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas une extension "xml" ou "zip".');
				return false;
			}
			else
			{
				$('#ajax_msg').removeAttr("class").addClass("loader").html('Fichier envoyé...');
				return true;
			}
		}

		function retourner_fichier(fichier_nom,responseHTML)	// Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
		{
			$('button.enabled').prop('disabled',false);
			$('#import_lpc_disabled').prop('disabled',true);
			if(responseHTML.substring(0,4)!='<li>')
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				$('#ajax_msg').removeAttr("class").html('');
				$('#ajax_info').html(responseHTML);
				initialiser_compteur();
			}
		}

	}
);
