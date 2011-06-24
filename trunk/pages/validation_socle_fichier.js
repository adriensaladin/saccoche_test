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

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		// Exporter un fichier de validations à destination de LPC
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('#bouton_export').click
		(
			function()
			{
				$("#bouton_export").prop('disabled',true);
				$("#ajax_info").html('');
				$('#ajax_msg_export').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$('#ajax_msg_import').removeAttr("class").html('');
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_action=exporter',
						dataType : "html",
						error : function(msg,string)
						{
							$("#bouton_export").prop('disabled',false);
							$('#ajax_msg_export').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							$("#bouton_export").prop('disabled',false);
							if(responseHTML.substring(0,4)!='<li>')
							{
								$('#ajax_msg_export').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg_export').removeAttr("class").html('');
								$('#ajax_info').html(responseHTML);
								format_liens('#ajax_info');
								maj_clock(1);
							}
						}
					}
				);
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		// Importer un fichier de validations en provenance de LPC avec jquery.ajaxupload.js
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		if($('#bouton_import').length)
		{
			new AjaxUpload
			('#bouton_import',
				{
					action: 'ajax.php?page='+PAGE,
					name: 'userfile',
					data: {'f_action':'uploader'},
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
			$("#bouton_import").prop('disabled',true);
			$("#ajax_info").html('');
			$('#ajax_msg_import').removeAttr("class").html('');
			$('#ajax_msg_export').removeAttr("class").html('');
			return true;
		}

		function verifier_fichier(fichier_nom,fichier_extension)
		{
			if (fichier_nom==null || fichier_nom.length<5)
			{
				$("#bouton_import").prop('disabled',false);
				$('#ajax_msg_import').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
				return false;
			}
			else if(fichier_extension.toLowerCase()!='zip')
			{
				$("#bouton_import").prop('disabled',false);
				$('#ajax_msg_import').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas l\'extension zip.');
				return false;
			}
			else
			{
				$('#ajax_msg_import').removeAttr("class").addClass("loader").html('Fichier envoyé... Veuillez patienter.');
				return true;
			}
		}

		function retourner_fichier(fichier_nom,responseHTML)	// Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
		{
			if( (responseHTML.substring(0,26)!='<li><label class="valide">') && (responseHTML.substring(0,24)!='<LI><LABEL class=valide>') )
			{
				$("#bouton_import").prop('disabled',false);
				$('#ajax_msg_import').removeAttr("class").html('');
				$('#ajax_info').html(responseHTML);
			}
			else
			{
				$('#ajax_msg_import').html('Demande traitée... Veuillez patienter.');
				$('#ajax_info').html(responseHTML);
				maj_clock(1);
				importer();
			}
		}

		function importer()
		{
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'f_action=importer',
					dataType : "html",
					error : function(msg,string)
					{
						$("#bouton_import").prop('disabled',false);
						$('#ajax_msg_import').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
						return false;
					},
					success : function(responseHTML)
					{
						$("#bouton_import").prop('disabled',false);
						if(responseHTML.substring(0,4)!='<li>')
						{
							$('#ajax_msg_import').removeAttr("class").addClass("alerte").html(responseHTML);
						}
						else
						{
							$('#ajax_msg_import').removeAttr("class").html('');
							$('#ajax_info').append(responseHTML);
							format_liens('#ajax_info');
							maj_clock(1);
						}
					}
				}
			);
		}

	}
);
