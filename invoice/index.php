<?php

include '../include/sasFunction.php';

// paid invoice data
if (isset($_GET['invoicekey']) && $_GET['invoicekey'] != "")
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiBaseUrl . '/show_paid_invoice/' . $_GET['invoicekey'] . "?" . mt_rand());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Set a timeout to avoid hanging the script
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',  // Adjust this header as necessary
        // Add any other headers you need (e.g., Authorization, User-Agent, etc.)
    ]);
    
    $response = curl_exec($ch);
    $cxmMerchantArr = json_decode($response, true);
  
    // $cxmMerchantJson = file_get_contents($apiBaseUrl . '/show_paid_invoice/' . $_GET['invoicekey']."?".mt_rand());
    // $cxmMerchantArr = json_decode($cxmMerchantJson, true);

    // echo '<pre>';
    //     print_r($cxmMerchantArr['brandData']['brand_url']);
    //     exit;



    $cxm_merchants = array();
    if (!empty($cxmMerchantArr['data']))
    {
        $cxm_merchants = $cxmMerchantArr['data'];

    }
    else
    {
        $base_url = $cxmMerchantArr['brandData']['brand_url']."/checkout/index.php?invoicekey=" . $_GET['invoicekey'];
        header('Location: ' . $base_url);
    }
}
else
{
    $base_url = $cxmMerchantArr['brandData']['brand_url'];
    header('Location: ' . $base_url);
}

$currencySym = "&#36;";

if ($country == "United Kingdom")
{
    $currencySym = "&#163;";
}

$symbols = array(
    'USD' => '$',  # US Dollar
    'EUR' => '€',  # Euro
    'GBP' => '£',  # British Pound Sterling
    'JPY' => '¥',  # Japanese Yen
);


$brandData = $cxmMerchantArr['brandData'];


$currencySym = $symbols[$cxmMerchantArr['invoiceData']['cur_symbol']];


// echo '<pre>';
// print_r();
// exit;

    
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" href="<?=$brandData['logo'] ?>" type="image/x-icon" />
<title>Payment Invoice - <?=$brandData['name'] ?></title>
<meta name="author" content="<?=$brandData['name'] ?>">

<!-- Web Fonts
======================= -->
<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Poppins:100,200,300,400,500,600,700,800,900' type='text/css'>
<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.2.0/css/font-awesome.css' type='text/css'>
<!-- Stylesheet
======================= -->
<link rel="stylesheet" type="text/css" href="style/bootstrap.min.css"/>
<!--<link rel="stylesheet" type="text/css" href="style/all.min.css"/>-->
<link rel="stylesheet" type="text/css" href="style/stylesheet.css"/>

<style>
    @media print {
        #launcher {display:none;}
        body {background-color:white;}
    }
    
    #background{
        /*position:relative;*/
        text-align: center;
    }
    #bg-text
    {
        color:rgba(25,135,84,0.2);
        font-size:120px;
        transform:rotate(300deg);
        -webkit-transform:rotate(300deg);
        position: absolute;
        margin: auto;
        left: 0;
        right: 0;
        z-index: 1;
        top:30%;
    }
    a.btn.btn-light.border.text-black-50.shadow-none {z-index: 2;}
    
#signatureCanvas {
    border: 1px solid #000;
    position: relative;
    z-index: 999;
}
.btn_sign
{
    color:white;
    background-color: #64b5ff;
    border-color: #64b5ff;
}
.btn-info:hover {
    color:white;
    background-color: #64b5ff;
    border-color: #64b5ff;
}
.signature_div {
    margin: 0 auto;
    text-align: center;
 
}

@media only screen and (max-width: 768px) {
  #signatureCanvas {
         width: 300px !important;
  }
}
  </style>
</head>
<body>
    
<!-- Container -->
<ul class="design_notifications_toaster"></ul>
<div class="container-fluid invoice-container">
  <!-- Header -->
  <header>
  <div class="row align-items-center">
    <div class="col-sm-7 text-center text-sm-start mb-3 mb-sm-0">
      <img id="logo" src="<?=$brandData['logo'] ?>"  width="250"/>
    </div>
    <div class="col-sm-5 text-center text-sm-end">
      <h4 class="text-7 mb-0">Invoice</h4>
    </div>
  </div>
  <hr>
  </header>
  
  <!-- Main Content -->
  <main>
      <div id="background">
        <p id="bg-text">PAID</p>
    </div>
  <div class="row">
    <div class="col-sm-6"><strong>Date:</strong> <?php echo date('d-M-Y', strtotime($cxm_merchants['created_at'])); ?></div>
    <div class="col-sm-6 text-sm-end"> 
    <strong>Invoice Created On :</strong> <?php echo date('d-M-Y', strtotime($cxmMerchantArr['invoiceData']['created_at'])); ?> <br>
    <strong>Invoice No:</strong> <?php echo $cxm_merchants['invoice_id'] ?>

  </div>
    
  </div>
  <hr>
    
  <div class="row">
    <div class="col-sm-6 text-sm-end order-sm-1"> <strong>Pay To:</strong>
      <address>
        <?=$brandData['name'] ?>,<br>
        <?php
          $parse = parse_url($brandData['brand_url']);
          echo 'info@' . $parse['host'];
                    
          // echo '<pre>';
          // print_r($cxm_merchants);
          // exit;
        ?>
      </address>
    </div>
    <div class="col-sm-6 order-sm-0"> <strong>Invoiced To:</strong>
      <address>
      <?php echo $cxm_merchants['name'] ?><br />
      <?php echo $cxm_merchants['email'] ?><br />
      <?php echo $cxm_merchants['address'] ?><br />
      </address>
    </div>
  </div>
  
  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table mb-0">
    <thead class="card-header">
          <tr>
      <td class="col-4"><strong>Description</strong></td>
            <td class="col-2 text-center"><strong>Rate</strong></td>
            <td class="col-2 text-end"><strong>Amount</strong></td>
          </tr>
        </thead>
          <tbody>
            <tr>
              <td class="col-4 text-1" style="vertical-align: middle;"><?php echo $cxm_merchants['payment_notes'] ?></td>
              <td class="col-2 text-center" style="vertical-align: middle;" ><?php echo $currencySym . number_format($cxmMerchantArr['invoiceData']['total_amount'], 2); ?></td>
        <td class="col-2 text-end" style="vertical-align: middle;"><?php echo $currencySym . number_format($cxmMerchantArr['invoiceData']['total_amount'], 2); ?></td>
            </tr>
          </tbody>
      <tfoot class="card-footer">
      <tr>
              <td colspan="2" class="text-end border-bottom-0"><strong>Total:</strong></td>
              <td class="text-end border-bottom-0"><?php echo $currencySym . number_format($cxmMerchantArr['invoiceData']['total_amount'], 2); ?></td>
            </tr>
      </tfoot>
        </table>



      </div>
    </div>
  </div>
<br>

<div class="signature_div">
    <canvas id="signatureCanvas" width="400" height="200"></canvas><br>
    <button class="btn btn-danger btn_sign" id="clearButton">Clear Signature</button>
    <button class="btn btn-info btn_sign" id="submitButton">Submit Signature</button>

</div>
  <br>

  <div class="alert alert-warning text-center" role="alert">On your bank statement the descriptor should be <b><?php echo $cxmMerchantArr['merchant']['merchant'] ?></b> as Merchant Name.</div>
      

  </main>
  <!-- Footer -->
  <footer class="text-center mt-4">
  <p class="text-1"><strong>NOTE :</strong> This is computer generated receipt and does not require physical signature.</p>
   <div class="btn-group btn-group-sm d-print-none"> 
    <a href="javascript:window.print()" class="btn btn-light border text-black-50 shadow-none">
    <i class="fa fa-download"></i> Download</a>
    <!--<a href="" class="btn btn-light border text-black-50 shadow-none"><i class="fa fa-download"></i> Download</a>-->
  </div>
  </footer>
</div>
<script
  src="https://code.jquery.com/jquery-3.6.4.min.js"
  integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8="
  crossorigin="anonymous"></script>



<?php


function isMobile() {
   return preg_match('/(android|iphone|ipad|ipod|blackberry|opera mini|iemobile|mobile)/i', $_SERVER['HTTP_USER_AGENT']);
}

$chatPath = './../../include/chat-code.php';

// Check if '../include/chat-code.php' exists, if not, use '../includes/chat-code.php'
if (!file_exists($chatPath)) {
    $chatPath = './../../includes/chat-code.php';
}

if ($_SERVER['REMOTE_ADDR'] != '::1') {
   if ($cxm_merchants['team_key'] == 489362) {
      if (!isMobile()) {
         include __DIR__ . $chatPath;
      } else {
         echo '<p class="user from mobile."></p>';
      }
   } else {
      include __DIR__ . $chatPath;
   }
}
?>





<script>

  
var API_URL = "https://tgcrm.net/api/" ;

// if(window.location.origin == 'https://localhost')
// {
//    API_URL ="https://development.tgcrm.net/api/" ;
// }


  const BrandID = '<?=$cxmMerchantArr['brandData']['brand_key']?>'
  /// Tracking api.
  setTimeout(function()
    {
        $.ajax({
            type:"POST",
            url: API_URL+"create-tracking-ip/"+BrandID,
            data:{
                url:window.location.href,
            },success:function(s){
                
                // console.log('tracking success',s)
                

            }
        });
    },3000)



// sending request for split payment
// setTimeout(function(){$.ajax({type:"GET",url:"https://development.tgcrm.net/api/pay-now-split-payments",success:function(t){console.log("t",t)}})},15e3);

// setTimeout(function(){$.ajax({type:"GET",url:"https://development.tgcrm.net/api/pay-now-split-payments",success:function(t){console.log("t",t)}})},30e3);
// sending request for split payment


</script>
<script>
  $(document).ready(function() {
    var canvas = document.getElementById("signatureCanvas");
    var context = canvas.getContext("2d");
    var drawing = false;

    // Event listeners for drawing
    $(canvas).on("mousedown touchstart", function(e) {
      e.preventDefault(); // Prevent default touch behavior
      drawing = true;
      var mouseX = e.pageX || e.originalEvent.touches[0].pageX;
      var mouseY = e.pageY || e.originalEvent.touches[0].pageY;
      context.beginPath();
      context.moveTo(mouseX - canvas.offsetLeft, mouseY - canvas.offsetTop);
    });

    $(canvas).on("mousemove touchmove", function(e) {
      e.preventDefault(); // Prevent default touch behavior
      if (drawing) {
        var mouseX = e.pageX || e.originalEvent.touches[0].pageX;
        var mouseY = e.pageY || e.originalEvent.touches[0].pageY;
        context.lineTo(mouseX - canvas.offsetLeft, mouseY - canvas.offsetTop);
        context.stroke();
      }
    });

    $(canvas).on("mouseup touchend", function() {
      drawing = false;
    });

    // Clear signature
    $("#clearButton").on("click", function() {
      context.clearRect(0, 0, canvas.width, canvas.height);
    });


    // Submit button (you can customize this part)
    $("#submitButton").on("click", function() {
      // Add your code to submit the form or perform any other action
      console.log("Form submitted");
      var signatureData = canvas.toDataURL(); // You can send this data to the server or use it as needed
      console.log("Generated Signature Data: ", signatureData);
    //   api/add-signature-to-invoice
        $.ajax({
            type:"POST",
            url: API_URL+"add-signature-to-invoice",
            data:{
                invoice_id:'<?=$_GET['invoicekey']?>',
                signature:signatureData,
            },success:function(s){
                
                console.log('signature_res',s)
                createToast('success',s.success);

                context.clearRect(0, 0, canvas.width, canvas.height);
            }
        });

    });

    // Reset button
    $("#resetButton").on("click", function() {
      // Reset the canvas and any other form elements as needed
      context.clearRect(0, 0, canvas.width, canvas.height);
      console.log("Form reset");
    });
  });




        // Nofitication Start.
        setTimeout(function(){
         // console.log('5 secs')
         $('head').append('<link rel="stylesheet" type="text/css" href="https://api.tgcrm.net/toaster.css">');
         $('head').append('<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">');
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

</script>

<style>
    .signature_div .btn {
        font-size: 13px;
        padding: 6px 10px;
        margin: 15px 0 0 10px;
        outline: none;
        box-shadow: none;
        border: 0;
    }
 
    .signature_div .btn.btn-danger{
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .signature_div .btn:focus {
        outline:none;
        box-shadow:none;
    }

</style>

</body>
</html>
