
window.onscroll = function()

{

	if( window.XMLHttpRequest ) {

		if (document.documentElement.scrollTop > 221 || self.pageYOffset > 121) {

			$('ja-header').style.position = 'fixed';


			$('ja-header').style.top = '0';


            $$('#ja-header').addClass('st-menu')
            $$('#ja-top').addClass('top11')
            $$('#ja-mainnav').addClass('fixed')

		} else if (document.documentElement.scrollTop < 221 || self.pageYOffset < 121) {

            $$('#ja-header').removeClass('st-menu')
            $$('#ja-mainnav').removeClass('fixed')
            $$('#ja-top').removeClass('top11')




			$('ja-header').style.position = 'relative';


			$('ja-header').style.top = '0px';
			$('ja-mainnav').style.top = '-8px';




		}

	}

}
