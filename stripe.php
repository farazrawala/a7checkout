<?php
function cxmEncrypt($string, $key)
{
   $result = '';
   for ($i = 0; $i < strlen($string); $i++) {
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

   for ($i = 0; $i < strlen($string); $i++) {
      $char = substr($string, $i, 1);
      $keychar = substr($key, ($i % strlen($key)) - 1, 1);
      $char = chr(ord($char) - ord($keychar));
      $result .= $char;
   }

   return $result;
}

$sasPrivateKey = "sas";

// Start session to get/generate session ID
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$stripe_session_id = session_id();

if ($_SERVER['REMOTE_ADDR'] == '::1')
   $apiBaseUrl = "https://crm.trivexlabs.com/api";
else
   $apiBaseUrl = "https://crm.trivexlabs.com/api";


// $cxmMerchantJson = file_get_contents($apiBaseUrl . '/show_invoice/' . $_GET['invoicekey'].'?clear='.mt_rand());


// Check if invoicekey exists
if (!isset($_GET['invoicekey']) || empty($_GET['invoicekey'])) {
    die('Error: Invoice key is required');
}

$ch = curl_init();
$url = $apiBaseUrl . '/show_invoice/' . $_GET['invoicekey'] . '?clear=' . mt_rand();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$cxmMerchantJson = curl_exec($ch);
curl_close($ch);


$cxmMerchantArr = json_decode($cxmMerchantJson, true);
$cxm_merchants = array();
if (!empty($cxmMerchantArr['data'])) {
   $cxm_merchants = $cxmMerchantArr['data'];

} else {
   $cxm_merchants = array(
      '1' => array(
         'merchant' => 'No Merchant',
         'live_login_id' => '0',
         'live_transaction_key' => '0',
      )
   );
}

// Safely parse brand URL
$brandUrl_parsed = isset($cxm_merchants['brandurl']) ? parse_url($cxm_merchants['brandurl']) : false;
$brandUrl_with_http = ($brandUrl_parsed && is_array($brandUrl_parsed)) ? $brandUrl_parsed : array('host' => '');

// $brandUrl_with_http = preg_replace('#^https?://#', '', $brandUrl);

// echo '<pre>';
// print_r($brandUrl_with_http);
// exit;

if (isset($cxm_merchants['status']) && ($cxm_merchants['status'] == 'paid' || $cxm_merchants['status'] == 'authorized')) {
   $host = isset($brandUrl_with_http['host']) && !empty($brandUrl_with_http['host']) ? preg_replace('/^www\./', '', $brandUrl_with_http['host']) : 'checkout.example.com';
   $invoice_key = isset($cxm_merchants['invoice_key']) ? $cxm_merchants['invoice_key'] : $_GET['invoicekey'];
   header('Location: https://checkout.' . $host . '/invoice/?invoicekey=' . $invoice_key . '&clear=' . mt_rand());
   exit;
}


// echo '<pre>';
// print_r($cxm_merchants);
// exit;



$curl = curl_init();
if ($_SERVER['REMOTE_ADDR'] == '::1') {
   curl_setopt($curl, CURLOPT_URL, "https://ipinfo.io/203.135.30.178/json?token=12b59c8b5bf82e");
} else {
   curl_setopt($curl, CURLOPT_URL, "https://ipinfo.io/" . $_SERVER['REMOTE_ADDR'] . "/json?token=12b59c8b5bf82e");
}
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$ipResponse = curl_exec($curl);
curl_close($curl);
$ipResponse = json_decode($ipResponse, true);
if (!is_array($ipResponse)) {
    $ipResponse = array('country' => 'US', 'region' => '', 'state' => '');
}
if (isset($ipResponse['region'])) {
    $ipResponse['state'] = $ipResponse['region'];
} else {
    $ipResponse['state'] = '';
}
if (!isset($ipResponse['country'])) {
    $ipResponse['country'] = 'US';
}


function getClientIp()
{
   $ipaddress = '';
   if (getenv('HTTP_CLIENT_IP'))
      $ipaddress = getenv('HTTP_CLIENT_IP');
   else if (getenv('HTTP_X_FORWARDED_FOR'))
      $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
   else if (getenv('HTTP_X_FORWARDED'))
      $ipaddress = getenv('HTTP_X_FORWARDED');
   else if (getenv('HTTP_FORWARDED_FOR'))
      $ipaddress = getenv('HTTP_FORWARDED_FOR');
   else if (getenv('HTTP_FORWARDED'))
      $ipaddress = getenv('HTTP_FORWARDED');
   else if (getenv('REMOTE_ADDR'))
      $ipaddress = getenv('REMOTE_ADDR');
   else
      $ipaddress = 'UNKNOWN';
   return $ipaddress;
}

getClientIp();



?>
<!DOCTYPE html>
<html lang="en-US">

<head>
   <meta charset="utf-8">
   <title><?php echo isset($cxm_merchants['brandName']) ? $cxm_merchants['brandName'] : 'Secure Payment Terminal'; ?> Secure Payment Terminal</title>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="icon" href="<?php echo isset($cxm_merchants['brandlogo']) ? $cxm_merchants['brandlogo'] : ''; ?>" type="image/webp">
   <meta name="csrf-token" content="{{ csrf_token() }}">
   <!-- Begin CSS -->
   <link href="assets/bootstrap.min.css" rel="stylesheet">
   <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.2.1/dist/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous"> -->
   <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"> -->

   <link href="assets/font-awesome.min.css" rel="stylesheet">
   <link href="assets/sweetalert.css" rel="stylesheet">
   <link href="assets/helpers.css" rel="stylesheet">
   <link href="assets/app.css" rel="stylesheet">
   <!-- Begin JS -->

    <script src="https://js.stripe.com/v3/"></script>

    <!-- CSRF (Laravel example – optional) -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
   <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script> -->
   <!--[if lt IE 9]>
      <script src="js/html5shiv.min.js"></script>
      <![endif]-->
   <script>

      const BrandID = '<?php echo isset($cxm_merchants['brand_key']) ? $cxm_merchants['brand_key'] : ''; ?>';

   </script>
   <style>
      /* .card-type-image {background:transparent url('images/credit-cards.jpg')" 0 0 no-repeat;} */

      .dn {
         display: none;
      }

      .pre {
         position: absolute;
         top: 50%;
         left: 50%;
         margin-top: -15px;
         margin-left: -15px;
         z-index: 1;
      }

      .loader_class {
         opacity: .5;
      }

      .body_647188 .colorprimary,
      .body_791104 .colorprimary {
         display: none;
      }

      .addressdiv .input-group {
         width: 100%;
      }

      .addressdiv .input-group .form-control {
         border-radius: 4px !important;
      }

      /* Stripe Elements Styling */
      #card-element {
         padding: 10px 12px;
         border: 1px solid #ccc;
         border-radius: 4px;
         background-color: white;
         min-height: 40px;
         transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
      }

      #card-element:focus {
         border-color: #80bdff;
         outline: 0;
         box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
      }

      .StripeElement {
         box-sizing: border-box;
         height: 40px;
         padding: 10px 12px;
         border: 1px solid transparent;
         border-radius: 4px;
         background-color: white;
         box-shadow: 0 1px 3px 0 #e6ebf1;
         transition: box-shadow 150ms ease;
      }

      .StripeElement--focus {
         box-shadow: 0 1px 3px 0 #cfd7df;
      }

      .StripeElement--invalid {
         border-color: #fa755a;
      }

      .StripeElement--webkit-autofill {
         background-color: #fefde5 !important;
      }
   </style>

   <style>
      /* Toaster container */
      .design_notifications_toaster {
         position: fixed;
         bottom: 10px;
         right: 10px;
         z-index: 9999;
         list-style: none;
         padding: 0;
      }

      /* Toast base styles */
      .toast {
         display: flex;
         align-items: center;
         padding: 10px;
         margin-bottom: 10px;
         border-radius: 5px;
         background-color: #f8f9fa;
         box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
         transition: opacity 0.3s ease;
      }

      /* Icon style */
      .toast i {
         margin-right: 10px;
         font-size: 18px;
      }

      /* Close button */
      .toast i.fa-xmark {
         margin-left: auto;
         cursor: pointer;
      }

      /* Success Toast */
      .toast.success {
         background-color: #28a745;
         color: white;
      }

      /* Error Toast */
      .toast.error {
         background-color: #dc3545;
         color: white;
      }

      /* Warning Toast */
      .toast.warning {
         background-color: #ffc107;
         color: black;
      }

      /* Info Toast */
      .toast.info {
         background-color: #17a2b8;
         color: white;
      }

      /* Hide toast when it's being removed */
      .toast.hide {
         opacity: 0;
      }
   </style>
</head>


<body class="terminal-body body_<?php echo isset($cxm_merchants['brand_key']) ? $cxm_merchants['brand_key'] : ''; ?>">


   <ul class="design_notifications_toaster"></ul>


   <div class="pre loading_div dn">
      <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
         viewBox="0 0 30 30" enable-background="new 0 0 30 30" xml:space="preserve" width="30" height="30">

         <rect fill="#FBBA44" width="15" height="15">
            <animateTransform attributeName="transform" attributeType="XML" type="translate" dur="1.7s"
               values="0,0;15,0;15,15;0,15;0,0;" repeatCount="indefinite" />
         </rect>

         <rect x="15" fill="#E84150" width="15" height="15">
            <animateTransform attributeName="transform" attributeType="XML" type="translate" dur="1.7s"
               values="0,0;0,15;-15,15;-15,0;0,0;" repeatCount="indefinite" />
         </rect>

         <rect x="15" y="15" fill="#62B87B" width="15" height="15">
            <animateTransform attributeName="transform" attributeType="XML" type="translate" dur="1.7s"
               values="0,0;-15,0;-15,-15;0,-15;0,0;" repeatCount="indefinite" />
         </rect>

         <rect y="15" fill="#2F6FB6" width="15" height="15">
            <animateTransform attributeName="transform" attributeType="XML" type="translate" dur="1.7s"
               values="0,0;0,-15;15,-15;15,0;0,0;" repeatCount="indefinite" />
         </rect>
      </svg>
   </div>



   <noscript>
      <div class="alert alert-danger mt20neg">
         <div class="container aligncenter">
            <strong>Oops!</strong> It looks like your browser doesn't have Javascript enabled. Please enable Javascript
            to use this website.
         </div>
      </div>
   </noscript>
   
   <div class="container terminal-wrapper ">
      <div class="page-header">
         <div class="row align-items-center">
            <div class="col-md-8">
               <h2 class="colorprimary">
                  <img class="img-responsive" src="<?php echo isset($cxm_merchants['brandlogo']) ? $cxm_merchants['brandlogo'] : ''; ?>" width="250px;">
                  <small style="font-size: 13px; padding: 0 0 0 33px;"><?php echo isset($cxm_merchants['brandName']) ? $cxm_merchants['brandName'] : ''; ?> Secure
                     Payment Terminal</small>
               </h2>
            </div>
            <div class="col-md-4">
               <h1
                  class="text-right text-uppercase <?php echo (isset($cxm_merchants['status']) && $cxm_merchants['status'] == 'paid') ? 'text-success' : 'text-danger'; ?>">
                  <?php echo isset($cxm_merchants['status']) ? $cxm_merchants['status'] : 'due'; ?>
               </h1>
            </div>
         </div>
      </div>
      <?php if (isset($cxm_merchants['status']) && $cxm_merchants['status'] == 'paid') { ?>
         <div class="alert alert-success">
            <?php 
            if (isset($cxm_merchants['updated_at']) && !empty($cxm_merchants['updated_at'])) {
               $date = date_create($cxm_merchants['updated_at']);
               $date_formatted = $date ? date_format($date, "j F, Y") : date("j F, Y");
            } else {
               $date_formatted = date("j F, Y");
            }
            ?>
            <strong><i class="fa fa-check"></i> This invoice has already been paid!</strong><br>Payment for this invoice
            was received on <b><?php echo $date_formatted; ?></b>.
         </div>
         <?php
      } ?>
      <form method="POST" class="validate form-horizontal" id="pay_form" >
         <?php if (isset($cxm_merchants['status']) && $cxm_merchants['status'] == "due") { ?>

            <input type="hidden" name="customer_ip" value="<?php echo getClientIp(); ?>">

            <input type="hidden" id="team_key" name="team_key" value="<?php echo isset($cxm_merchants['team_key']) ? $cxm_merchants['team_key'] : ''; ?>">
            <input type="hidden" id="brand_key" name="brand_key" class="enable-subscriptions"
               value="<?php echo isset($cxm_merchants['brand_key']) ? $cxm_merchants['brand_key'] : ''; ?>">
            <input type="hidden" id="creatorid" name="creatorid" class="enable-subscriptions"
               value="<?php echo isset($cxm_merchants['creatorid']) ? $cxm_merchants['creatorid'] : ''; ?>">
            <input type="hidden" id="agentid" name="agentid" class="enable-subscriptions"
               value="<?php echo isset($cxm_merchants['agent_id']) ? $cxm_merchants['agent_id'] : ''; ?>">
            <input type="hidden" id="clientid" name="clientid" class="enable-subscriptions"
               value="<?php echo isset($cxm_merchants['clientid']) ? $cxm_merchants['clientid'] : ''; ?>">
            <input type="hidden" id="invoiceid" name="invoiceid" class="enable-subscriptions"
               value="<?php echo isset($cxm_merchants['invoice_key']) ? $cxm_merchants['invoice_key'] : ''; ?>">
            <input type="hidden" id="projectid" name="projectid" class="enable-subscriptions"
               value="<?php echo isset($cxm_merchants['project_id']) ? $cxm_merchants['project_id'] : ''; ?>">
            <input type="hidden" id="salesType" name="salestype" class="enable-subscriptions"
               value="<?php echo isset($cxm_merchants['sales_type']) ? $cxm_merchants['sales_type'] : ''; ?>">
            <input type="hidden" id="payment_gateway" name="payment_gateway" class="enable-subscriptions"
               value="authorize">
            <input type="hidden" name="tkn" id="tkn" value="<?php echo isset($cxm_merchants['invoice_key']) ? $cxm_merchants['invoice_key'] : ''; ?>">
            <input type="hidden" name="ip" value="">
            <input type="hidden" name="city" value="">
            <input type="hidden" name="state" value="">
            <input type="hidden" name="country" value="">
            <input type="hidden" name="brand_url" value="<?php echo isset($cxm_merchants['brandurl']) ? $cxm_merchants['brandurl'] : ''; ?>">
            <input type="hidden" name="date_stamp" value="<?php echo date("Y-m-d h:i:s A"); ?>">
            <input type="hidden" name="brand_name" value="<?php echo isset($cxm_merchants['brandName']) ? $cxm_merchants['brandName'] : ''; ?>">
            <input type="hidden" name="merchant_name" value="<?php echo isset($cxm_merchants['merchant']['merchant']) ? $cxm_merchants['merchant']['merchant'] : '' ?>">
            <input type="hidden" name="source" value="New CRM">
            <input type="hidden" name="cxm_m_id"
               value="<?php echo isset($cxm_merchants['merchant']['id']) ? cxmEncrypt($cxm_merchants['merchant']['id'], $sasPrivateKey) : ''; ?>">
            <input type="hidden" name="cxm_m_mode"
               value="<?php echo isset($cxm_merchants['merchant']['mode']) ? cxmEncrypt($cxm_merchants['merchant']['mode'], $sasPrivateKey) : ''; ?>">
            <input type="hidden" name="cxm_m_n"
               value="<?php echo isset($cxm_merchants['merchant']['merchant']) ? cxmEncrypt($cxm_merchants['merchant']['merchant'], $sasPrivateKey) : ''; ?>">
            <input type="hidden" name="cxm_m_lid"
               value="<?php echo isset($cxm_merchants['merchant']['live_login_id']) ? cxmEncrypt($cxm_merchants['merchant']['live_login_id'], $sasPrivateKey) : ''; ?>">
            <input type="hidden" name="cxm_m_key"
               value="<?php echo isset($cxm_merchants['merchant']['live_transaction_key']) ? cxmEncrypt($cxm_merchants['merchant']['live_transaction_key'], $sasPrivateKey) : ''; ?>">
            <input type="hidden" id="card_type" name="card_type" value="">
            <?php
         } ?>
         <input type="hidden" name="amount" value="<?php


         $amount = (isset($cxm_merchants['total_amount']) && $cxm_merchants['total_amount'] != 0) ? $cxm_merchants['total_amount'] : (isset($cxm_merchants['final_amount']) ? $cxm_merchants['final_amount'] : 0);

         echo round($amount);


         ?>">
         <div class="row">
            <div class="col-md-6">
               <h3 class="colorgray mb30">Payment Details</h3>
               <div class="form-group">
                  <label class="col-md-3 control-label"><span class="colordanger">*</span>Amount</label>
                  <div class="col-md-9">
                     <div class="input-group">
                        <span class="input-group-addon"><?php echo isset($cxm_merchants['currency_symbol']) ? $cxm_merchants['currency_symbol'] : '$'; ?></span>
                        <input type="text" id="final_amount" name="final_amount" class="form-control" placeholder="0.00"
                           value="<?php echo isset($cxm_merchants['final_amount']) ? $cxm_merchants['final_amount'] : ''; ?>" readonly>
                     </div>
                  </div>
               </div>


               <?php if (isset($cxm_merchants['tax_amount']) && $cxm_merchants['tax_amount'] != 0) { ?>
                  <div class="form-group">
                     <label class="col-md-3 control-label"><span
                           class="colordanger">*</span><?php echo isset($cxm_merchants['tax_percentage']) ? $cxm_merchants['tax_percentage'] : '0'; ?>% Tax</label>
                     <div class="col-md-9">
                        <div class="input-group">
                           <span class="input-group-addon"><?php echo isset($cxm_merchants['currency_symbol']) ? $cxm_merchants['currency_symbol'] : '$'; ?></span>
                           <input type="text" id="tax" name="tax" class="form-control" placeholder="0.00"
                              value="<?php echo isset($cxm_merchants['tax_amount']) ? $cxm_merchants['tax_amount'] : ''; ?>" readonly>
                        </div>
                     </div>
                  </div>
                  <div class="form-group">
                     <label class="col-md-3 control-label"><span class="colordanger">*</span>Net Amount</label>
                     <div class="col-md-9">
                        <div class="input-group">
                           <span class="input-group-addon"><?php echo isset($cxm_merchants['currency_symbol']) ? $cxm_merchants['currency_symbol'] : '$'; ?></span>
                           <input type="text" id="amount" name="amount" class="form-control" placeholder="0.00"
                              data-rule-required="true" data-rule-number="true"
                              value="<?php echo (isset($cxm_merchants['total_amount']) && $cxm_merchants['total_amount']) ? $cxm_merchants['total_amount'] : (isset($cxm_merchants['final_amount']) ? $cxm_merchants['final_amount'] : ''); ?>"
                              readonly>
                        </div>
                     </div>
                  </div>
                  <?php
               } ?>
               <div class="form-group">
                  <label class="col-md-3 control-label"><span class="colordanger">*</span>Description</label>
                  <div class="col-md-9">
                     <textarea id="description" name="description" class="form-control xh55 xmaxlength" xmaxlength="120"
                        placeholder="Description" rows="5" xdata-rule-required="true" readonly
                        style="resize:none;"><?php echo isset($cxm_merchants['invoice_descriptione']) ? $cxm_merchants['invoice_descriptione'] : ''; ?></textarea>
                  </div>
               </div>
               <hr class="visible-xs visible-sm">
               <h3 class="colorgray mt40 mb30">Your Information</h3>
               <div class="form-group">
                  <label class="control-label col-md-3"><span class="colordanger">*</span>Name</label>
                  <div class="col-md-9">
                     <input type="text" readonly id="name" name="name" class="form-control" placeholder="Name"
                        value="<?php echo isset($cxm_merchants['clientname']) ? $cxm_merchants['clientname'] : ''; ?>" data-rule-required="true">
                  </div>
               </div>
               <div class="form-group">
                  <label class="control-label col-md-3"><span class="colordanger">*</span>Email</label>
                  <div class="col-md-9">
                     <input type="text" readonly id="email" name="email" class="form-control" placeholder="Email"
                        value="<?php echo isset($cxm_merchants['clientemail']) ? $cxm_merchants['clientemail'] : ''; ?>" data-rule-required="true"
                        data-rule-email="true">
                  </div>
               </div>
               <div class="form-group">
                  <label class="control-label col-md-3"><span class="colordanger">*</span>Phone</label>
                  <div class="col-md-9">
                     <input type="text" readonly id="email" name="phone" class="form-control" placeholder="Phone"
                        value="<?php echo isset($cxm_merchants['clientphone']) ? $cxm_merchants['clientphone'] : ''; ?>" data-rule-required="true">
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

                  <form id="payment-form" novalidate onsubmit="return false;">
                     <div class="form-group">
                        <label class="control-label col-md-3"><span class="colordanger">*</span>Name on Card</label>
                        <div class="col-md-9">
                           <div class="input-group">
                              <input type="text" id="cardHolderName" name="cardHolderName" class="form-control" placeholder="Name on Card">
                              <span class="input-group-addon"><i class="fa fa-user"></i></span>
                           </div>
                        </div>
                     </div>

                     <!--<div class="form-group">-->
                     <!--   <label class="control-label col-md-3"><span class="colordanger">*</span>Email</label>-->
                     <!--   <div class="col-md-9">-->
                     <!--      <div class="input-group">-->
                     <!--         <input type="email" id="email" name="email" class="form-control" placeholder="Email">-->
                     <!--         <span class="input-group-addon"><i class="fa fa-envelope"></i></span>-->
                     <!--      </div>-->
                     <!--   </div>-->
                     <!--</div>-->

                     <div class="form-group">
                        <label class="control-label col-md-3"><span class="colordanger">*</span>Card Details</label>
                        <div class="col-md-9">
                           <div id="card-element" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px; background-color: white; min-height: 40px;">
                              <!-- Stripe Elements will mount here -->
                           </div>
                        </div>
                     </div>

                     <input type="hidden" id="leadId" name="leadId" value="<?php echo isset($_GET['invoicekey']) ? $_GET['invoicekey'] : ''; ?>">
                     <input type="hidden" id="amount" name="amount" value="<?php 
                        $stripe_amount = (isset($cxm_merchants['total_amount']) && $cxm_merchants['total_amount'] != 0) ? $cxm_merchants['total_amount'] : (isset($cxm_merchants['final_amount']) ? $cxm_merchants['final_amount'] : 0);
                        echo intval(round($stripe_amount * 100)); // Convert to cents for Stripe
                     ?>">

                     <div class="form-group">
                        <div class="col-md-9 col-md-offset-3">
                           <div class="checkbox">
                              <label for="terms" style="font-size: 13px;">
                                 <input type="checkbox" id="terms" name="terms">
                                 I agree to the <a href="<?php
echo ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . implode('.', array_slice(explode('.', preg_replace('/:\d+$/','', $_SERVER['HTTP_HOST'])), -2));
?>/privacy-policy" target="_blank">Privacy Policy</a> &amp;
                                 <a href="<?php
echo ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . implode('.', array_slice(explode('.', preg_replace('/:\d+$/','', $_SERVER['HTTP_HOST'])), -2));
?>/terms-condition" target="_blank">Terms</a>
                              </label>
                           </div>
                        </div>
                     </div>

                     <div class="form-group">
                        <div class="col-md-9 col-md-offset-3">
                           <div id="card-errors" class="alert alert-danger" role="alert" style="display: none; margin-bottom: 15px;"></div>
                           <button id="submit" type="button" class="btn btn-lg btn-primary submit-button">
                              <i class="fa fa-check"></i> Proceed
                           </button>
                        </div>
                     </div>
                  </form>

                 

               </div>
               <input type="hidden" name="invoice_id" value="<?php echo isset($_GET['invoicekey']) ? $_GET['invoicekey'] : ''; ?>">




               <br>
               <br>
               <!-- <h3 class="colorgray mb30">                  Billing Address</h3> -->
<!-- 
               <div class="addressdiv">

                  <div class="form-group"><label class="control-label col-md-3"><span class="colordanger"></span>Country</label><div class="col-md-9"><div class="input-group"><select id="country" name="country" class="form-control"><option value="AF">Afghanistan</option><option value="AL">Albania</option><option value="AM">Armenia</option><option value="AO">Angola</option><option value="AR">Argentina</option><option value="AS">American Samoa</option><option value="AT">Austria</option><option value="AU">Australia</option><option value="AW">Aruba</option><option value="AX">Åland Islands</option><option value="AZ">Azerbaijan</option><option value="BA">Bosnia and Herzegovina</option><option value="BB">Barbados</option><option value="BD">Bangladesh</option><option value="BE">Belgium</option><option value="BF">Burkina Faso</option><option value="BG">Bulgaria</option><option value="BH">Bahrain</option><option value="BI">Burundi</option><option value="BJ">Benin</option><option value="BL">Saint Barthélemy</option><option value="BN">Brunei</option><option value="BO">Bolivia</option><option value="BQ">Caribbean Netherlands</option><option value="BR">Brazil</option><option value="BS">Bahamas</option><option value="BT">Bhutan</option><option value="BW">Botswana</option><option value="BY">Belarus</option><option value="BZ">Belize</option><option value="CA">Canada</option><option value="CC">Cocos (Keeling) Islands</option><option value="CD">DR Congo</option><option value="CF">Central African Republic</option><option value="CG">Republic of the Congo</option><option value="CH">Switzerland</option><option value="CI">Ivory Coast</option><option value="CK">Cook Islands</option><option value="CL">Chile</option><option value="CM">Cameroon</option><option value="CN">China</option><option value="CO">Colombia</option><option value="CR">Costa Rica</option><option value="CU">Cuba</option><option value="CV">Cape Verde</option><option value="CW">Curaçao</option><option value="CX">Christmas Island</option><option value="CY">Cyprus</option><option value="CZ">Czechia</option><option value="DE">Germany</option><option value="DJ">Djibouti</option><option value="DK">Denmark</option><option value="DM">Dominica</option><option value="DO">Dominican Republic</option><option value="DZ">Algeria</option><option value="EC">Ecuador</option><option value="EE">Estonia</option><option value="EG">Egypt</option><option value="EH">Western Sahara</option><option value="ER">Eritrea</option><option value="ES">Spain</option><option value="ET">Ethiopia</option><option value="FI">Finland</option><option value="FJ">Fiji</option><option value="FM">Micronesia</option><option value="FO">Faroe Islands</option><option value="FR">France</option><option value="GA">Gabon</option><option value="GB">United Kingdom</option><option value="GD">Grenada</option><option value="GE">Georgia</option><option value="GH">Ghana</option><option value="GI">Gibraltar</option><option value="GL">Greenland</option><option value="GM">Gambia</option><option value="GN">Guinea</option><option value="GP">Guadeloupe</option><option value="GQ">Equatorial Guinea</option><option value="GR">Greece</option><option value="GT">Guatemala</option><option value="GU">Guam</option><option value="GW">Guinea-Bissau</option><option value="GY">Guyana</option><option value="HK">Hong Kong</option><option value="HN">Honduras</option><option value="HR">Croatia</option><option value="HT">Haiti</option><option value="HU">Hungary</option><option value="ID">Indonesia</option><option value="IE">Ireland</option><option value="IL">Israel</option><option value="IM">Isle of Man</option><option value="IN">India</option><option value="IQ">Iraq</option><option value="IR">Iran</option><option value="IS">Iceland</option><option value="IT">Italy</option><option value="JE">Jersey</option><option value="JM">Jamaica</option><option value="JO">Jordan</option><option value="JP">Japan</option><option value="KE">Kenya</option><option value="KG">Kyrgyzstan</option><option value="KH">Cambodia</option><option value="KI">Kiribati</option><option value="KM">Comoros</option><option value="KN">Saint Kitts and Nevis</option><option value="KP">North Korea</option><option value="KR">South Korea</option><option value="KW">Kuwait</option><option value="KY">Cayman Islands</option><option value="KZ">Kazakhstan</option><option value="LA">Laos</option><option value="LB">Lebanon</option><option value="LC">Saint Lucia</option><option value="LI">Liechtenstein</option><option value="LK">Sri Lanka</option><option value="LR">Liberia</option><option value="LS">Lesotho</option><option value="LT">Lithuania</option><option value="LU">Luxembourg</option><option value="LV">Latvia</option><option value="LY">Libya</option><option value="MA">Morocco</option><option value="MD">Moldova</option><option value="ME">Montenegro</option><option value="MF">Saint Martin</option><option value="MG">Madagascar</option><option value="MH">Marshall Islands</option><option value="MK">North Macedonia</option><option value="ML">Mali</option><option value="MM">Myanmar</option><option value="MN">Mongolia</option><option value="MO">Macau</option><option value="MP">Northern Mariana Islands</option><option value="MQ">Martinique</option><option value="MR">Mauritania</option><option value="MS">Montserrat</option><option value="MT">Malta</option><option value="MU">Mauritius</option><option value="MV">Maldives</option><option value="MW">Malawi</option><option value="MX">Mexico</option><option value="MY">Malaysia</option><option value="MZ">Mozambique</option><option value="NA">Namibia</option><option value="NC">New Caledonia</option><option value="NE">Niger</option><option value="NF">Norfolk Island</option><option value="NG">Nigeria</option><option value="NI">Nicaragua</option><option value="NL">Netherlands</option><option value="NO">Norway</option><option value="NP">Nepal</option><option value="NR">Nauru</option><option value="NU">Niue</option><option value="NZ">New Zealand</option><option value="OM">Oman</option><option value="PA">Panama</option><option value="PE">Peru</option><option value="PF">French Polynesia</option><option value="PG">Papua New Guinea</option><option value="PH">Philippines</option><option value="PK">Pakistan</option><option value="PL">Poland</option><option value="PM">Saint Pierre and Miquelon</option><option value="PN">Pitcairn Islands</option><option value="PR">Puerto Rico</option><option value="PT">Portugal</option><option value="PW">Palau</option><option value="PY">Paraguay</option><option value="QA">Qatar</option><option value="RE">Réunion</option><option value="RO">Romania</option><option value="RS">Serbia</option><option value="RU">Russia</option><option value="RW">Rwanda</option><option value="SA">Saudi Arabia</option><option value="SB">Solomon Islands</option><option value="SC">Seychelles</option><option value="SD">Sudan</option><option value="SE">Sweden</option><option value="SG">Singapore</option><option value="SH">Saint Helena, Ascension and Tristan da Cunha</option><option value="SI">Slovenia</option><option value="SJ">Svalbard and Jan Mayen</option><option value="SK">Slovakia</option><option value="SL">Sierra Leone</option><option value="SM">San Marino</option><option value="SN">Senegal</option><option value="SO">Somalia</option><option value="SR">Suriname</option><option value="SS">South Sudan</option><option value="ST">São Tomé and Príncipe</option><option value="SV">El Salvador</option><option value="SX">Sint Maarten</option><option value="SY">Syria</option><option value="SZ">Eswatini</option><option value="TC">Turks and Caicos Islands</option><option value="TD">Chad</option><option value="TF">French Southern and Antarctic Lands</option><option value="TG">Togo</option><option value="TH">Thailand</option><option value="TJ">Tajikistan</option><option value="TK">Tokelau</option><option value="TL">Timor-Leste</option><option value="TM">Turkmenistan</option><option value="TN">Tunisia</option><option value="TO">Tonga</option><option value="TR">Turkey</option><option value="TT">Trinidad and Tobago</option><option value="TV">Tuvalu</option><option value="TZ">Tanzania</option><option value="UA">Ukraine</option><option value="UG">Uganda</option><option value="UA">United Arab Emirates</option><option value="UM">United States Minor Outlying Islands</option><option value="UN">United Nations</option><option value="US" selected="selected" class="init">United States</option><option value="UY">Uruguay</option><option value="UZ">Uzbekistan</option><option value="VA">Vatican City</option><option value="VC">Saint Vincent and the Grenadines</option><option value="VE">Venezuela</option><option value="VI">United States Virgin Islands</option><option value="VN">Vietnam</option><option value="VU">Vanuatu</option><option value="WF">Wallis and Futuna</option><option value="WS">Samoa</option><option value="YE">Yemen</option><option value="YT">Mayotte</option><option value="ZA">South Africa</option><option value="ZM">Zambia</option><option value="ZW">Zimbabwe</option></select></div></div></div>
                  
                  <div class="form-group">
                     <label class="control-label col-md-3"><span class="colordanger"></span>Address *</label>
                     <div class="col-md-9">
                        <div class="input-group">
                           <input type="text" id="address" name="address" class="form-control" placeholder="Address"
                              value="">
                         </div>
                     </div>
                  </div>

                  <div class="form-group">
                     <label class="control-label col-md-3"><span class="colordanger"></span></label>
                     <div class="col-md-9">
                        <div class="input-group">
                           <input type="text" id="city" name="city" class="form-control" placeholder="City" value="">
                         </div>
                     </div>
                  </div>

                  <div class="row">
                     <label class="control-label col-md-3"><span class="colordanger"></span></label>
                     <div class="col-md-9">
                        <div class="row">
                           <div class="col-md-6 pr5">
                              <div class="input-group">
                                 <input id="state" name="state" class="form-control" placeholder="State" value=""
                                    type="text">
                              </div>
                           </div>

                           <div class="col-md-6 pl5">
                              <div class="input-group">
                                 <input id="zipcode" name="zipcode" class="form-control" placeholder="ZIP code" value=""
                                    type="text">
                              </div>
                           </div>
                        </div>

                     </div>

                  </div>

               </div> -->


               <!-- <div class="row mt50">
                  <div class="col-md-12 alignright">
                     <div class="creditcard-content">
                        <?php if (isset($cxm_merchants['status']) && $cxm_merchants['status'] == 'due') { ?>
                           <button id="pay_button" type="button" class="btn btn-lg btn-primary submit-button mb20">
                              <span class="total show">Total:
                                 <?php echo isset($cxm_merchants['currency_symbol']) ? $cxm_merchants['currency_symbol'] : '$'; ?><span><?php echo (isset($cxm_merchants['total_amount']) && $cxm_merchants['total_amount'] != 0) ? $cxm_merchants['total_amount'] : (isset($cxm_merchants['final_amount']) ? $cxm_merchants['final_amount'] : 0); ?></span>
                                 <small></small></span>
                              <i class="fa fa-check"></i> Submit Payment
                           </button>
                           <?php
                        } else { ?>
                           <button class="btn btn-lg btn-primary submit-button mb20" disabled="">
                              <i class="fa fa-check"></i> Submit Payment
                           </button>
                           <?php
                        } ?>
                     </div>
                  </div>
                  <br>
               </div> -->
               
            </div>
         </div>
      </form>
      <br>

      <input type="hidden" class="authorize_active" value=""
         name="merchant" />

     <!-- <div class="alert alert-warning text-center" role="alert">On your bank statement the descriptor should be <b>
               Zedexsolutions LLC
            </b> as Merchant Name.</div> -->
   </div>
</body>

<?php


function isMobile()
{
   return preg_match('/(android|iphone|ipad|ipod|blackberry|opera mini|iemobile|mobile)/i', $_SERVER['HTTP_USER_AGENT']);
}

$chatPath = '../include/chat-code.php';

// Check if '../include/chat-code.php' exists, if not, use '../includes/chat-code.php'
if (!file_exists($chatPath)) {
   $chatPath = '../includes/chat-code.php';
}

if ($_SERVER['REMOTE_ADDR'] != '::1') {
   if (isset($cxm_merchants['team_key']) && $cxm_merchants['team_key'] == 489362) {
      if (!isMobile()) {
         if (file_exists($chatPath)) {
            include $chatPath;
         }
      } else {
         echo '<p class="user from mobile."></p>';
      }
   } else {
      if (file_exists($chatPath)) {
         include $chatPath;
      }
   }
}

// Define URL variables for JavaScript
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
 $root_domain = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . implode('.', array_slice(explode('.', preg_replace('/:\d+$/','', $_SERVER['HTTP_HOST'])), -2));



$currentUrl = $protocol . $domain . $_SERVER['REQUEST_URI'];
$invoice_url = str_replace('checkout.php', 'index.php', $currentUrl);
?>

 <?php
   $publish_key = 'pk_live_51OlKHdDiItQuJkczJfnL5XlKhWfjlUPVi7zIH5T6vKSWGuDLX8XODTzKRI4oSNOdnmd0psCCmP7zK3e3m6oQrV1H00OVAQ9Qt6';
   if(isset($cxm_merchants['brand_type']) && $cxm_merchants['brand_type'] == 'Branded')
   {
      $publish_key = 'pk_live_51OlKHdDiItQuJkczJfnL5XlKhWfjlUPVi7zIH5T6vKSWGuDLX8XODTzKRI4oSNOdnmd0psCCmP7zK3e3m6oQrV1H00OVAQ9Qt6';
   }
   else
   {
      $publish_key = 'pk_live_51OlKHdDiItQuJkczJfnL5XlKhWfjlUPVi7zIH5T6vKSWGuDLX8XODTzKRI4oSNOdnmd0psCCmP7zK3e3m6oQrV1H00OVAQ9Qt6';
   }
   ?>
<script>

  
    const stripe = Stripe('<?php echo $publish_key; ?>');
    const elements = stripe.elements();

    const card = elements.create('card', {
        hidePostalCode: true
    });
    card.mount('#card-element');

    const form = document.getElementById('payment-form');
    const submitBtn = document.getElementById('submit');
    const cardErrors = document.getElementById('card-errors');

    // Prevent any default form submission
    if (form) {
        form.onsubmit = function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        };
    }

    // Display card errors
    card.on('change', function(event) {
        const displayError = document.getElementById('card-errors');
        if (displayError) {
            if (event.error) {
                displayError.className = 'alert alert-danger';
                displayError.textContent = event.error.message;
                displayError.style.display = 'block';
            } else {
                displayError.textContent = '';
                displayError.style.display = 'none';
            }
        }
    });

    // Handle form submission via button click
    async function handlePayment() {
        console.log('Payment form submitted - preventing default');
        
        // Get form values
        const cardHolderName = document.getElementById('cardHolderName').value.trim();
        const email = document.getElementById('email').value.trim();
        const terms = document.getElementById('terms').checked;
        const amount = parseInt(document.getElementById('amount').value);
        const leadId = document.getElementById('leadId').value;

        // Validate form
        if (!cardHolderName) {
            cardErrors.className = 'alert alert-danger';
            cardErrors.textContent = 'Please enter the name on card.';
            cardErrors.style.display = 'block';
            return;
        }

        if (!email) {
            cardErrors.className = 'alert alert-danger';
            cardErrors.textContent = 'Please enter your email address.';
            cardErrors.style.display = 'block';
            return;
        }

        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            cardErrors.className = 'alert alert-danger';
            cardErrors.textContent = 'Please enter a valid email address.';
            cardErrors.style.display = 'block';
            return;
        }

        if (!terms) {
            cardErrors.className = 'alert alert-danger';
            cardErrors.textContent = 'Please agree to the Privacy Policy and Terms.';
            cardErrors.style.display = 'block';
            return;
        }

        if (!amount || amount <= 0 || isNaN(amount)) {
            cardErrors.className = 'alert alert-danger';
            cardErrors.textContent = 'Invalid payment amount.';
            cardErrors.style.display = 'block';
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
        cardErrors.textContent = '';
        cardErrors.style.display = 'none';
        cardErrors.className = 'alert alert-danger';

        try {
            console.log('Sending request to stripe_process.php');
            
            const response = await fetch('stripe_process.php?type=<?php echo isset($cxm_merchants['brand_type']) ? $cxm_merchants['brand_type'] : ''; ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    amount: amount,
                    leadId: leadId,
                    name: cardHolderName,
                    email: email
                })
            });

            // console.log('Response:', response);
            // return;

            // Check if response is OK
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Parse JSON response
            let data;
            try {
                data = await response.json();
            } catch (parseError) {
                throw new Error('Invalid response from server. Please try again.');
            }

            // Check for errors in response
            if (data.error) {
                throw new Error(data.error);
            }

            if (!data.clientSecret) {
                throw new Error('Invalid response from server');
            }

            const { paymentIntent, error } = await stripe.confirmCardPayment(
                data.clientSecret,
                {
                    payment_method: {
                        card: card,
                        billing_details: {
                            name: cardHolderName,
                            email: email
                        }
                    }
                }
            );

            if (error) {
                cardErrors.className = 'alert alert-danger';
                cardErrors.textContent = error.message;
                cardErrors.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa fa-check"></i> Proceed';
                return;
            }

            if (paymentIntent.status === 'succeeded') {
                cardErrors.className = 'alert alert-success';
                cardErrors.textContent = 'Payment successful! Redirecting...';
                cardErrors.style.display = 'block';
                setTimeout(() => {
                  window.location.href = 'https://crm.trivexlabs.com/stripe_response/?type=success&invoiceid=<?php echo isset($_GET['invoicekey']) ? $_GET['invoicekey'] : ''; ?>&invoicekey=<?php echo isset($_GET['invoicekey']) ? $_GET['invoicekey'] : ''; ?>&payment_intent=' + paymentIntent.id + '&brandid=<?php echo isset($cxm_merchants['brand_key']) ? $cxm_merchants['brand_key'] : ''; ?>&session_id=<?php echo $stripe_session_id; ?>&url=<?php echo urlencode(preg_replace('#^(https?://)www\.#i', '$1', $invoice_url)); ?>';
                  
                  }, 1500);

            } else if (paymentIntent.status === 'processing') {
                cardErrors.className = 'alert alert-info';
                cardErrors.textContent = 'Your payment is being processed. Please wait...';
                cardErrors.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa fa-check"></i> Proceed';
            } else if (paymentIntent.status === 'requires_action') {
                cardErrors.className = 'alert alert-warning';
                cardErrors.textContent = 'Additional authentication required. Please complete the verification.';
                cardErrors.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa fa-check"></i> Proceed';
            } else {
                cardErrors.className = 'alert alert-danger';
                cardErrors.textContent = 'Payment was not completed. Status: ' + paymentIntent.status;
                cardErrors.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa fa-check"></i> Proceed';
            }

        } catch (error) {
            console.error('Payment error:', error);
            cardErrors.className = 'alert alert-danger';
            cardErrors.textContent = error.message || 'An error occurred. Please try again.';
            cardErrors.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa fa-check"></i> Proceed';
        }
    }

    // Attach event listeners
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Submit button clicked');
            handlePayment();
        });
    }

    // Also prevent form submission as backup
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            console.log('Form submit event - preventing default');
            handlePayment();
        });
    }
</script>
</html>