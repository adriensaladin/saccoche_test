/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
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
// Initialisation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var mode       = false;
    var memo_login = '';
    var td_resp    = false;
    var listing_id = new Array();
    var f_action   = '';

    // tri du tableau (avec jquery.tablesorter.js).
    $('#table_action').tablesorter({ headers:{0:{sorter:false},10:{sorter:false},12:{sorter:'date_fr'},13:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[6,0],[7,0],[8,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Recharger la page en restreignant l'affichage en fonction des choix préalables
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function reload()
    {
    }

    $('#form_prechoix select').change
    (
      function()
      {
        $('#form_prechoix').submit();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Tout cocher ou tout décocher
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'click',
      'q.cocher_tout , q.cocher_rien',
      function()
      {
        var etat = ( $(this).attr('class').substring(7) == 'tout' ) ? true : false ;
        $('#table_action td.nu input[type=checkbox]').prop('checked',etat);
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le checkbox pour choisir ou non un login
// Clic sur le checkbox pour choisir ou non un mot de passe
// Clic sur le checkbox pour choisir ou non une date de sortie
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#box_login , #box_password , #box_date').click
    (
      function()
      {
        if($(this).is(':checked'))
        {
          $(this).next().show(0).next().hide(0);
        }
        else
        {
          $(this).next().hide(0).next().show(0);
        }
      }
    );

    $('#f_profil').change
    (
      function()
      {
        if(mode=='ajouter')
        {
          $('#box_login').next().html("automatique (modèle "+tab_login_modele[$('#f_profil option:selected').val()]+")");
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_gestion( mode , id , id_ent , id_gepi , sconet_id , reference , profil , nom , prenom , login , courriel , date_fr , check )
    {
      $('#f_action').val(mode);
      $('#f_check').val(check);
      $('#f_id').val(id);
      $('#f_id_ent').val(id_ent);
      $('#f_id_gepi').val(id_gepi);
      $('#f_sconet_id').val(sconet_id);
      $('#f_reference').val(reference);
      $('#f_profil option[value='+profil+']').prop('selected',true);
      $('#f_nom').val(nom);
      $('#f_prenom').val(prenom);
      $('#f_courriel').val(courriel);
      // login
      memo_login = login;
      var texte_box  = (mode=='modifier') ? "inchangé" : "automatique (modèle "+tab_login_modele[profil]+")" ;
      $('#f_login').val(login).parent().css('display','none'); // plutôt que .hide(0) car suite au passage vers jQuery 1.11.0 un hide() sur un élément déjà caché provoque ici sa réapparition...
      $('#box_login').prop('checked',true).next().show(0).html(texte_box);
      // mot de passe
      var texte_box  = (mode=='modifier') ? "inchangé" : "aléatoire" ;
      $('#f_password').val('').parent().css('display','none'); // plutôt que .hide(0) car suite au passage vers jQuery 1.11.0 un hide() sur un élément déjà caché provoque ici sa réapparition...
      $('#box_password').prop('checked',true).next().show(0).html(texte_box);
      if(date_fr=='-')
      {
        $('#box_date').prop('checked',true).next().show(0);
        $('#f_sortie_date').val(input_date).parent().css('display','none'); // plutôt que .hide(0) car suite au passage vers jQuery 1.11.0 un hide() sur un élément déjà caché provoque ici sa réapparition...
      }
      else
      {
        $('#box_date').prop('checked',false).next().css('display','none'); // plutôt que .hide(0) car suite au passage vers jQuery 1.11.0 un hide() sur un élément déjà caché provoque ici sa réapparition...
        $('#f_sortie_date').val(date_fr).parent().show(0);
      }
      // pour finir
      $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " un utilisateur");
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
      if(mode=='ajouter') { $('#f_nom').focus(); }
    }

    /**
     * Ajouter un parent : mise en place du formulaire
     * @return void
     */
    var ajouter = function()
    {
      mode = $(this).attr('class');
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*id*/ , '' /*id_ent*/ , '' /*id_gepi*/ , '' /*sconet_id*/ , '' /*reference*/ , 'ENS' /*profil*/ , '' /*nom*/ , '' /*prenom*/ , '' /*login*/ , '' /*couriel*/ , '-' /*date_fr*/ , '' /*check*/ );
    };

    /**
     * Modifier un parent : mise en place du formulaire
     * @return void
     */
    var modifier = function()
    {
      mode = $(this).attr('class');
      var objet_tr   = $(this).parent().parent();
      var objet_tds  = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id         = objet_tr.attr('id').substring(3);
      var check      = Number(objet_tds.eq(0).children('input').is(':checked'));
          td_resp    = objet_tds.eq( 1);
      var id_ent     = objet_tds.eq( 2).html();
      var id_gepi    = objet_tds.eq( 3).html();
      var sconet_id  = objet_tds.eq( 4).html();
      var reference  = objet_tds.eq( 5).html();
      var profil     = objet_tds.eq( 6).html();
      var nom        = objet_tds.eq( 7).html();
      var prenom     = objet_tds.eq( 8).html();
      var login      = objet_tds.eq( 9).html();
      var courriel   = objet_tds.eq(11).html();
      var date_fr    = objet_tds.eq(12).html();
      // Retirer une éventuelle balise image présente dans profil
      position_image = profil.indexOf('<');
      if (position_image!=-1)
      {
        profil = profil.substring(0,position_image-1);
      }
      // Retirer une éventuelle balise image présente dans login
      position_image = login.indexOf('<');
      if (position_image!=-1)
      {
        login = login.substring(0,position_image-1);
      }
      // Afficher le formulaire
      afficher_form_gestion( mode , id , unescapeHtml(id_ent) , unescapeHtml(id_gepi) , sconet_id , unescapeHtml(reference) , profil , unescapeHtml(nom) , unescapeHtml(prenom) , unescapeHtml(login) , unescapeHtml(courriel) , date_fr , check );
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
     * Intercepter la touche entrée ou escape pour valider ou annuler les modifications
     * @return void
     */
    function intercepter(e)
    {
      if(mode)
      {
        if(e.which==13)  // touche entrée
        {
          $('#bouton_valider').click();
        }
        else if(e.which==27)  // touche escape
        {
          $('#bouton_annuler').click();
        }
      }
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Appel des fonctions en fonction des événements ; live est utilisé pour prendre en compte les nouveaux éléments créés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on( 'click' , 'q.ajouter'       , ajouter );
    $('#table_action').on( 'click' , 'q.modifier'      , modifier );

    $('#form_gestion').on( 'click' , '#bouton_annuler' , annuler );
    $('#form_gestion').on( 'click' , '#bouton_valider' , function(){formulaire.submit();} );
    $('#form_gestion').on( 'keyup' , 'input,select'    , function(e){intercepter(e);} );

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
          f_id_ent      : { required:false , maxlength:63 },
          f_id_gepi     : { required:false , maxlength:63 },
          f_sconet_id   : { required:false , digits:true , max:16777215 },
          f_reference   : { required:false , maxlength:11 },
          f_profil      : { required:true },
          f_nom         : { required:true , maxlength:25 },
          f_prenom      : { required:true , maxlength:25 },
          f_login       : { required:function(){return !$('#box_login').is(':checked');} , maxlength:20 },
          f_password    : { required:function(){return !$('#box_password').is(':checked');} , minlength:function(){return tab_mdp_longueur_mini[$('#f_profil option:selected').val()];} , maxlength:20 },
          f_courriel    : { required:false , email:true , maxlength:63 },
          f_sortie_date : { required:function(){return !$('#box_date').is(':checked');} , dateITA:true }
        },
        messages :
        {
          f_id_ent      : { maxlength:"identifiant ENT de 63 caractères maximum" },
          f_id_gepi     : { maxlength:"identifiant Gepi de 63 caractères maximum" },
          f_sconet_id   : { digits:"Id Sconet : nombre entier inférieur à 2^24" },
          f_reference   : { maxlength:"référence de 11 caractères maximum" },
          f_nom         : { required:"nom manquant"    , maxlength:"25 caractères maximum" },
          f_prenom      : { required:"prénom manquant" , maxlength:"25 caractères maximum" },
          f_login       : { required:"login manquant"  , maxlength:"20 caractères maximum" },
          f_password    : { required:"mot de passe manquant" , minlength:function(){return tab_mdp_longueur_mini[$('#f_profil option:selected').val()]+" caractères minimum pour ce profil";} , maxlength:"20 caractères maximum" },
          f_courriel    : { email:"adresse invalide", maxlength:"63 caractères maximum" },
          f_sortie_date : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.attr("id")=='f_sortie_date') { element.next().after(error); }
          else {element.after(error);}
        }
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
      beforeSerialize : action_form_avant_serialize,
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

    // Fonction précédent le traitement du formulaire (avec jquery.form.js)
    function action_form_avant_serialize(jqForm, options)
    {
      if($('#box_login').is(':checked'))
      {
        $('#f_login').val(memo_login); // Pas de risque d'enregistrement d'un mauvais login, mais d'un retour trompeur à afficher si login modifié puis case recochée.
      }
    }

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
            $('#table_action tbody tr td[colspan=14]').parent().remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML ; IE8 bugue si on n'indique que [colspan]
            $('#table_action tbody').prepend(responseHTML);
            break;
          case 'modifier':
            $('#temp_td').html(td_resp); // Pour ne pas perdre l'objet avec l'infobulle, on est obligé de le copier ailleurs avant le html qui suit.
            $('#id_'+$('#f_id').val()).addClass("new").html(responseHTML).children('td:first-child').after(td_resp);
            break;
        }
        tableau_maj();
        $.fancybox.close();
        mode = false;
      }
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un bouton pour effectuer une action sur les utilisateurs cochés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var prompt_etapes = {
      etape_1: {
        title   : 'Demande de confirmation',
        html    : "Attention : les informations associées aux comptes seront perdues !<br />Souhaitez-vous vraiment supprimer les comptes sélectionnés ?",
        buttons : {
          "Non, c'est une erreur !" : false ,
          "Oui, je confirme !" : true
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            envoyer_action_confirmee(f_action,listing_id);
          }
        }
      }
    };

    $('#zone_actions button').click
    (
      function()
      {
        // Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
        listing_id = [];
        $("input[name=f_ids]:checked").each(function(){listing_id.push($(this).val());});
        if(!listing_id.length)
        {
          $('#ajax_msg_actions').removeAttr("class").addClass("erreur").html("Aucun utilisateur coché !");
          return false;
        }
        // On demande confirmation pour la suppression
        f_action = $(this).attr('id');
        if(f_action=='supprimer')
        {
          $('#ajax_msg_actions').removeAttr("class").html("&nbsp;");
          $.prompt(prompt_etapes);
        }
        else
        {
          envoyer_action_confirmee(f_action,listing_id);
        }
        return false;
      }
    );

    function envoyer_action_confirmee(f_action,listing_id)
    {
      $('#ajax_msg_actions').removeAttr("class").addClass("loader").html("En cours&hellip;");
      $('#zone_actions button').prop('disabled',true);
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action='+f_action+'&f_listing_id='+listing_id,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_actions').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
            $('#zone_actions button').prop('disabled',false);
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            tab_response = responseHTML.split(',');
            if(tab_response[0]!='ok')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#ajax_msg_actions').removeAttr("class").addClass("alerte").html(responseHTML);
            }
            else
            {
              $('#ajax_msg_actions').removeAttr("class").addClass("valide").html("Demande réalisée.");
              for ( i=1 ; i<tab_response.length ; i++ )
              {
                switch (f_action)
                {
                  case 'retirer':
                    $('#id_'+tab_response[i]).children("td:last").prev().html(input_date);
                    break;
                  case 'reintegrer':
                    $('#id_'+tab_response[i]).children("td:last").prev().html('-');
                    break;
                  case 'supprimer':
                    $('#id_'+tab_response[i]).remove();
                    break;
                }
              }
              tableau_maj();
            }
            $('#zone_actions button').prop('disabled',false);
          }
        }
      );
    }

  }
);
