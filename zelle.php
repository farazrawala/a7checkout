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



$ch = curl_init();
$url = $apiBaseUrl . '/show_invoice/' . $_GET['invoicekey'] . '?clear=' . mt_rand();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$cxmMerchantJson = curl_exec($ch);


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

$brandUrl_with_http = preg_replace('/^www\./', '', parse_url($cxm_merchants['brandurl']));

// $brandUrl_with_http = preg_replace('#^https?://#', '', $brandUrl);

// echo '<pre>';
// print_r($brandUrl_with_http);
// exit;

if ($cxm_merchants['status'] == 'paid' || $cxm_merchants['status'] == 'authorized') {

   header('Location: https://checkout.' . $brandUrl_with_http['host'] . '/invoice/?invoicekey=' . $cxm_merchants['invoice_key'] . '&clear=' . mt_rand());
   exit;
}

$curl = curl_init();
if ($_SERVER['REMOTE_ADDR'] == '::1') {
   curl_setopt($curl, CURLOPT_URL, "https://ipinfo.io/203.135.30.178/json?token=12b59c8b5bf82e");
} else {
   curl_setopt($curl, CURLOPT_URL, "https://ipinfo.io/" . $_SERVER['REMOTE_ADDR'] . "/json?token=12b59c8b5bf82e");
}
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$ipResponse = curl_exec($curl);
$ipResponse = (array) json_decode($ipResponse);
$ipResponse['state'] = $ipResponse['region'];


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
   <title><?php echo $cxm_merchants['brandName'] ?> Secure Payment Terminal</title>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="icon" href="<?php echo $cxm_merchants['brandlogo'] ?>" type="image/webp">
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

      const BrandID = '<?php echo $cxm_merchants['brand_key'] ?>';

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


<body class="terminal-body body_<?= $cxm_merchants['brand_key'] ?>">


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
   <?php
   if ($ipResponse['country'] == 'PK' && $_SERVER['REMOTE_ADDR'] != '::1') { ?>
      <style>
         .swal-text {
            text-align: center;
         }
      </style>
      <script>swal("Alert!", "You are using non-US IP, please use the correct IP for payment", "error", { buttons: false, closeOnClickOutside: false, closeOnEsc: false });</script>
      <?php
   } ?>
   <div class="container terminal-wrapper ">
      <div class="page-header">
         <div class="row align-items-center">
            <div class="col-md-8">
               <h2 class="colorprimary">
                  <img class="img-responsive" src="<?php echo $cxm_merchants['brandlogo'] ?>" width="250px;">
                  <small style="font-size: 13px; padding: 0 0 0 33px;"><?php echo $cxm_merchants['brandName'] ?> Secure
                     Payment Terminal</small>
               </h2>
            </div>
            <div class="col-md-4">
               <h1
                  class="text-right text-uppercase <?php echo ($cxm_merchants['status'] == 'paid') ? 'text-success' : 'text-danger'; ?>">
                  <?php echo $cxm_merchants['status'] ?>
               </h1>
            </div>
         </div>
      </div>
      <?php if ($cxm_merchants['status'] == 'paid') { ?>
         
         <?php
      } ?>
      <form method="POST" class="validate form-horizontal" id="pay_form" >
         <?php if ($cxm_merchants['status'] == "due") { ?>

            <input type="hidden" name="customer_ip" value="<? echo getClientIp(); ?>">

            <input type="hidden" id="team_key" name="team_key" value="<?php echo $cxm_merchants['team_key'] ?>">
            <input type="hidden" id="brand_key" name="brand_key" class="enable-subscriptions"
               value="<?php echo $cxm_merchants['brand_key'] ?>">
            <input type="hidden" id="creatorid" name="creatorid" class="enable-subscriptions"
               value="<?php echo $cxm_merchants['creatorid'] ?>">
            <input type="hidden" id="agentid" name="agentid" class="enable-subscriptions"
               value="<?php echo $cxm_merchants['agent_id'] ?>">
            <input type="hidden" id="clientid" name="clientid" class="enable-subscriptions"
               value="<?php echo $cxm_merchants['clientid'] ?>">
            <input type="hidden" id="invoiceid" name="invoiceid" class="enable-subscriptions"
               value="<?php echo $cxm_merchants['invoice_key'] ?>">
            <input type="hidden" id="projectid" name="projectid" class="enable-subscriptions"
               value="<?php echo $cxm_merchants['project_id'] ?>">
            <input type="hidden" id="salesType" name="salestype" class="enable-subscriptions"
               value="<?php echo $cxm_merchants['sales_type'] ?>">
            <input type="hidden" id="payment_gateway" name="payment_gateway" class="enable-subscriptions"
               value="authorize">
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
            <input type="hidden" name="cxm_m_id"
               value="<?php echo cxmEncrypt($cxm_merchants['merchant']['id'], $sasPrivateKey); ?>">
            <input type="hidden" name="cxm_m_mode"
               value="<?php echo cxmEncrypt($cxm_merchants['merchant']['mode'], $sasPrivateKey); ?>">
            <input type="hidden" name="cxm_m_n"
               value="<?php echo cxmEncrypt($cxm_merchants['merchant']['merchant'], $sasPrivateKey); ?>">
            <input type="hidden" name="cxm_m_lid"
               value="<?php echo cxmEncrypt($cxm_merchants['merchant']['live_login_id'], $sasPrivateKey); ?>">
            <input type="hidden" name="cxm_m_key"
               value="<?php echo cxmEncrypt($cxm_merchants['merchant']['live_transaction_key'], $sasPrivateKey); ?>">
            <input type="hidden" id="card_type" name="card_type" value="">
            <?php
         } ?>
         <input type="hidden" name="amount" value="<?php


         $amount = ($cxm_merchants['total_amount'] != 0) ? $cxm_merchants['total_amount'] : $cxm_merchants['final_amount'];

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


               <?php if ($cxm_merchants['tax_amount'] != 0) { ?>
                  <div class="form-group">
                     <label class="col-md-3 control-label"><span
                           class="colordanger">*</span><?php echo $cxm_merchants['tax_percentage'] ?>% Tax</label>
                     <div class="col-md-9">
                        <div class="input-group">
                           <span class="input-group-addon"><?php echo $cxm_merchants['currency_symbol'] ?></span>
                           <input type="text" id="tax" name="tax" class="form-control" placeholder="0.00"
                              value="<?php echo $cxm_merchants['tax_amount'] ?>" readonly>
                        </div>
                     </div>
                  </div>
                  <div class="form-group">
                     <label class="col-md-3 control-label"><span class="colordanger">*</span>Net Amount</label>
                     <div class="col-md-9">
                        <div class="input-group">
                           <span class="input-group-addon"><?php echo $cxm_merchants['currency_symbol'] ?></span>
                           <input type="text" id="amount" name="amount" class="form-control" placeholder="0.00"
                              data-rule-required="true" data-rule-number="true"
                              value="<?php echo ($cxm_merchants['total_amount']) ? $cxm_merchants['total_amount'] : $cxm_merchants['final_amount'] ?>"
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
                        style="resize:none;"><?php echo $cxm_merchants['invoice_descriptione'] ?></textarea>
                  </div>
               </div>
               <hr class="visible-xs visible-sm">
               <h3 class="colorgray mt40 mb30">Your Information</h3>
               <div class="form-group">
                  <label class="control-label col-md-3"><span class="colordanger">*</span>Name</label>
                  <div class="col-md-9">
                     <input type="text" readonly id="name" name="name" class="form-control" placeholder="Name"
                        value="<?php echo $cxm_merchants['clientname'] ?>" data-rule-required="true">
                  </div>
               </div>
               <div class="form-group">
                  <label class="control-label col-md-3"><span class="colordanger">*</span>Email</label>
                  <div class="col-md-9">
                     <input type="text" readonly id="email" name="email" class="form-control" placeholder="Email"
                        value="<?php echo $cxm_merchants['clientemail'] ?>" data-rule-required="true"
                        data-rule-email="true">
                  </div>
               </div>
               <div class="form-group">
                  <label class="control-label col-md-3"><span class="colordanger">*</span>Phone</label>
                  <div class="col-md-9">
                     <input type="text" readonly id="email" name="phone" class="form-control" placeholder="Phone"
                        value="<?php echo $cxm_merchants['clientphone'] ?>" data-rule-required="true">
                  </div>
               </div>
            </div>
            <div class="col-md-6">
               <hr class="visible-xs visible-sm">
               <h3 class="colorgray mb30">
                  Zelle Payment Instructions

                  <div class="floatright">
                     <img src="images/credit-cards.jpg" class="">
                  </div>
               </h3>
               <div class="creditcard-content">
                  <?php
                  $zelle_amount = ($cxm_merchants['total_amount'] != 0) ? $cxm_merchants['total_amount'] : $cxm_merchants['final_amount'];
                  $zelle_email = 'zackk.ernie@gmail.com'; // You can replace this with a dynamic variable if available
                  ?>
                  
                  <div class="zelle-instructions-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 30px; margin-bottom: 25px; color: white; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                     <!-- <div class="text-center mb-4">
                        <i class="fa fa-money-bill-wave" style="font-size: 48px; color: #fff; margin-bottom: 15px;"></i>
                        <h3 style="color: #fff; font-weight: 600; margin: 0;">Zelle Payment Instructions</h3>
                     </div> -->
                     
                     <div style="background: rgba(255,255,255,0.2); border-radius: 8px; padding: 20px; margin-bottom: 20px; backdrop-filter: blur(10px);">
                        <div class="row" style="margin-bottom: 15px;">
                           <div class="col-md-4">
                              <div style="display: flex; align-items: center;">
                                 <i class="fa fa-dollar-sign" style="font-size: 24px; margin-right: 10px; color: #fff;"></i>
                                 <strong style="font-size: 16px;">Amount to Pay:</strong>
                              </div>
                           </div>
                           <div class="col-md-8">
                              <div style="background: rgba(255,255,255,0.3); border-radius: 6px; padding: 10px 15px; display: inline-block;">
                                 <span style="font-size: 24px; font-weight: 700; color: #fff;">
                                    <?php echo $cxm_merchants['currency_symbol'] . number_format($zelle_amount, 2); ?>
                                 </span>
                              </div>
                           </div>
                        </div>
                        
                        <div class="row" style="border-top: 1px solid rgba(255,255,255,0.3); padding-top: 15px;">
                           <div class="col-md-4">
                              <div style="display: flex; align-items: center;">
                                 <i class="fa fa-envelope" style="font-size: 24px; margin-right: 10px; color: #fff;"></i>
                                 <strong style="font-size: 16px;">Zelle Email:</strong>
                              </div>
                           </div>
                           <div class="col-md-8">
                              <div style="background: rgba(255,255,255,0.3); border-radius: 6px; padding: 10px 15px; display: inline-block;">
                                 <span style="font-size: 18px; font-weight: 600; color: #fff; word-break: break-all;">
                                    <?php echo htmlspecialchars($zelle_email); ?>
                                 </span>
                              </div>
                           </div>
                        </div>
                     </div>
                     
                     <div style="background: rgba(255,255,255,0.15); border-radius: 8px; padding: 20px; border-left: 4px solid #fff;">
                        <p style="margin: 0; font-size: 15px; line-height: 1.6; color: #fff;">
                           <i class="fa fa-info-circle" style="margin-right: 8px;"></i>
                           Please send the payment via Zelle to the above email address and upload your payment receipt/screenshot below.
                        </p>
                     </div>
                  </div>
                  
                  <!-- File Upload Section -->
                  <!-- <div class="form-group" style="margin-top: 20px;">
                     <label class="control-label" style="font-weight: 600; margin-bottom: 10px; display: block;">
                        <i class="fa fa-upload" style="margin-right: 8px;"></i>
                        Upload Payment Receipt/Screenshot
                     </label>
                     <div style="border: 2px dashed #ddd; border-radius: 8px; padding: 20px; text-align: center; background: #f9f9f9; transition: all 0.3s;">
                        <input type="file" id="zelle_receipt" name="zelle_receipt" accept="image/*,.pdf" 
                               style="display: none;" onchange="handleFileSelect(this)">
                        <label for="zelle_receipt" style="cursor: pointer; display: inline-block;">
                           <i class="fa fa-cloud-upload-alt" style="font-size: 48px; color: #667eea; margin-bottom: 10px; display: block;"></i>
                           <span style="color: #667eea; font-weight: 600; display: block; margin-bottom: 5px;">Click to upload</span>
                           <span style="color: #999; font-size: 13px;">PNG, JPG, PDF up to 10MB</span>
                        </label>
                        <div id="file-name" style="margin-top: 10px; color: #28a745; font-weight: 600; display: none;">
                           <i class="fa fa-check-circle"></i> <span id="file-name-text"></span>
                        </div>
                     </div>
                  </div> -->
                  
                  <style>
                     .zelle-instructions-box:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 15px 40px rgba(0,0,0,0.2);
                        transition: all 0.3s ease;
                     }
                     
                     label[for="zelle_receipt"]:hover {
                        opacity: 0.8;
                     }
                     
                     #zelle_receipt:focus + label,
                     label[for="zelle_receipt"]:hover {
                        background: #f0f0f0;
                     }
                     
                     @media (max-width: 768px) {
                        .zelle-instructions-box {
                           padding: 20px !important;
                        }
                     }
                  </style>
                  
                  <script>
                     function handleFileSelect(input) {
                        if (input.files && input.files[0]) {
                           var fileName = input.files[0].name;
                           var fileSize = (input.files[0].size / 1024 / 1024).toFixed(2); // Size in MB
                           
                           document.getElementById('file-name-text').textContent = fileName + ' (' + fileSize + ' MB)';
                           document.getElementById('file-name').style.display = 'block';
                           
                           // Validate file size (10MB limit)
                           if (input.files[0].size > 10 * 1024 * 1024) {
                              alert('File size exceeds 10MB limit. Please choose a smaller file.');
                              input.value = '';
                              document.getElementById('file-name').style.display = 'none';
                           }
                        }
                     }
                  </script>
               </div>
               <input type="hidden" name="invoice_id" value="<?= $_GET['invoicekey'] ?>">




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
                        <?php if ($cxm_merchants['status'] == 'due') { ?>
                           <button id="pay_button" type="button" class="btn btn-lg btn-primary submit-button mb20">
                              <span class="total show">Total:
                                 <?php echo $cxm_merchants['currency_symbol'] ?><span><?php echo ($cxm_merchants['total_amount'] != 0) ? $cxm_merchants['total_amount'] : $cxm_merchants['final_amount'] ?></span>
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

      <input type="hidden" class="authorize_active" value="<?= $cxm_merchants['authorize_active']['merchant'] ?>"
         name="merchant" />

     <div class="alert alert-warning text-center" role="alert">On your bank statement the descriptor should be <b>
               Zedexsolutions LLC
            </b> as Merchant Name.</div>
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
   if ($cxm_merchants['team_key'] == 489362) {
      if (!isMobile()) {
         include $chatPath;
      } else {
         echo '<p class="user from mobile."></p>';
      }
   } else {
      include $chatPath;
   }
}

// Define URL variables for JavaScript
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$currentUrl = $protocol . $domain . $_SERVER['REQUEST_URI'];
$invoice_url = str_replace('checkout.php', 'index.php', $currentUrl);
?>


<script>
    const stripe = Stripe('pk_live_51OlKHdDiItQuJkczJfnL5XlKhWfjlUPVi7zIH5T6vKSWGuDLX8XODTzKRI4oSNOdnmd0psCCmP7zK3e3m6oQrV1H00OVAQ9Qt6');
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
            
            const response = await fetch('stripe_process.php', {
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
                  window.location.href = 'https://crm.trivexlabs.com/stripe_response/?type=success&invoiceid=<?= $_GET['invoicekey'] ?>&invoicekey=<?= $_GET['invoicekey'] ?>&payment_intent=' + paymentIntent.id + '&brandid=<?=$cxm_merchants['brand_key']?>&session_id=<?=$stripe_session_id?>&url=<?= urlencode(preg_replace('#^(https?://)www\.#i', '$1', $invoice_url)) ?>';
                  
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