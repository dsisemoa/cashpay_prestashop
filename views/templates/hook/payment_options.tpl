{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<head>
    {block name='head'}
        {include file='_partials/head.tpl'}
    {/block}
    <link rel="stylesheet" type="text/css" href="{$moduleDir|addslashes}/views/css/ladda/ladda.min.css">
    <link rel="stylesheet" type="text/css" href="{$moduleDir|addslashes}/views/plugins/intl-input-tel/intlTelInput.css">

</head>

<body>
	  <header id="header">
        {block name='header'}
            {include file='_partials/header.tpl'}
        {/block}

    </header>


    <section id="wrapper">
        <div class="container">

            <section id="content" class="page-content card card-block">
            	<h4>4. Paiement</h4>
            	<br>
				<form name="payment_form" id="cashpay_payment_form" action="{$action_url}" method="post">

				  	<div class="cart-grid-body row">
				  		<div class="col-md-8">
					  		<p>
						    	Vous avez choisi Cashpay comme moyen de paiement.
						    </p>
						    <br>
						    <div class="row">
								<div class="col-lg-6 ">
									<p>Veuillez saisir votre numéro de téléphone</p>
								</div>

								<div class="col-lg-6 ">
									<div class="form-group"> 
					                    <input class="form-control" id="phone" name="phone" type="tel" required>
					                    <input type="hidden" name="tel" id="tel" value="" />

				                	</div> 
								</div>
								
							</div>
						    

				            <div class=" row">
				             	<div class="col-lg-12">
									<div class="text-center">
						            	<button class="btn btn-primary" data-style="expand-right" type="button" id="send">Continuer</button>
						            </div>
					            </div>
				            </div>
				        
				  		</div>
				  		<div class="col-md-4">
				  			<h5>Détails de la commande</h5>
				  			<dl>
				  			    <dd>Client : <b>{$client_fullname}</b></dd>
						        <dd>Montant total: <b>{$amount}</b></dd>
						    </dl>
				  			
				  		</div>
				    </div>
				    
		        
		        </form>
	      
	  
            </section>
        </div>
    </section>
    <!-- Footer starts -->
	
	<footer id="footer">
        {block name="footer"}
            {include file="_partials/footer.tpl"}
        {/block}
    </footer>
    <!-- Footer Ends -->
 
    {hook h='displayBeforeBodyClosingTag'}

    {include file="_partials/javascript.tpl" javascript=$javascript.bottom}
        <script type="text/javascript" src="{$moduleDir|addslashes}/views/css/ladda/spin.min.js"></script>

        <script type="text/javascript" src="{$moduleDir|addslashes}/views/css/ladda/ladda.min.js"></script>

        <script type="text/javascript" src="{$moduleDir|addslashes}/views/plugins/intl-input-tel/intlTelInput.min.js"></script>
		<script>
      		var intlphUtilsScriptsUrl = "{$moduleDir|addslashes}/views/plugins/intl-input-tel/utils.js";
  		</script>

        <script type="text/javascript">
        
            var input = document.querySelector("#phone");

	        var preferredCountries = ["tg", "bj", "ci", "sn"];
	        let phoneNumberOptions = {
		        separateDialCode: true,
                utilsScript: intlphUtilsScriptsUrl,
		        preferredCountries: preferredCountries
	    	}
            var phone = intlTelInput(input, phoneNumberOptions);
			$('#send').click(function() {
			    var value = phone.getNumber(); // Get international number
			    $('#tel').val(value);
			    $('#cashpay_payment_form').submit();
			});
        </script>
	
</body>

