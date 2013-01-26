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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Intercepter la touche entrée
// ////////////////////////////////////////////////////////////////////////////////////////////////////
    $('input').keyup
    (
      function(e)
      {
        if(e.which==13)  // touche entrée
        {
          $('#bouton_valider').click();
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Alerter sur la nécessité de valider
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("select , input").change
    (
      function()
      {
        $('#ajax_msg').removeAttr("class").addClass("alerte").html("Penser à valider les modifications.");
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher / masquer le formulaire CAS
// Afficher / masquer le formulaire GEPI
// Afficher / masquer l'adresse de connexion directe
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function actualiser_formulaire()
    {
      // on masque
      $('#cas_options , #gepi_options , #lien_direct , #lien_gepi , #info_inacheve').hide();
      // on récupère les infos
      var valeur = $('#connexion_mode_nom option:selected').val();
      var tab_infos = valeur.split('~');
      var connexion_mode = tab_infos[0];
      var connexion_ref  = tab_infos[1];
      if(connexion_mode=='cas')
      {
        var valeur = tab_param[connexion_mode][connexion_ref];
        var tab_infos = valeur.split(']¤[');
        var is_operationnel = tab_infos[0];
        $('#cas_serveur_host').val( tab_infos[1] );
        $('#cas_serveur_port').val( tab_infos[2] );
        $('#cas_serveur_root').val( tab_infos[3] );
        if(connexion_ref=='|perso')
        {
          $('#cas_options').show();
        }
        if(is_operationnel=='1')
        {
          $("#bouton_valider").prop('disabled',false);
          $('#lien_direct').show();
        }
        else
        {
          $("#bouton_valider").prop('disabled',true);
          $('#info_inacheve').show();
        }
      }
      else if(connexion_mode=='gepi')
      {
        var valeur = tab_param[connexion_mode][connexion_ref];
        var tab_infos = valeur.split(']¤[');
        $('#gepi_saml_url').val( tab_infos[0] );
        $('#gepi_saml_rne').val( tab_infos[1] );
        $('#gepi_saml_certif').val( tab_infos[2] );
        $("#bouton_valider").prop('disabled',false);
        $('#gepi_options').show();
        $('#lien_direct').show();
        $('#lien_gepi').show();
      }
      else
      {
        $("#bouton_valider").prop('disabled',false);
      }
    }

    $("#connexion_mode_nom").change
    (
      function()
      {
        actualiser_formulaire();
      }
    );

    // Initialisation au chargement de la page
    actualiser_formulaire();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mode d'identification (normal, CAS...) & paramètres associés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_valider').click
    (
      function()
      {
        var connexion_mode_nom = $('#connexion_mode_nom option:selected').val();
        var tab_infos = connexion_mode_nom.split('~');
        var connexion_mode = tab_infos[0];
        var connexion_ref  = tab_infos[1];
        if(connexion_mode=='gepi')
        {
          // Le RNE n'étant pas obligatoire, et pas forcément un vrai RNE dans Gepi (pour les établ sans UAI, c'est un identifiant choisi...), on ne vérifie rien.
          // Pas de vérif particulière de l'empreinte du certificat non plus, ne sachant pas s'il peut y avoir plusieurs formats.
        }
        $("#bouton_valider").prop('disabled',true);
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_connexion_mode='+connexion_mode+'&f_connexion_ref='+connexion_ref+'&'+$("form").serialize(),
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("#bouton_valider").prop('disabled',false);
              $('#ajax_msg').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
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
