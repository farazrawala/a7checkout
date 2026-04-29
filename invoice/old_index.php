<?php
function cxmEncrypt($string, $key)
{
    $result = '';
    for ($i = 0;$i < strlen($string);$i++)
    {
        $char = substr($string, $i, 1);
        $keychar = substr($key, ($i % strlen($key)) - 1, 1);
        $char = chr(ord($char) + ord($keychar));
        $result .= $char;
    }

    return base64_encode($result);
}

function cxmDecrypt($string, $key)
{
    $result = '';
    $string = base64_decode($string);

    for ($i = 0;$i < strlen($string);$i++)
    {
        $char = substr($string, $i, 1);
        $keychar = substr($key, ($i % strlen($key)) - 1, 1);
        $char = chr(ord($char) - ord($keychar));
        $result .= $char;
    }

    return $result;
}

$sasPrivateKey = "sas";

if ($_SERVER['REMOTE_ADDR'] == '::1') 
   $apiBaseUrl = "https://development.trivexlabs.com/api";
else 
   $apiBaseUrl = "https://crm.trivexlabs.com/api";


$cxmMerchantJson = file_get_contents($apiBaseUrl . '/show_invoice/' . $_GET['invoicekey']);

$cxmMerchantArr = json_decode($cxmMerchantJson, true);
$cxm_merchants = array();
if (!empty($cxmMerchantArr['data']))
{
    $cxm_merchants = $cxmMerchantArr['data'];
    
}
else
{
    $cxm_merchants = array(
        '1' => array(
            'merchant' => 'No Merchant',
            'live_login_id' => '0',
            'live_transaction_key' => '0',
        )
    );
}

// echo '<pre>';
// print_r($cxmMerchantArr);
// // echo $cxm_merchants['brandurl'].'checkout/invoice/index.php?invoicekey='.$cxm_merchants['invoice_key'];
// exit;


if($cxm_merchants['status'] == 'paid'){


   header('Location: '.$cxm_merchants['brandurl'].'checkout/invoice/index.php?invoicekey='.$cxm_merchants['invoice_key']);


}



$curl = curl_init();
if ($_SERVER['REMOTE_ADDR'] == '::1')
{
    curl_setopt($curl, CURLOPT_URL, "https://ipinfo.io/203.135.30.178/json?token=12b59c8b5bf82e");
}
else
{
    curl_setopt($curl, CURLOPT_URL, "https://ipinfo.io/" . $_SERVER['REMOTE_ADDR'] . "/json?token=12b59c8b5bf82e");
}
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$ipResponse = curl_exec($curl);
$ipResponse = (array)json_decode($ipResponse);
$ipResponse['state'] = $ipResponse['region'];




?>
<!DOCTYPE html>
<html lang="en-US">
   <head>
      <meta charset="utf-8">
      <title><?php echo $cxm_merchants['brandName'] ?> Secure Payment Terminal</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="icon" href="<?php echo $cxm_merchants['brandlogo'] ?>" type="image/webp">
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <!-- Begin CSS -->
      <link href="assets/bootstrap.min.css" rel="stylesheet">
      <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.2.1/dist/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous"> -->

      <link href="assets/font-awesome.min.css" rel="stylesheet">
      <link href="assets/sweetalert.css" rel="stylesheet">
      <link href="assets/helpers.css" rel="stylesheet">
      <link href="assets/app.css" rel="stylesheet">
      <!-- Begin JS -->
      <script type="text/javascript">
         var checkNotification = false;
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
      <!--[if lt IE 9]>
      <script src="js/html5shiv.min.js"></script>
      <![endif]-->
      <script>
         
const BrandID = '<?php echo $cxm_merchants['brand_key'] ?>';

         </script>
      <style>
      /* .card-type-image {background:transparent url('images/credit-cards.jpg')" 0 0 no-repeat;} */
      
.dn {
   display:none;
}
.pre {
  position: absolute;
  top: 50%;
  left: 50%;
  margin-top: -15px;
  margin-left: -15px;
  z-index:1;
}
.loader_class
{
   opacity: .5;
}

.body_647188 .colorprimary,
.body_791104 .colorprimary
{
   display:none;
}
.addressdiv .input-group {
    width: 100%;
}

.addressdiv .input-group .form-control {
    border-radius: 4px !important;
}

      </style>
   </head>

   
   <body class="terminal-body body_<?=$cxm_merchants['brand_key']?>">

  
      <ul class="design_notifications_toaster"></ul>
      
<div class="pre loading_div dn">
    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 30 30" enable-background="new 0 0 30 30" xml:space="preserve" width="30" height="30">

		<rect fill="#FBBA44" width="15" height="15">
      <animateTransform attributeName="transform" attributeType="XML" type="translate" dur="1.7s" values="0,0;15,0;15,15;0,15;0,0;" repeatCount="indefinite"/>
		</rect>	

		<rect x="15" fill="#E84150" width="15" height="15">
      <animateTransform attributeName="transform" attributeType="XML" type="translate" dur="1.7s" values="0,0;0,15;-15,15;-15,0;0,0;" repeatCount="indefinite"/>
		</rect>	
      
		<rect x="15" y="15" fill="#62B87B" width="15" height="15">
      <animateTransform attributeName="transform" attributeType="XML" type="translate" dur="1.7s" values="0,0;-15,0;-15,-15;0,-15;0,0;" repeatCount="indefinite"/>
		</rect>	

		<rect y="15" fill="#2F6FB6" width="15" height="15">
      <animateTransform attributeName="transform" attributeType="XML" type="translate" dur="1.7s" values="0,0;0,-15;15,-15;15,0;0,0;" repeatCount="indefinite"/>
		</rect>
    </svg>
  </div>



      <noscript>
         <div class="alert alert-danger mt20neg">
            <div class="container aligncenter">
               <strong>Oops!</strong> It looks like your browser doesn't have Javascript enabled.  Please enable Javascript to use this website.
            </div>
         </div>
      </noscript>
      <?php
if ($ipResponse['country'] == 'PK' && $_SERVER['REMOTE_ADDR'] != '::1')
{ ?>
      <style>.swal-text{text-align:center;}</style>
      <script>swal("Alert!", "You are using non-US IP, please use the correct IP for payment", "error", {buttons: false, closeOnClickOutside: false, closeOnEsc: false});</script>
      <?php
} ?>
      <div class="container terminal-wrapper ">
         <div class="page-header">
            <div class="row align-items-center">
               <div class="col-md-8">
                  <h2 class="colorprimary">
                     <img class="img-responsive" src="<?php echo $cxm_merchants['brandlogo'] ?>" width="250px;">
                     <small style="font-size: 13px; padding: 0 0 0 33px;"><?php echo $cxm_merchants['brandName'] ?> Secure Payment Terminal</small>
                  </h2>
               </div>
               <div class="col-md-4">
                  <h1 class="text-right text-uppercase <?php echo ($cxm_merchants['status'] == 'paid') ? 'text-success' : 'text-danger'; ?>"><?php echo $cxm_merchants['status'] ?></h1>
               </div>
            </div>
         </div>
         <?php if ($cxm_merchants['status'] == 'paid')
{ ?>  
         <div class="alert alert-success">
            <?php $date = date_create($cxm_merchants['updated_at']); ?>
            <strong><i class="fa fa-check"></i> This invoice has already been paid!</strong><br>Payment for this invoice was received on <b><?php echo date_format($date, "j F, Y"); ?></b>.
         </div>
         <?php
} ?>
         <form method="POST" class="validate form-horizontal" id="pay_form" action="receipt.php">
            <?php if ($cxm_merchants['status'] == "due")
{ ?>
            <input type="hidden" id="team_key" name="team_key" value="<?php echo $cxm_merchants['team_key'] ?>">
            <input type="hidden" id="brand_key" name="brand_key" class="enable-subscriptions" value="<?php echo $cxm_merchants['brand_key'] ?>">
            <input type="hidden" id="creatorid" name="creatorid" class="enable-subscriptions" value="<?php echo $cxm_merchants['creatorid'] ?>">
            <input type="hidden" id="agentid"  name="agentid" class="enable-subscriptions" value="<?php echo $cxm_merchants['agent_id'] ?>">
            <input type="hidden" id="clientid"  name="clientid" class="enable-subscriptions" value="<?php echo $cxm_merchants['clientid'] ?>">
            <input type="hidden" id="invoiceid"  name="invoiceid" class="enable-subscriptions" value="<?php echo $cxm_merchants['invoice_key'] ?>">
            <input type="hidden" id="projectid"  name="projectid" class="enable-subscriptions" value="<?php echo $cxm_merchants['project_id'] ?>">
            <input type="hidden" id="salesType"  name="salestype" class="enable-subscriptions" value="<?php echo $cxm_merchants['sales_type'] ?>">
            <input type="hidden" id="payment_gateway"  name="payment_gateway" class="enable-subscriptions" value="authorize">
            <input type="hidden" name="tkn" id="tkn" value="<?php echo $cxm_merchants['invoice_key'] ?>">
            <input type="hidden" name="ip" value="">
            <input type="hidden" name="city" value="">
            <input type="hidden" name="state" value="">
            <input type="hidden" name="country" value="">
            <input type="hidden" name="brand_url" value="<?php echo $cxm_merchants['brandurl'] ?>">
            <input type="hidden" name="date_stamp" value="<?php echo date("Y-m-d h:i:s A"); ?>">
            <input type="hidden" name="brand_name" value="<?php echo $cxm_merchants['brandName'] ?>">
            <input type="hidden" name="merchant_name" value="<?php echo $cxm_merchants['merchant']['merchant'] ?>">
            <input type="hidden" name="source" value="New CRM">
            <input type="hidden" name="cxm_m_id" value="<?php echo cxmEncrypt($cxm_merchants['merchant']['id'], $sasPrivateKey); ?>">
            <input type="hidden" name="cxm_m_mode" value="<?php echo cxmEncrypt($cxm_merchants['merchant']['mode'], $sasPrivateKey); ?>">
            <input type="hidden" name="cxm_m_n" value="<?php echo cxmEncrypt($cxm_merchants['merchant']['merchant'], $sasPrivateKey); ?>">
            <input type="hidden" name="cxm_m_lid" value="<?php echo cxmEncrypt($cxm_merchants['merchant']['live_login_id'], $sasPrivateKey); ?>">
            <input type="hidden" name="cxm_m_key" value="<?php echo cxmEncrypt($cxm_merchants['merchant']['live_transaction_key'], $sasPrivateKey); ?>">
            <input type="hidden" id="card_type" name="card_type" value="">
            <?php
} ?>
            <input type="hidden" name="amount" value="<?php 


            $amount =  ($cxm_merchants['total_amount'] != 0) ? $cxm_merchants['total_amount'] : $cxm_merchants['final_amount'] ;

            echo round($amount);


         ?>">
            <div class="row">
               <div class="col-md-6">
                  <h3 class="colorgray mb30">Payment Details</h3>
                  <div class="form-group">
                     <label class="col-md-3 control-label"><span class="colordanger">*</span>Amount</label>
                     <div class="col-md-9">
                        <div class="input-group">
                           <span class="input-group-addon"><?php echo $cxm_merchants['currency_symbol'] ?></span>
                           <input type="text" id="final_amount" name="final_amount" class="form-control" placeholder="0.00" 
                           value="<?php echo $cxm_merchants['final_amount']; ?>" readonly>
                        </div>
                     </div>
                  </div>


                  <?php if ($cxm_merchants['tax_amount'] != 0)
{ ?>  
                  <div class="form-group">
                     <label class="col-md-3 control-label"><span class="colordanger">*</span><?php echo $cxm_merchants['tax_percentage'] ?>% Tax</label>
                     <div class="col-md-9">
                        <div class="input-group">
                           <span class="input-group-addon"><?php echo $cxm_merchants['currency_symbol'] ?></span>
                           <input type="text" id="tax" name="tax" class="form-control" placeholder="0.00" value="<?php echo $cxm_merchants['tax_amount'] ?>" readonly>
                        </div>
                     </div>
                  </div>
                  <div class="form-group">
                     <label class="col-md-3 control-label"><span class="colordanger">*</span>Net Amount</label>
                     <div class="col-md-9">
                        <div class="input-group">
                           <span class="input-group-addon"><?php echo $cxm_merchants['currency_symbol'] ?></span>
                           <input type="text" id="amount" name="amount" class="form-control" placeholder="0.00" data-rule-required="true" data-rule-number="true" value="<?php echo ($cxm_merchants['total_amount']) ? $cxm_merchants['total_amount'] : $cxm_merchants['final_amount'] ?>" readonly>
                        </div>
                     </div>
                  </div>
                  <?php
} ?>
                  <div class="form-group">
                     <label class="col-md-3 control-label"><span class="colordanger">*</span>Description</label>
                     <div class="col-md-9">
                        <textarea  id="description" name="description" class="form-control xh55 xmaxlength" xmaxlength="120" placeholder="Description" rows="5" xdata-rule-required="true" readonly style="resize:none;"><?php echo $cxm_merchants['invoice_descriptione'] ?></textarea>
                     </div>
                  </div>
                  <hr class="visible-xs visible-sm">
                  <h3 class="colorgray mt40 mb30">Your Information</h3>
                  <div class="form-group">
                     <label class="control-label col-md-3"><span class="colordanger">*</span>Name</label>
                     <div class="col-md-9">
                        <input type="text" readonly id="name" name="name" class="form-control" placeholder="Name" value="<?php echo $cxm_merchants['clientname'] ?>" data-rule-required="true">
                     </div>
                  </div>
                  <div class="form-group">
                     <label class="control-label col-md-3"><span class="colordanger">*</span>Email</label>
                     <div class="col-md-9">
                        <input type="text" readonly  id="email" name="email" class="form-control" placeholder="Email" value="<?php echo $cxm_merchants['clientemail'] ?>" data-rule-required="true" data-rule-email="true">
                     </div>
                  </div>
                  <div class="form-group">
                     <label class="control-label col-md-3"><span class="colordanger">*</span>Phone</label>
                     <div class="col-md-9">
                        <input type="text" readonly  id="email" name="phone" class="form-control" placeholder="Phone" value="<?php echo $cxm_merchants['clientphone'] ?>" data-rule-required="true">
                     </div>
                  </div>
               </div>
               <div class="col-md-6">
                  <hr class="visible-xs visible-sm">
                  <h3 class="colorgray mb30">
                     Payment Method
                     <div class="floatright">
                        <img src="images/credit-cards.jpg" class="">
                     </div>
                  </h3>
                  <div class="creditcard-content">
                     <div class="form-group">
                        <label class="control-label col-md-3"><span class="colordanger">*</span>Name on Card</label>
                        <div class="col-md-9">
                           <div class="input-group">
                              <input type="text" id="card_name" name="card_name" class="form-control" placeholder="Name on Card" value="" data-rule-required="true">
                              <span class="input-group-addon"><i class="fa fa-user"></i></span>
                           </div>
                        </div>
                     </div>
                     <div class="form-group">
                        <label class="control-label col-md-3"><span class="colordanger">*</span>Card Number</label>
                        <div class="col-md-9">
                           <div class="input-group">
                              <input maxlength="16" type="tel" id="card_number" name="card_number" class="form-control card-number" placeholder="Card Number" value="" data-rule-required="true" data-rule-creditcard="true" maxlength="16">
                              <span class="input-group-addon"><i class="fa fa-id-card"></i></span>
                           </div>
                           <div class="card-type-image none"></div>
                        </div>
                     </div>




                     <div class="form-group">
                        <label class="control-label col-md-3"><span class="colordanger">*</span>Expiration/CVC</label>
                        <div class="col-md-9">
                           <div class="row">
                              <div class="col-md-4 col-xs-4 pr5">
                                 <select id="card_exp_month" name="card_exp_month" class="form-control" data-rule-required="true">
                                    <option value="1" >01</option>
                                    <option value="2" >02</option>
                                    <option value="3" >03</option>
                                    <option value="4" >04</option>
                                    <option value="5" >05</option>
                                    <option value="6" >06</option>
                                    <option value="7" >07</option>
                                    <option value="8" >08</option>
                                    <option value="9" >09</option>
                                    <option value="10" >10</option>
                                    <option value="11" >11</option>
                                    <option value="12" >12</option>
                                 </select>
                              </div>
                              <div class="col-md-4 col-xs-4 pl5 pr5">
                                 <select id="card_exp_year" name="card_exp_year" class="form-control" data-rule-required="true">
                                    <?php
for ($j = date('Y');$j < date('Y') + 10;$j++)
{
?>
                                    <option value="<?=$j
?>"><?=$j
?></option>
                                    <?php
}
?>
                                 </select>
                              </div>

                     




                              <div class="col-md-4 col-xs-4 pl5">
                                 <div class="input-group">
                                    <input  
                                       id="card_cvv"  name="card_cvv"  class="form-control" 
                                       placeholder="CVV" value="" type="text" maxlength="4" data-rule-required="true">
                                    <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                 </div>
                              </div>

                           </div>
                        </div>
                     
                     </div>
                     
                     

                     <!-- <div class="form-group">
                        <label class="control-label col-md-3">Zipcode</label>
                        <div class="col-md-9">
                           <div class="input-group">
                              <input type="text" id="zipcode" name="zipcode" class="form-control" placeholder="Zipcode" value="" data-rule-required="true">
                              <span class="input-group-addon"><i class="fa fa-home"></i></span>
                           </div>
                        </div>
                     </div>  -->
<!-- 
                     
<div class="form-group">
                        <label class="control-label col-md-3">Address</label>
                        <div class="col-md-9">
                           <div class="input-group">
                              <input type="text" id="address" name="address" class="form-control" placeholder="Address" value="" data-rule-required="true">
                              <span class="input-group-addon"><i class="fa fa-home"></i></span>
                           </div>
                        </div>
                     </div>


                     -->


                  </div>
                  <input type="hidden" name="invoice_id" value="<?=$_GET['invoicekey'] ?>">



                  
                  <br>
                  <br>
                  <h3 class="colorgray mb30">
                  Billing Address
                     
                  </h3>

                  <div class="addressdiv">

                     <div class="form-group">
                        <label class="control-label col-md-3"><span class="colordanger"></span>Country</label>
                        <div class="col-md-9">
                           <div class="input-group">
                              <select id="country" name="country" class="form-control" ></select>
                              <!-- <span class="input-group-addon"><i class="fa fa-globe"></i></span> -->
                           </div>
                        </div>
                     </div>

                     <div class="form-group">
                        <label class="control-label col-md-3"><span class="colordanger"></span>Address *</label>
                        <div class="col-md-9">
                           <div class="input-group">
                              <input type="text" id="address" name="address" class="form-control" placeholder="Address" value="">
                              <!-- <span class="input-group-addon"><i class="fa fa-map-marker"></i></span> -->
                           </div>
                        </div>
                     </div>

                     <div class="form-group">
                        <label class="control-label col-md-3"><span class="colordanger"></span></label>
                        <div class="col-md-9">
                           <div class="input-group">
                              <input type="text" id="city" name="city" class="form-control" placeholder="City" value="">
                              <!-- <span class="input-group-addon"><i class="fa fa-address-card"></i></span> -->
                           </div>
                        </div>
                     </div>

                     <div class="row">
                        <label class="control-label col-md-3"><span class="colordanger"></span></label>
                        <div class="col-md-9">
                           <div class="row">
                              <div class="col-md-6 pr5">
                                 <div class="input-group">
                                    <input id="state"  name="state" class="form-control" 
                                       placeholder="State" value="" type="text">
                                    <!-- <span class="input-group-addon"><i class="fa fa-building"></i></span> -->
                                 </div>
                              </div>
                              
                              <div class="col-md-6 pl5">
                                 <div class="input-group">
                                    <input id="zipcode"  name="zipcode" class="form-control" 
                                       placeholder="ZIP code" value="" type="text">
                                    <!-- <span class="input-group-addon"><i class="fa fa-envelope"></i></span> -->
                                 </div>
                              </div>
                           </div>
                           
                        </div>
                        
                     </div>

                  </div>

                  
                  <div class="row mt50">
                     <div class="col-md-12 alignright">
                        <div class="creditcard-content">
                           <?php if ($cxm_merchants['status'] == 'due')
{ ?>     
                           <button id="pay_button" type="button" class="btn btn-lg btn-primary submit-button mb20">
                           <span class="total show">Total: <?php echo $cxm_merchants['currency_symbol'] ?><span><?php echo ($cxm_merchants['total_amount'] != 0) ? $cxm_merchants['total_amount'] : $cxm_merchants['final_amount'] ?></span>
                           <small></small></span>
                           <i class="fa fa-check"></i> Submit Payment
                           </button>
                           <?php
}
else
{ ?>
                           <button class="btn btn-lg btn-primary submit-button mb20" disabled="">
                           <i class="fa fa-check"></i> Submit Payment
                           </button>
                           <?php
} ?>
                        </div>
                     </div>
                     <br>
                  </div>
               </div>
            </div>
         </form>
         <br>

         <input type="hidden" value="<?=$cxm_merchants['merchant']['id']?>" name="merchant" />

         <?
         if($cxm_merchants['merchant']['merchant'] != null && $cxm_merchants['merchant']['merchant'] != '')
         {
            ?>
 <div class="alert alert-warning text-center" role="alert">On your bank statement the descriptor should be <b><?php echo $cxm_merchants['merchant']['merchant'] ?></b> as Merchant Name.</div>
            <?
         }
         ?>
        </div>
   </body>
   
<?php
if ($_SERVER['REMOTE_ADDR'] != '::1')
{
    include '../include/chat-code.php';
}
?>


   <script>
$(document).ready(function(){
    // Check if user agent contains the word "Mobile"
    if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        // User is on a mobile device
      //   $zopim.livechat.window.hide();
        console.log("User is on a mobile device");
    } else {
        // User is on a desktop or tablet device
        console.log("User is not on a mobile device");
    }
});

var API_URL = "https://tgcrm.net/api/" ;

if(window.location.origin == 'https://localhost')
{
   API_URL ="https://development.tgcrm.net/api/" ;
}

         $(function(){
            // You can replace this URL with an API that provides country data.
            var countriesURL = 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/countries.json';

            $.getJSON(countriesURL, function (data) {
                var countriesDropdown = $('#country');

                $.each(data, function (key, value) {

                    var selected = '';
                    if(value.iso2 === 'US')
                        selected = 'selected';
                  
                     countriesDropdown.append('<option '+selected+' value="' + value.iso2 + '">' + value.name + '</option>');

                });
            });
        });

      $(function(){
         $('.card-number').on('keyup', function() {
            let cxmCardNumber = $(this).val();
            let cxmCardType = app.getCardType(cxmCardNumber);
            $('#card_type').val(cxmCardType);
         });
         
         $(window).bind("pageshow", function(event) {
                   if (event.originalEvent.persisted) {
                       window.location.reload(); 
                   }
             });
      
      
               $('#pay_button').click(function(){
                  // console.log('pay_button')
                  var card_name = $('#card_name').val();
                  var card_number = $('#card_number').val();
                  var card_cvv = $('#card_cvv').val();


                  var address = $('#address').val();
                  var city = $('#city').val();
                  var state = $('#state').val();
                  var zipcode = $('#zipcode').val();

                  

                  


                  if(card_name == '')
                  {
                     createToast('error','Card name cannot be empty.');
                     return false;
                  }

                  
                  else if(card_number == '')
                  {
                     createToast('error','Card Number cannot be empty.');
                     return false;
                  }
                  else if(card_number.length < 15)
                  {
                     createToast('error','Card Number cannot be less than 15 digits.');
                     return false;
                  }
                  else if(card_cvv.length < 3)
                  {
                     createToast('error','CVV cannot be less than 3 digits.');
                     return false;
                  }




                  <?php 

                  if($cxm_merchants['merchant']['id'] == 2)
                  {
                     ?>
                     if(address == '')
                     {
                        createToast('error','Address Field is required');
                        return false;
                     }

                     if(city == '')
                     {
                        createToast('error','City Field is required');
                        return false;
                     }

                     if(state == '')
                     {
                        createToast('error','State Field is required');
                        return false;
                     }

                     if(zipcode == '')
                     {
                        createToast('error','Zip code Field is required');
                        return false;
                     }
                     
                     <?Php
                  }
                  ?>

                  

                  $('.terminal-wrapper').addClass('loader_class');
                  $('.loading_div').removeClass('dn');

                  

                  var data = $("#pay_form").serialize();
                  $.ajax({
                        type:"POST",
                        url:  "<?=$apiBaseUrl?>/multi-payments",
                        data,
                        success:function(res){
                            console.log('_transactionid_',res);
                        
                            var t = res.response
                        
                        
                        $('.terminal-wrapper').removeClass('loader_class');
                        $('.loading_div').addClass('dn');


                        if(res.payment_gateway == 'none')
                        {  
                           // console.log('t_errors',res.errors);

                           createToast('error','Please Contact Support or try again later.');
                           // var obj = res.errors.payment_process_from.expigate.errors;
                           // createToast('error',obj[Object.keys(obj)[0]]);
                           return false;
                        }


                     
                        if(t.payment_gateway == 'authorize')
                        {
                           if(t.code == 1)
                           {
                              createToast('success','Payment has been done Successfully.');
                              window.location = './invoice/index.php?invoicekey=<?=$_GET['invoicekey']?>';
                           }
                           else if(t.errors == 'Payment merchant not found')
                           {
                              createToast('error','Invalid Card Details');
                           }
                           else
                           {
                              createToast('error',t.message);
                           }
                        }

                        

                        
                        if(t.payment_gateway == 'expigate')
                        {
                           if(t.message.toLowerCase() == 'success' || t.message.toLowerCase() == 'approved')
                           {
                              createToast('success','Payment has been done Successfully.');
                              window.location = './invoice/index.php?invoicekey=<?=$_GET['invoicekey']?>';
                           }
                           else
                           {
                              createToast('error',t.message);
                           }
                        }


                        if(t.payment_gateway == 'payarc')
                        {
                           if(t.message.toLowerCase() == 'success' || t.message.toLowerCase() == 'approved')
                           {
                              createToast('success','Payment has been done Successfully.');
                              window.location = './invoice/index.php?invoicekey=<?=$_GET['invoicekey']?>';
                           }
                           else
                           {
                              createToast('error',t.message);
                           }
                        }


                        
                        
                        },error:function(e){
                            console.log('e.errors',JSON.parse(e.responseText).errors);
                           
                           $('.terminal-wrapper').removeClass('loader_class');
                           $('.loading_div').addClass('dn');

                           

                           


                            if(JSON.parse(e.responseText).errors.card_name != undefined)
                            {
                              createToast('error',JSON.parse(e.responseText).errors.card_name[0]);
                            }
                            else if(JSON.parse(e.responseText).errors.card_number != undefined)
                            {
                              createToast('error',JSON.parse(e.responseText).errors.card_number[0]);
                            }
                            else if(JSON.parse(e.responseText).errors.card_exp_month != undefined)
                            {
                              createToast('error',JSON.parse(e.responseText).errors.card_exp_month[0]);
                            }

                            else if(JSON.parse(e.responseText).errors.card_exp_year != undefined)
                            {
                              createToast('error',JSON.parse(e.responseText).errors.card_exp_year[0]);
                            }
                            else if(JSON.parse(e.responseText).errors.card_cvv != undefined)
                            {
                              createToast('error',JSON.parse(e.responseText).errors.card_cvv[0]);
                            }
                            else{
                              createToast('error',JSON.parse(e.responseText).errors);
                            }

                            // console.log('e.errors',JSON.parse(e.responseText).errors.card_cvv2);
                        }
                    });


               

            });

         });
      
      
            // Nofitication Start.
      setTimeout(function(){
         // console.log('5 secs')
         $('head').append('<link rel="stylesheet" type="text/css" href="https://api.tgcrm.net/toaster.css">');
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
