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

    var mode = false;

    // tri du tableau (avec jquery.tablesorter.js).
    var sorting = [[1,1],[0,0]];
    $('table.form').tablesorter({ headers:{2:{sorter:false},3:{sorter:false},4:{sorter:false}} });
    function trier_tableau()
    {
      if($('table.form tbody tr td').length>1)
      {
        $('table.form').trigger('update');
        $('table.form').trigger('sorton',[sorting]);
      }
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_gestion( mode , id , date_debut_fr , date_fin_fr , destinataires_nombre , destinataires_liste , message_info , message_contenu )
    {
      $('#f_action').val(mode);
      $('#f_id').val(id);
      $('#f_debut_date').val(date_debut_fr);
      $('#f_fin_date').val(date_fin_fr);
      $('#f_destinataires_nombre').val(destinataires_nombre);
      $('#f_destinataires_liste').val(destinataires_liste);
      $('#f_message_info').val(message_info);
      $('#f_message_contenu').val(message_contenu);
      // pour finir
      $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " un message d'accueil");
      if(mode!='supprimer')
      {
        $('#gestion_edit').show(0);
        $('#gestion_delete').hide(0);
      }
      else
      {
        $('#gestion_delete_identite').html(message_info);
        $('#gestion_edit').hide(0);
        $('#gestion_delete').show(0);
      }
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
    }

    /**
     * Ajouter un message : mise en place du formulaire
     * @return void
     */
    var ajouter = function()
    {
      mode = $(this).attr('class');
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*id*/ , input_date /*date_debut_fr*/ , input_date /*date_fin_fr*/ , 'aucun' /*destinataires_nombre*/ , '' /*destinataires_liste*/ , 'aucun' /*message_info*/ , '' /*message_contenu*/ );
    };

    /**
     * Modifier un message : mise en place du formulaire
     * @return void
     */
    var modifier = function()
    {
      mode = $(this).attr('class');
      var objet_tr             = $(this).parent().parent();
      var objet_tds            = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id                   = objet_tr.attr('id').substring(3);
      var debut_date           = objet_tds.eq(0).html();
      var fin_date             = objet_tds.eq(1).html();
      var destinataires_nombre = objet_tds.eq(2).html();
      var message_info         = objet_tds.eq(3).text();
      // enlever les date mysql cachées
      var debut_date_fr        = debut_date.substring(17,debut_date.length);
      var fin_date_fr          = fin_date.substring(17,fin_date.length);
      // liste des destinataires et contenu du message
      var destinataires_liste  = tab_destinataires[id];
      var message_contenu      = tab_msg_contenus[id];
      // Afficher le formulaire
      afficher_form_gestion( mode , id , debut_date_fr , fin_date_fr , destinataires_nombre , destinataires_liste , unescapeHtml(message_info) , unescapeHtml(message_contenu) );
    };

    /**
     * Supprimer un message : mise en place du formulaire
     * @return void
     */
    var supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tr     = $(this).parent().parent();
      var objet_tds    = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id           = objet_tr.attr('id').substring(3);
      var message_info = objet_tds.eq(3).text();
      // Afficher le formulaire
      afficher_form_gestion( mode , id , '' /*date_debut_fr*/ , '' /*date_fin_fr*/ , '' /*destinataires_nombre*/ , '' /*destinataires_liste*/ , unescapeHtml(message_info) , '' /*message_contenu*/ );
    };

    /**
     * Annuler une action
     * @return void
     */
    var annuler = function()
    {
      $.fancybox.close();
      mode = false;
    };

    /**
     * Choisir les destinataires associés à un message : mise en place du formulaire
     * @return void
     */
    var choisir_destinataires = function()
    {
      // Ne pas changer ici la valeur de "mode" (qui est à "ajouter" ou "modifier").
      var destinataires_liste = $("#f_destinataires_liste").val();
      if(destinataires_liste=='')
      {
        $('#select_destinataires').html();
        $('#retirer_destinataires').prop('disabled',true);
        $('#valider_destinataires').prop('disabled',true);
      }
      else
      {
        $.fancybox( '<label class="loader">En cours&hellip;</label>' , {'centerOnScroll':true} );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=afficher_destinataires'+'&f_ids='+destinataires_liste,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">Échec de la connexion ! Veuillez recommencer.</label>' , {'centerOnScroll':true} );
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              if( (responseHTML.substring(0,7)!='<option') && (responseHTML!='') )
              {
                $.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
                return false;
              }
              else
              {
                $('#select_destinataires').html(responseHTML);
                var etat_disabled = (responseHTML!='') ? false : true ;
                $('#retirer_destinataires').prop('disabled',etat_disabled);
                $('#valider_destinataires').prop('disabled',etat_disabled);
              }
            }
          }
        );
      }
      // Afficher la zone
      $.fancybox( { 'href':'#form_destinataires' , onStart:function(){$('#form_destinataires').css("display","block");} , onClosed:function(){$('#form_destinataires').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
    };

    /**
     * Choisir le contenu d'un message : mise en place du formulaire
     * @return void
     */
    var editer_contenu_message = function()
    {
      // Ne pas changer ici la valeur de "mode" (qui est à "ajouter" ou "modifier").
      var message_contenu = $("#f_message_contenu").val();
      // Afficher la zone
      $.fancybox( { 'href':'#form_message' , onStart:function(){$('#form_message').css("display","block");} , onClosed:function(){$('#form_message').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
      $('#f_message').focus().val(unescapeHtml(message_contenu));
      afficher_textarea_reste( $('#f_message') , 999 );
    };

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Appel des fonctions en fonction des événements ; live est utilisé pour prendre en compte les nouveaux éléments créés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('q.ajouter').click( ajouter );
    $('q.modifier').live(  'click' , modifier );
    $('q.supprimer').live( 'click' , supprimer );
    $('#bouton_annuler').click( annuler );
    $('#bouton_valider').click( function(){formulaire.submit();} );

    $('q.choisir_eleve').live( 'click' , choisir_destinataires );
    $('q.texte_editer').live(  'click' , editer_contenu_message );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Indiquer le nombre de caractères restant autorisés dans le textarea
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_message').keyup
    (
      function()
      {
        afficher_textarea_reste( $(this) , 999 );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour le formulaire avec la liste des utilisateurs pour un regroupement et un profil donnés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_affichage()
    {
      // On récupère le profil
      var profil = $("#f_profil option:selected").val();
      if(profil=='')
      {
        $('#ajax_msg_destinataires').removeAttr("class").html("&nbsp;");
        $('#f_user').hide();
        $('#ajouter_destinataires').prop('disabled',true);
        return false
      }
      // On récupère le regroupement
      var groupe_val = $("#f_groupe").val();
      if(!groupe_val)
      {
        $('#ajax_msg_destinataires').removeAttr("class").html("&nbsp;");
        $('#f_user').hide();
        $('#ajouter_destinataires').prop('disabled',true);
        return false
      }
      // Pour un directeur ou un administrateur, groupe_val est de la forme d3 / n2 / c51 / g44
      if(isNaN(parseInt(groupe_val,10)))
      {
        groupe_type = groupe_val.substring(0,1);
        groupe_id   = groupe_val.substring(1);
      }
      // Pour un professeur, groupe_val est un entier, et il faut récupérer la 1ère lettre du label parent
      else
      {
        groupe_type = $("#f_groupe option:selected").parent().attr('label').substring(0,1).toLowerCase();
        groupe_id   = groupe_val;
      }
      $('#ajax_msg_destinataires').removeAttr("class").addClass("loader").html("En cours&hellip;");
      $('#bilan tbody').html('');
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=afficher_users'+'&f_profil='+profil+'&f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_destinataires').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            if( (responseHTML.substring(0,7)!='<option') && (responseHTML!='') )
            {
              $('#ajax_msg_destinataires').removeAttr("class").addClass("alerte").html(responseHTML);
              $('#f_user').hide();
              $('#ajouter_destinataires').prop('disabled',true);
            }
            else
            {
              $('#ajax_msg_destinataires').removeAttr("class").html("&nbsp;");
              $('#f_user').html(responseHTML).show();
              $('#ajouter_destinataires').prop('disabled',false);
            }
          }
        }
      );
    }

    $("#f_profil , #f_groupe").change
    (
      function()
      {
        maj_affichage();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour ajouter des destinataires
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#ajouter_destinataires').click
    (
      function()
      {
        $('#f_user option:selected').each
        (
          function()
          {
            var destinataire_id = $(this).val();
            var destinataire_nom = $(this).text();
            if( ! $('#select_destinataires option[value='+destinataire_id+']').length )
            {
              $('#select_destinataires').append('<option value="'+destinataire_id+'" selected>'+destinataire_nom+'</option>');
            }
          }
        );
        var etat_disabled = ($('#select_destinataires option').length) ? false : true ;
        $('#retirer_destinataires').prop('disabled',etat_disabled);
        $('#valider_destinataires').prop('disabled',etat_disabled);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour retirer des destinataires
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#retirer_destinataires').click
    (
      function()
      {
        $('#select_destinataires option:selected').each
        (
          function()
          {
            $(this).remove();
          }
        );
        var etat_disabled = ($('#select_destinataires option').length) ? false : true ;
        $('#retirer_destinataires').prop('disabled',etat_disabled);
        $('#valider_destinataires').prop('disabled',etat_disabled);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour valider le choix des destinataires associés à un message
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_destinataires').click
    (
      function()
      {
        var liste = '';
        var nombre = 0;
        $('#select_destinataires option').each
        (
          function()
          {
            var id = $(this).val();
            if(id)
            {
              liste += $(this).val()+'_';
              nombre++;
            }
          }
        );
        var destinataires_liste  = liste.substring(0,liste.length-1);
        var destinataires_nombre = (nombre==0) ? 'aucun' : ( (nombre>1) ? nombre+' destinataires' : nombre+' destinataire' ) ;
        $('#f_destinataires_liste').val(destinataires_liste);
        $('#f_destinataires_nombre').val(destinataires_nombre);
        $('#annuler_destinataires').click();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour valider le contenu d'un message
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_message').click
    (
      function()
      {
        var message_contenu = $("#f_message").val();
        $('#f_message_info').val(message_contenu.substring(0,50));
        $('#f_message_contenu').val(message_contenu);
        $('#annuler_message').click();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour fermer le cadre des destinataires associés à un message (annuler / retour)
    // Clic sur le bouton pour fermer le cadre de rédaction du contenu d'un message (annuler / retour)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#annuler_destinataires , #annuler_message').click
    (
      function()
      {
        $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
        return(false);
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $('#form_gestion');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_debut_date           : { required:true , dateITA:true },
          f_fin_date             : { required:true , dateITA:true },
          f_destinataires_nombre : { accept:'destinataire|destinataires' },
          f_message_info         : { minlength:10 } // On ne peut pas contrôler la longueur de f_message_contenu car il n'y a pas de vérifications sur un champ caché.
        },
        messages :
        {
          f_debut_date           : { required:"date manquante" , dateITA:"date JJ/MM/AAAA incorrecte" },
          f_fin_date             : { required:"date manquante" , dateITA:"date JJ/MM/AAAA incorrecte" },
          f_destinataires_nombre : { accept:"destinataire(s) manquant(s)" },
          f_message_info         : { minlength:"contenu manquant / insuffisant" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { element.next().after(error); }
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : "html",
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_gestion",
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
      $('#ajax_msg_gestion').removeAttr("class").html("&nbsp;");
      var readytogo = validation.form();
      if(readytogo)
      {
        please_wait = true;
        $('#form_gestion button').prop('disabled',true);
        $('#ajax_msg_gestion').removeAttr("class").addClass("loader").html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      please_wait = false;
      $('#form_gestion button').prop('disabled',false);
      $('#ajax_msg_gestion').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseHTML)
    {
      initialiser_compteur();
      please_wait = false;
      $('#form_gestion button').prop('disabled',false);
      if(responseHTML.substring(0,2)!='<t')
      {
        $('#ajax_msg_gestion').removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        $('#ajax_msg_gestion').removeAttr("class").addClass("valide").html("Demande réalisée !");
        switch (mode)
        {
          case 'ajouter':
            $('table.form tbody tr td[colspan=5]').parent().remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML ; IE8 bugue si on n'indique que [colspan]
            var position_script = responseHTML.lastIndexOf('<SCRIPT>');
            var new_tr = responseHTML.substring(0,position_script);
            $('table.form tbody').prepend(new_tr);
            eval( responseHTML.substring(position_script+8) );
            break;
          case 'modifier':
            var position_script = responseHTML.lastIndexOf('<SCRIPT>');
            var new_tds = responseHTML.substring(0,position_script);
            $('#id_'+$('#f_id').val()).addClass("new").html(new_tds);
            eval( responseHTML.substring(position_script+8) );
            break;
          case 'supprimer':
            $('#id_'+$('#f_id').val()).remove();
            break;
        }
        $.fancybox.close();
        mode = false;
        infobulle();
      }
    }

    // Retirer l'option vide (laissée pour la conformité...)
    $('#select_destinataires').html('');

  }
);
