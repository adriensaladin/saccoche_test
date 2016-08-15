/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

// jQuery !
$(document).ready
(
  function()
  {

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger le select f_eleve en ajax
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_eleve(groupe_id,groupe_type)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_eleves',
          data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_eleves_ordre=alpha'+'&f_statut=1'+'&f_multiple=1'+'&f_selection=1',
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_groupe').removeAttr('class').addClass('alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==true)
            {
              $('#ajax_msg_groupe').removeAttr('class').addClass('valide').html("Affichage actualisé !");
              $('#f_eleve').html(responseJSON['value']).parent().show();
            }
            else
            {
              $('#ajax_msg_groupe').removeAttr('class').addClass('alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }
    function changer_groupe()
    {
      $("#f_eleve").html('').parent().hide();
      var groupe_val = $("#f_groupe option:selected").val();
      if(groupe_val)
      {
        // type = $("#f_groupe option:selected").parent().attr('label');
        groupe_type = groupe_val.substring(0,1);
        groupe_id   = groupe_val.substring(1);
        $('#ajax_msg_groupe').removeAttr('class').addClass('loader').html("En cours&hellip;");
        maj_eleve(groupe_id,groupe_type);
      }
      else
      {
        $('#ajax_msg_groupe').removeAttr('class').html("");
      }
    }
    $("#f_groupe").change
    (
      function()
      {
        changer_groupe();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Réagir au changement dans le premier formulaire (choix principal)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#f_choix_principal").change
    (
      function()
      {
        // Masquer tout
        $('fieldset[id^=fieldset]').hide(0);
        $('#ajax_msg').removeAttr('class').html("");
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

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Exporter un fichier de validations
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#export_lpc , #export_sacoche').click
    (
      function()
      {
        var action = $(this).attr('id');
        // grouper le select multiple
        if( $("#f_eleve input:checked").length==0 )
        {
          $('#ajax_msg').removeAttr('class').addClass('erreur').html("Sélectionnez au moins un élève !");
          return false;
        }
        else
        {
          // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
          var f_eleve = new Array(); $("#f_eleve input:checked").each(function(){f_eleve.push($(this).val());});
        }
        // on envoie
        $('button.enabled').prop('disabled',true);
        $('#ajax_msg').removeAttr('class').addClass('loader').html("En cours&hellip;");
        $('#ajax_info').html("");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+action+'&f_eleve='+f_eleve,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button.enabled').prop('disabled',false);
              $('#import_lpc_disabled').prop('disabled',true);
              $('#ajax_msg').removeAttr('class').addClass('alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              $('button.enabled').prop('disabled',false);
              $('#import_lpc_disabled').prop('disabled',true);
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg').removeAttr('class').addClass('alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_msg').removeAttr('class').html('');
                $('#ajax_info').html(responseJSON['value']);
                initialiser_compteur();
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire #form_import
    // Upload d'un fichier (avec jquery.form.js)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_import = $('#form_import');

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_import =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg",
      error : retour_form_erreur_import,
      success : retour_form_valide_import
    };

    // Vérifications précédant l'envoi du formulaire, déclenchées au choix d'un fichier
    $('#f_import').change
    (
      function()
      {
        var file = this.files[0];
        if( typeof(file) == 'undefined' )
        {
          $('#ajax_msg').removeAttr('class').html('');
          return false;
        }
        else
        {
          var fichier_nom = file.name;
          var fichier_ext = fichier_nom.split('.').pop().toLowerCase();
          if( '.xml.zip.'.indexOf('.'+fichier_ext+'.') == -1 )
          {
            $('#ajax_msg').removeAttr('class').addClass('erreur').html('Le fichier "'+fichier_nom+'" n\'a pas une extension "xml" ou "zip".');
            return false;
          }
          else
          {
            $('button.enabled').prop('disabled',true);
            $('#ajax_msg').removeAttr('class').addClass('loader').html("En cours&hellip;");
            $('#ajax_retour').html("");
            formulaire_import.submit();
          }
        }
      }
    );

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_import.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_import);
        return false;
      }
    );

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_import(jqXHR, textStatus, errorThrown)
    {
      $('#f_import').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $('button.enabled').prop('disabled',false);
      $('#ajax_msg').removeAttr('class').addClass('alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_import(responseJSON)
    {
      $('#f_import').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $('button.enabled').prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg').removeAttr('class').addClass('alerte').html(responseJSON['value']);
      }
      else
      {
        initialiser_compteur();
        $('button.enabled').prop('disabled',false);
        $('#ajax_msg').removeAttr('class').html('');
        $('#ajax_info').html(responseJSON['value']);
      }
    }

    $('button.fichier_import').click
    (
      function()
      {
        $('#ajax_info').html('');
        $('#ajax_msg').removeAttr('class').html('');
        var objet = $(this).attr('id'); // import_sacoche | import_compatible
        $('#f_action').val(objet);
        $('#f_import').click();
      }
    );

  }
);
