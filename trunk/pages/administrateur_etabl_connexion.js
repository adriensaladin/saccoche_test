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

    $('#form_mode input').keyup
    (
      function(e)
      {
        if(e.which==13)  // touche entrée
        {
          $('#bouton_valider_mode').click();
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Alerter sur la nécessité de valider
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#form_mode select , #form_mode input").change
    (
      function()
      {
        $('#ajax_msg_mode').removeAttr("class").addClass("alerte").html("Penser à valider les modifications.");
        $('#table_action thead q').removeAttr("class").addClass("ajouter_non").attr("title","Validez d'abord le mode d'identification.");
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
      $('#cas_options , #gepi_options , #lien_direct , #lien_gepi , #info_inacheve , #info_hors_sesamath , #info_hors_actualite , #info_hors_ent , #info_heberg_acad , #info_conv_acad , #info_conv_etabl').hide();
      if(!IS_HEBERGEMENT_SESAMATH)
      {
        $('#info_hors_sesamath').show();
      }
      else if(!CONVENTION_ENT_REQUISE)
      {
        $('#info_hors_actualite').show();
      }
      // on récupère les infos
      var valeur = $('#connexion_mode_nom option:selected').val();
      var tab_infos = valeur.split('~');
      var connexion_mode = tab_infos[0];
      var connexion_ref  = tab_infos[1];
      if(connexion_mode=='cas')
      {
        var valeur = tab_param[connexion_mode][connexion_ref];
        var tab_infos = valeur.split(']¤[');
        var type_convention = tab_infos[0];
        var is_operationnel = tab_infos[1];
        $('#cas_serveur_host').val( tab_infos[2] );
        $('#cas_serveur_port').val( tab_infos[3] );
        $('#cas_serveur_root').val( tab_infos[4] );
        $('#cas_serveur_url_login').val(    tab_infos[5] );
        $('#cas_serveur_url_logout').val(   tab_infos[6] );
        $('#cas_serveur_url_validate').val( tab_infos[7] );
        if(IS_HEBERGEMENT_SESAMATH && CONVENTION_ENT_REQUISE)
        {
          $('#info_'+type_convention).show();
        }
        if(connexion_ref=='|perso')
        {
          $('#cas_options').show();
        }
        if(is_operationnel=='1')
        {
          $("#bouton_valider_mode").prop('disabled',false);
          $('#lien_direct').show();
        }
        else
        {
          $("#bouton_valider_mode").prop('disabled',true);
          $('#info_inacheve').show();
        }
      }
      else if(connexion_mode=='shibboleth')
      {
        if(IS_HEBERGEMENT_SESAMATH && CONVENTION_ENT_REQUISE)
        {
          $('#info_hors_ent').show();
        }
        var is_operationnel = tab_param[connexion_mode][connexion_ref];
        if(is_operationnel=='1')
        {
          $("#bouton_valider_mode").prop('disabled',false);
          $('#lien_direct').show();
        }
        else
        {
          $("#bouton_valider_mode").prop('disabled',true);
          $('#info_inacheve').show();
        }
      }
      else if(connexion_mode=='gepi')
      {
        if(IS_HEBERGEMENT_SESAMATH && CONVENTION_ENT_REQUISE)
        {
          $('#info_hors_ent').show();
        }
        var valeur = tab_param[connexion_mode][connexion_ref];
        var tab_infos = valeur.split(']¤[');
        $('#gepi_saml_url').val(    tab_infos[0] );
        $('#gepi_saml_rne').val(    tab_infos[1] );
        $('#gepi_saml_certif').val( tab_infos[2] );
        $("#bouton_valider_mode").prop('disabled',false);
        $('#gepi_options').show();
        $('#lien_direct').show();
        $('#lien_gepi').show();
      }
      else
      {
        if(IS_HEBERGEMENT_SESAMATH && CONVENTION_ENT_REQUISE)
        {
          $('#info_hors_ent').show();
        }
        $("#bouton_valider_mode").prop('disabled',false);
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

    $('#bouton_valider_mode').click
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
        $("#bouton_valider_mode").prop('disabled',true);
        $('#ajax_msg_mode').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+'enregistrer_mode_identification'+'&f_connexion_mode='+connexion_mode+'&f_connexion_ref='+connexion_ref+'&'+$("#form_mode").serialize(),
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("#bouton_valider_mode").prop('disabled',false);
              $('#ajax_msg_mode').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $("#bouton_valider_mode").prop('disabled',false);
              if(responseHTML!='ok')
              {
                $('#ajax_msg_mode').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_msg_mode').removeAttr("class").addClass("valide").html("Mode de connexion enregistré !");
                $('#table_action thead q').removeAttr("class").addClass("ajouter").attr("title","Ajouter une convention.");
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une convention : mise en place du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action q.ajouter').click
    (
      function()
      {
        var connexion_mode_texte = $('#connexion_mode_nom option:selected').text();
        $('#f_connexion_texte').val(connexion_mode_texte);
        $('#ajax_msg_ajout').removeAttr('class').html("");
        $('#form_ajout label[generated=true]').removeAttr('class').html("");
        $.fancybox( { 'href':'#form_ajout' , onStart:function(){$('#form_ajout').css("display","block");} , onClosed:function(){$('#form_ajout').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une convention : fermer le formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_annuler_ajout').click
    (
      function()
      {
        $.fancybox.close();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une convention : soumettre le formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_valider_ajout').click
    (
      function()
      {
        var connexion_mode_nom = $('#connexion_mode_nom option:selected').val();
        var tab_infos = connexion_mode_nom.split('~');
        var connexion_mode = tab_infos[0];
        var connexion_ref  = tab_infos[1];
        var f_annee = $('#f_annee option:selected').val();
        if(f_annee=='-1')
        {
          $('#ajax_msg_ajout').removeAttr("class").addClass("erreur").html("Période manquante !");
          return false;
        }
        $("#form_ajout button").prop('disabled',true);
        $('#ajax_msg_ajout').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+'ajouter_convention'+'&f_connexion_mode='+connexion_mode+'&f_connexion_ref='+connexion_ref+'&f_annee='+f_annee,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("#form_ajout button").prop('disabled',false);
              $('#ajax_msg_ajout').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $("#form_ajout button").prop('disabled',false);
              if(responseHTML.substring(0,2)!='<t')
              {
                $('#ajax_msg_ajout').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_msg_ajout').removeAttr("class").addClass("valide").html("Convention ajoutée !");
                $('#table_action tbody tr td[colspan=7]').parent().remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML ; IE8 bugue si on n'indique que [colspan]
                $('#table_action tbody').prepend(responseHTML);
                $('#table_action tbody tr:first td:last q.voir_archive').click();
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Imprimer les documents associés à une convention
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'click' ,
      'q.voir_archive' ,
      function()
      {
        var f_convention_id = $(this).parent().parent().attr('id').substring(3);
        $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+'imprimer_documents'+'&f_convention_id='+f_convention_id,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+'Échec de la connexion !'+'</label>' , {'centerOnScroll':true} );
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              var tab_response = responseHTML.split(']¤[');
              if(tab_response[0]!='ok')
              {
                $.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
              }
              else
              {
                $('#fichier_convention').attr("href",tab_response[1]);
                $('#fichier_facture'   ).attr("href",tab_response[2]);
                $.fancybox( { 'href':'#form_impression' , onStart:function(){$('#form_impression').css("display","block");} , onClosed:function(){$('#form_impression').css("display","none");} , 'centerOnScroll':true } );
              }
            }
          }
        );
      }
    );

  }
);
