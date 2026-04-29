<?php

include("include/sasFunction.php");

$cxmMerchantJson = file_get_contents($apiBaseUrl.'/show_invoice/'.$_GET['invoicekey']);

$cxmMerchantArr = json_decode($cxmMerchantJson, true);


// echo '<pre>';
// print_r($cxmMerchantArr);
// exit;


$cxm_merchants = array();

if(!empty($cxmMerchantArr['data'])){

    $cxm_merchants = $cxmMerchantArr['data'];

}else{

    $cxm_merchants = array(

        '1' => array(

            'merchant' => 'No Merchant',
            'live_login_id' => '0',
            'live_transaction_key' => '0',
            
         )
    );

}

$brandUrl_with_http = preg_replace('/^www\./', '', parse_url($cxm_merchants['brandurl']));

// $brandUrl_with_http = preg_replace('#^https?://#', '', $brandUrl);

// echo '<pre>';
// print_r($brandUrl_with_http);
// exit;

if ($cxm_merchants['status'] == 'paid' || $cxm_merchants['status'] == 'authorized') {

   header('Location: https://checkout.' . $brandUrl_with_http['host'] . '/invoice/?invoicekey=' . $cxm_merchants['invoice_key'] . '&clear=' . mt_rand());
   exit;
}


// if($cxm_merchants['status'] == 'paid'){

//       $base_url = $cxmMerchantArr['data']['brandurl']."checkout/invoice/index.php?status=paid&invoicekey=".$cxm_merchants['invoice_key'];
//       header('Location: ' . $base_url);
//       exit;

// }

?>

<!DOCTYPE html>

<html lang="en-US">

    <head>

      <meta charset="utf-8">

      <title><?php echo $cxm_merchants['brandName']?> Secure Payment Terminal</title>

      <meta name="viewport" content="width=device-width, initial-scale=1.0">

      <link rel="icon" href="<?php echo $cxm_merchants['brandlogo']?>" type="image/webp">

      
      <!-- Begin CSS -->

      <link href="assets/bootstrap.min.css" rel="stylesheet">

      <link href="assets/font-awesome.min.css" rel="stylesheet">

      <link href="assets/sweetalert.css" rel="stylesheet">

      <link href="assets/helpers.css" rel="stylesheet">

      <link href="assets/app.css" rel="stylesheet">

      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css"/>

      <!-- Begin JS -->

<script type="text/javascript">

var checkNotification = false;
const BrandID = '<?php echo $cxm_merchants['brand_key'] ?>';

</script>

      <script src="assets/jquery.min.js"></script>

      <script src="assets/bootstrap.min.js"></script>

      <script src="assets/bootstrap-datepicker.js"></script>

      <script src="assets/bootstrap-maxlength.js"></script>

      <script src="assets/sweetalert.min.js"></script>

      <script src="assets/jquery.form.min.js"></script>

      <script src="assets/jquery.jGet.js"></script>

      <script src="assets/jquery.validate.min.js"></script>

      <script src="assets/jquery.validate.additional-methods.min.js"></script>

      <script src="assets/app.js"></script>

      <link rel="stylesheet" type="text/css" href="invocie/style/toaster.css"/>

      <style>.card-type-image {
         background:transparent url('images/credit-cards.jpg') 0 0 no-repeat;}
      </style>

   </head>

   <body class="terminal-body">
         <ul class="design_notifications_toaster">
      </ul>
      <noscript>

         <div class="alert alert-danger mt20neg">

            <div class="container aligncenter">

               <strong>Oops!</strong> It looks like your browser doesn't have Javascript enabled.  Please enable Javascript to use this website.

            </div>

         </div>

       </noscript>

        <?php if($country == 'Pakistan'){?>

         <style>.swal-text{text-align:center;}</style>

         <script>swal("Alert!", "You are using non-US IP, please use the correct IP for payment", "error", {buttons: false, closeOnClickOutside: false, closeOnEsc: false});</script>

      <?php }?>

      <div class="container terminal-wrapper">

         <div class="page-header">

            <div class="row align-items-center">

               <div class="col-md-8">

                  <h2 class="colorprimary">

                     <img class="img-responsive" src="<?php echo $cxm_merchants['brandlogo']?>" width="250px;">

                     <small style="font-size: 13px; padding: 0 0 0 33px;"><?php echo $cxm_merchants['brandName']?> Secure Payment Terminal</small>

                  </h2>

               </div>

               <div class="col-md-4">

                  <h1 class="text-right text-uppercase <?php echo ($cxm_merchants['status'] == 'paid')? 'text-success' : 'text-danger';?>"><?php echo $cxm_merchants['status']?></h1>

               </div>

            </div>   

         </div>

         <?php if($cxm_merchants['status'] == 'paid'){?> 

         <div class="alert alert-success">

             <?php $date=date_create($cxm_merchants['updated_at']);?>

            <strong><i class="fa fa-check"></i> This invoice has already been paid!</strong><br>Payment for this invoice was received on <b><?php echo date_format($date,"j F, Y"); ?></b>.

         </div>

         <?php }?>

         <form method="POST" class="validate form-horizontal" action="receipt.php">

             <?php if($cxm_merchants['status'] == "due"){?>

            <input type="hidden" id="team_key" name="team_key" value="<?php echo $cxm_merchants['team_key']?>">

            <input type="hidden" id="brand_key" name="brand_key" class="enable-subscriptions" value="<?php echo $cxm_merchants['brand_key']?>">

            <input type="hidden" id="creatorid" name="creatorid" class="enable-subscriptions" value="<?php echo $cxm_merchants['creatorid']?>">

            <input type="hidden" id="agentid"  name="agentid" class="enable-subscriptions" value="<?php echo $cxm_merchants['agent_id']?>">

            <input type="hidden" id="clientid"  name="clientid" class="enable-subscriptions" value="<?php echo $cxm_merchants['clientid']?>">

            <input type="hidden" id="invoiceid"  name="invoiceid" class="enable-subscriptions" value="<?php echo $cxm_merchants['invoice_key']?>">

            <input type="hidden" id="projectid"  name="projectid" class="enable-subscriptions" value="<?php echo $cxm_merchants['project_id']?>">

            <input type="hidden" id="salesType"  name="salestype" class="enable-subscriptions" value="<?php echo $cxm_merchants['sales_type']?>">

            <input type="hidden" id="payment_gateway"  name="payment_gateway" class="enable-subscriptions" value="authorize">

            <input type="hidden" name="tkn" id="tkn" value="<?php echo $cxm_merchants['invoice_key']?>">

            

            <input type="hidden" name="brand_url" value="<?php echo $cxm_merchants['brandurl']?>">

            <input type="hidden" name="date_stamp" value="<?php echo date("Y-m-d h:i:s A"); ?>">

            <input type="hidden" name="brand_name" value="<?php echo $cxm_merchants['brandName']?>">

            <input type="hidden" name="merchant_name" value="<?php echo $cxm_merchants['merchant']['merchant']?>">

            <input type="hidden" name="smtp_host" value="<?php echo $cxm_merchants['smtp_host']?>">

            <input type="hidden" name="smtp_email" value="<?php echo $cxm_merchants['smtp_email']?>">

            <input type="hidden" name="smtp_password" value="<?php echo $cxm_merchants['smtp_password']?>">

            <input type="hidden" name="smtp_port" value="<?php echo $cxm_merchants['smtp_port']?>">

            <input type="hidden" name="source" value="New CRM">

            <input type="hidden" name="cxm_m_id" value="<?php echo cxmEncrypt($cxm_merchants['merchant']['id'], $sasPrivateKey); ?>">

            <input type="hidden" name="cxm_m_mode" value="<?php echo cxmEncrypt($cxm_merchants['merchant']['mode'], $sasPrivateKey); ?>">

            <input type="hidden" name="cxm_m_n" value="<?php echo cxmEncrypt($cxm_merchants['merchant']['merchant'], $sasPrivateKey); ?>">

            <input type="hidden" name="cxm_m_lid" value="<?php echo cxmEncrypt($cxm_merchants['merchant']['live_login_id'], $sasPrivateKey); ?>">

            <input type="hidden" name="cxm_m_key" value="<?php echo cxmEncrypt($cxm_merchants['merchant']['live_transaction_key'], $sasPrivateKey); ?>">

            <input type="hidden" id="card_type" name="card_type" value="">

            <?php }?>

            <input type="hidden" name="amount" value="<?php echo ($cxm_merchants['total_amount'] != 0)?$cxm_merchants['total_amount'] : $cxm_merchants['final_amount'] ?>">

            <div class="row">

               <div class="col-md-8 col-md-offset-2">

                  <h3 class="colorgray mb30">Payment Details</h3>

                  <div class="form-group">

                     <label class="col-md-3 control-label"><span class="colordanger">*</span>Amount</label>

                     <div class="col-md-9">

                        <div class="input-group">

                           <span class="input-group-addon"><?php echo $cxm_merchants['currency_symbol']?></span>

                           <input type="text" id="final_amount" name="final_amount" class="form-control" placeholder="0.00" value="<?php echo $cxm_merchants['final_amount']?>" readonly>

                        </div>

                     </div>

                  </div>

                  <?php if($cxm_merchants['tax_amount'] != 0){?>  

                  <div class="form-group">

                     <label class="col-md-3 control-label"><span class="colordanger">*</span><?php echo $cxm_merchants['tax_percentage']?>% Tax</label>

                     <div class="col-md-9">

                        <div class="input-group">

                           <span class="input-group-addon"><?php echo $cxm_merchants['currency_symbol']?></span>

                           <input type="text" id="tax" name="tax" class="form-control" placeholder="0.00" value="<?php echo $cxm_merchants['tax_amount']?>" readonly>

                        </div>

                     </div>

                  </div>

                  <div class="form-group">

                     <label class="col-md-3 control-label"><span class="colordanger">*</span>Net Amount</label>

                     <div class="col-md-9">

                        <div class="input-group">

                           <span class="input-group-addon"><?php echo $cxm_merchants['currency_symbol']?></span>

                           <input type="text" id="amount" name="amount" class="form-control" placeholder="0.00" data-rule-required="true" data-rule-number="true" value="<?php echo ($cxm_merchants['total_amount'])?$cxm_merchants['total_amount'] : $cxm_merchants['final_amount']?>" readonly>

                        </div>

                     </div>

                  </div>

                  <?php }?>

                  <div class="form-group">

                     <label class="col-md-3 control-label"><span class="colordanger">*</span>Description</label>

                     <div class="col-md-9">

                        <textarea  id="description" name="description" class="form-control xh55 xmaxlength" xmaxlength="120" placeholder="Description" rows="5" xdata-rule-required="true" readonly style="resize:none;"><?php echo $cxm_merchants['invoice_descriptione']?></textarea>

                     </div>

                  </div>

                  <hr class="visible-xs visible-sm">

                  <h3 class="colorgray mt40 mb30">Your Information</h3>

                  <div class="form-group">

                     <label class="control-label col-md-3"><span class="colordanger">*</span>Name</label>

                     <div class="col-md-9">

                        <input type="text" id="name" name="name" class="form-control" placeholder="Name" value="<?php echo $cxm_merchants['clientname']?>" data-rule-required="true">

                     </div>

                  </div>

                  <div class="form-group">

                     <label class="control-label col-md-3"><span class="colordanger">*</span>Email</label>

                     <div class="col-md-9">

                        <input type="text"  id="email" name="email" class="form-control" placeholder="Email" value="<?php echo $cxm_merchants['clientemail']?>" data-rule-required="true" data-rule-email="true">

                     </div>

                  </div>

                  <div class="form-group">

                     <label class="control-label col-md-3"><span class="colordanger">*</span>Phone</label>

                     <div class="col-md-9">

                        <input type="text"  id="email" name="phone" class="form-control" placeholder="Phone" value="<?php echo $cxm_merchants['clientphone']?>" data-rule-required="true">

                     </div>

                  </div>



               <!--<div class="col-md-6">    -->

                  <hr class="visible-xs visible-sm">

                  <h3 class="colorgray mt40 mb30">

                     Payment Method

                     <div class="floatright">

                     <img src="images/credit-cards.jpg" class="">

                     </div>

                  </h3>

                  <div class="creditcard-content">

                     <!--<div class="form-group">-->

                     <!--  <label class="control-label col-md-3"><span class="colordanger">*</span>Name on Card</label>-->

                     <!--  <div class="col-md-9">-->

                     <!--     <div class="input-group">-->

                     <!--        <input type="text" id="card_name" name="card_name" class="form-control" placeholder="Name on Card" value="" data-rule-required="true">-->

                     <!--        <span class="input-group-addon"><i class="fa fa-lock"></i></span>-->

                     <!--     </div> -->

                     <!--  </div>-->

                     <!--</div>-->

                     <!--<div class="form-group">-->

                     <!--  <label class="control-label col-md-3"><span class="colordanger">*</span>Card Number</label>-->

                     <!--  <div class="col-md-9">-->

                     <!--     <div class="input-group">-->

                     <!--        <input maxlength="16" type="text" id="card_number" name="card_number" class="form-control card-number" placeholder="Card Number" value="" data-rule-required="true" data-rule-creditcard="true">-->

                     <!--        <span class="input-group-addon"><i class="fa fa-lock"></i></span>-->

                     <!--     </div> -->

                     <!--     <div class="card-type-image none"></div>-->

                     <!--  </div>-->

                     <!--</div>-->

                     <!--<div class="form-group">-->

                     <!--  <label class="control-label col-md-3"><span class="colordanger">*</span>Expiration/CVC</label>-->

                     <!--  <div class="col-md-9">-->

                     <!--     <div class="row">-->

                     <!--        <div class="col-md-4 col-xs-4 pr5">-->

                     <!--           <select id="card_exp_month" name="card_exp_month" class="form-control" data-rule-required="true">-->

                     <!--              <option value="1" >01</option>-->

                     <!--              <option value="2" >02</option>-->

                     <!--              <option value="3" >03</option>-->

                     <!--              <option value="4" >04</option>-->

                     <!--              <option value="5" >05</option>-->

                     <!--              <option value="6" >06</option>-->

                     <!--              <option value="7" >07</option>-->

                     <!--              <option value="8" selected="selected">08</option>-->

                     <!--              <option value="9" >09</option>-->

                     <!--              <option value="10" >10</option>-->

                     <!--              <option value="11" >11</option>-->

                     <!--              <option value="12" >12</option>-->

                     <!--           </select>   -->

                     <!--        </div>-->

                     <!--        <div class="col-md-4 col-xs-4 pl5 pr5">-->

                     <!--           <select id="card_exp_year" name="card_exp_year" class="form-control" data-rule-required="true">-->

                     <!--              <option value="2022">2022</option>-->

                     <!--              <option value="2023">2023</option>-->

                     <!--              <option value="2024">2024</option>-->

                     <!--              <option value="2025">2025</option>-->

                     <!--              <option value="2026">2026</option>-->

                     <!--              <option value="2027">2027</option>-->

                     <!--              <option value="2028">2028</option>-->

                     <!--              <option value="2029">2029</option>-->

                     <!--              <option value="2030">2030</option>-->

                     <!--              <option value="2031">2031</option>-->

                     <!--              <option value="2032">2032</option>-->

                     <!--           </select>-->

                     <!--        </div>-->

                     <!--        <div class="col-md-4 col-xs-4 pl5">-->

                     <!--           <div class="input-group">-->

                     <!--              <input type="text" id="card_cvv"  name="card_cvv" name="cvc" class="form-control" placeholder="CVV" value="" data-rule-required="true">-->

                     <!--              <span class="input-group-addon"><i class="fa fa-lock"></i></span>-->

                     <!--           </div> -->

                     <!--        </div>-->

                     <!--     </div>-->

                     <!--  </div>-->

                     <!--</div>-->

                              <div class="panel-body">

                <!-- Paypal -->

                         <div >

                        

                         <br>

             <!-- <form  method="POST" action="{{route('payout')}}" id="order-place"> -->

             <form  method="POST" action="" id="order-place">

              <!-- @csrf -->

              <input type="hidden" name="recorder_token" value="<?=trim($_GET['invoicekey'])?>">

              <input type="hidden" name="payment_id" value="" />

              <input type="hidden" name="payer_id" value="" />

              <input type="hidden" name="payment_status" value="" />

              <input type="hidden" name="respon_data" value="" />                            

                        <div id="paypal-button-container"></div>

              </form>

                        <!--</div>-->

                        </div>

                  </div>

                  <!--<div class="row mt50">                   -->

                  <!--  <div class="col-md-12 alignright">-->

                  <!--     <div class="creditcard-content">-->

                  <!--        <?php if($cxm_merchants['status'] == 'due'){?>     -->

                  <!--        <button id="pay_button" type="submit" class="btn btn-lg btn-primary submit-button mb20">-->

                  <!--              <span class="total show">Total: <?php echo $cxm_merchants['currency_symbol']?><span><?php echo ($cxm_merchants['total_amount'] != 0 ) ?$cxm_merchants['total_amount'] : $cxm_merchants['final_amount'] ?></span>-->

                  <!--              <small></small></span>-->

                  <!--              <i class="fa fa-check"></i> Submit Payment-->

                  <!--        </button>-->

                  <!--        <?php }else{?>-->

                  <!--        <button class="btn btn-lg btn-primary submit-button mb20" disabled="">-->

                  <!--              <i class="fa fa-check"></i> Submit Payment-->

                  <!--        </button>-->

                  <!--        <?php }?>-->

                  <!--     </div>-->

                  <!--  </div>-->

                  <!--  <br>-->

                  <!--</div>-->

               </div>

            </div>

                           </div>

         </form>

         

         <br>

         <!-- <div class="alert alert-warning text-center" role="alert">On your bank statement the descriptor should be <b>Zedexsolutions LLC</b> as Merchant Name.</div> -->



      </div>

    </body>
    
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script src="https://www.paypalobjects.com/api/checkout.js"></script>

<script>

var API_URL = "https://crm.trivexlabs.com/api/" ;

if(window.location.origin == 'https://localhost')
{
   API_URL ="https://crm.trivexlabs.com/api/" ;
}



    $(document).ready(function(){

      // var getAmount = $("#getAmount").val();

      var getAmount = '<?php echo ($cxm_merchants['total_amount'] != 0 ) ?$cxm_merchants['total_amount'] : $cxm_merchants['final_amount'] ?>';

      var getcurrency = '<?=$cxm_merchants['cur_symbol']?>';

           // Create a container for the PayPal button

        var paypalContainer = document.getElementById('paypal-button-container');


        // Create a span element to display the payment amount dynamically

        var amountSpan = document.createElement('span');

        amountSpan.textContent = 'Pay <?=$cxm_merchants['currency_symbol']?> ' + getAmount + ' with PayPal';



        // Append the span element to the container

        paypalContainer.appendChild(amountSpan);

        paypal.Button.render({

            env: 'production', // sandbox | production

            style: {

            label: 'checkout',

            size: 'responsive', // small | medium | large | responsive

            shape: 'rect',  // pill | rect

            color: 'gold'    // gold | blue | silver | black

        },

            client: {

               sandbox: 'AQosDam3xY-0pqhBdIFsTAGWK32_hpJTRf4KLyuoskKV0GG8rYkK9b__JUD0Xp2o0zX9qVaOIliiK78P',
               production: 'AchIOtJeUZmo8T1543L3YukwBLt2ppO-FJ8mh4kHHSu-h7ALWiD8mSJWcWIM_dU_sTco9-Brx99dXcuK',

            },

            // Show the buyer a 'Pay Now' button in the checkout flow

            commit: true,

            // payment() is called when the button is clicked

            payment: function(data, actions) {

                // Make a call to the REST api to create the payment

                return actions.payment.create({

                    payment: {

                        transactions: [

                            {

                                amount: { total: getAmount, currency: getcurrency } 

                            }

                        ]

                    }

                });

            },

            // onAuthorize() is called when the buyer approves the payment

            onAuthorize: function(data, actions) {

                // Make a call to the REST api to execute the payment

                return actions.payment.execute().then(function() {

                     // window.alert('Payment Complete!');

                     // AdminToastr.success('Thank you! your payment has been made.','Payment Success');

                     console.log(data);

                     var EXECUTE_URL = 'https://crm.trivexlabs.com/api/crm-api-paypal-create';

                     <?php 
                     if ($_SERVER['REMOTE_ADDR'] == '::1')
                     {
                        ?>
                     
                     EXECUTE_URL = 'https://crm.trivexlabs.com/api/crm-api-paypal-create';
                     
                        <?php
                     }
                     ?>
                    

                     var params = {

                        payment_status:'Completed',
                        invoice_id:'<?=trim($_GET['invoicekey'])?>',
                        payment_id: data.paymentID,
                        payerID: data.payerID,
                        respon_data: data,

                     };

                    if(paypal.request.post(EXECUTE_URL, params)){

                     console.log('params respons from api...',params);

                        if(params.payment_status== 'Completed'){

                             setInterval(function(){ 

                              createToast('success','Payment has been done Successfully.');
                                window.location = './invoice/index.php?invoicekey='+params.invoice_id

                             }, 3000); 

                        } else {

                         createToast('error','Payment Failed.');

                        }

                    }

                }).catch(function (error) {

                         createToast('error','Payment Failed.');

                });

            }

        }, '#paypal-button-container');

});



       // Nofitication Start.
       setTimeout(function(){
         // console.log('5 secs')
         // $('head').append('<link rel="stylesheet" type="text/css" href="https://api.tgcrm.net/toaster.css">');
         $('head').append('<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">');
         // createToast('success','Thank you for filling out your information!');
         
      },500)
      
      const notifications = document.querySelector(".design_notifications_toaster");
      const toastDetails = {
         timer: 15000,
         success: {
             icon: 'fa-circle-check',
         },
         error: {
             icon: 'fa-circle-xmark',
         },
         warning: {
             icon: 'fa-triangle-exclamation',
         },
         info: {
             icon: 'fa-circle-info',
         }
      }
      const removeToast = (toast) => {
         toast.classList.add("hide");
         if(toast.timeoutId) clearTimeout(toast.timeoutId); // Clearing the timeout for the toast
         setTimeout(() => toast.remove(), 10000); // Removing the toast after 500ms
      }
      const createToast = (id,msg) => {
         const { icon, text } = toastDetails[id];
         const toast = document.createElement("li"); // Creating a new 'li' element for the toast
         toast.className = `toast ${id}`;
         toast.innerHTML = `<div class="column">
                              <i class="fa-solid ${icon}"></i>
                              <span>${msg}</span>
                           </div>
                           <i class="fa-solid fa-xmark" onclick="removeToast(this.parentElement)"></i>`;
         notifications.appendChild(toast); // Append the toast to the notification ul
         toast.timeoutId = setTimeout(() => removeToast(toast), toastDetails.timer);
      }



  /// Tracking api.
  setTimeout(function()
    {
        $.ajax({
            type:"POST",
            url: API_URL+"create-tracking-ip/"+BrandID,
            data:{
                url:window.location.href,
            },success:function(s){
                
                console.log('tracking success',s)
            }
        });
    },3000)
    /// Tracking api.
   </script>

</html>