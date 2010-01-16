/**
 * @version $Id: accueil.js 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 */

// jQuery !
$(document).ready
(
	function()
	{

		function animation()
		{
//			$('#appel_menu').animate({borderColor:'#FF0000'},1000).animate({borderColor:'#FF9933'},1000);
			$('#look_menu').animate({top:'31px'},500).animate({top:'21px'},500);
		}
		animation();
		$("body").everyTime
		('1s', function()
			{
				animation();
			}
		);

	}
);
