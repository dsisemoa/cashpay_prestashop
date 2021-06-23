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
    <style>
        .detail-payment{
            padding-left: 20px;
            line-height: 28px;
        }
    </style>
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
                {include file='_partials/breadcrumb.tpl'}
                
                <div class="row">
                    
	  		    	<div class="col-md-8">
                       
	  		    	{if $isCancel == 1}
                        <h2><i class="material-icons rtl-no-flip error" style="color: red;">&#x274C;</i> Echec paiement par Cashpay </h2>
                        <br>
	  		    		<p>
					    	Votre paiement n'a pas abouti!
					    </p>
					{else}
                        <h2><i class="material-icons rtl-no-flip done" style="color: success;"></i> Votre commande est enregistrée</h2>
                        <br>
                        <div class="detail-payment">
					        <p>
					    	    Félicitations! Votre paiement est en attente de vérification et votre commande a été enregistrée. Vous recevrez un mail de confirmation dès que le traitement aura été effectué.
					        </p>
					    </div>
					{/if}
                        <div class="detail-payment">
                            <p>Date du paiement: <b>{$date_paiement}</b><br>
                            Etat: <b>{$state}</b></p>
                        </div>
	  		    	</div>
	  		    	<div class="col-md-4">
                            <br>
                            <h5>Détails de la commande</h5>
                            <hr>
                            <dl>
                            <p>Client : <b>{$client}</b></p>
                            <p>Référence commande : <b> {$order_ref} </b></p>
                            <p>Montant Total : <b>{$amount}</b> </p>
                            </dl>
				  			
	  		    	</div>

	  		    </div>
                <div class="table-responsive-row clearfix">                            
                    <a class="btn btn-primary" href="{$link->getPageLink('history', true)}"> Revenir à mes commandes</a>
                </div>
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
    {block name='javascript_bottom'}
        {include file="_partials/javascript.tpl" javascript=$javascript.bottom}
    {/block}
    {hook h='displayBeforeBodyClosingTag'}

</body>


