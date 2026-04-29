<?php

include '../include/sasFunction.php';

// paid invoice data
if (isset($_GET['invoicekey']) && $_GET['invoicekey'] != "")
{

  
    $cxmMerchantJson = file_get_contents($apiBaseUrl . '/show_paid_invoice/' . $_GET['invoicekey']);
    $cxmMerchantArr = json_decode($cxmMerchantJson, true);

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
// print_r($cxmMerchantArr['signatures']);
// exit;

$team_key = $cxmMerchantArr['data']['team_key'];
$parse = parse_url($brandData['brand_url']);
$brand_email =  'info@' . $parse['host'];
$brand_name =  $brandData['name'];
$signatures = $cxmMerchantArr['signatures'][count($cxmMerchantArr['signatures'])-1];
    



// echo '<pre>';
// print_r($signatures);
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
      <!-- <h4 class="text-7 mb-0">Invoice</h4> -->
    </div>
  </div>
  <hr>
  </header>
  
  <!-- Main Content -->
  <main>
      
  <div class="row">
    <div class="col-sm-12"><h1 style="text-align:center"><strong>Terms & Conditions </strong></h1></div>
    
  </div>
  <hr>
    
  <div class="row justify-content-center">
    
    <div class="col-sm-12"> 
      <h3 class="heading"><strong>Revision Policy </strong></h3>
      <p class="paragraph">
        Revisions are contingent on the package selected. Clients can approach us for unlimited free amendments and we will revise their design with no extra charges, given that the design and concept remain intact. Revision turnaround time will be 48 hours.
      </p>
    </div>


    <div class="col-sm-12"> 
      <h3 class="heading"><strong>My Account </strong></h3>
      <p class="paragraph">
      The My Account section provides a convenient means of communication. It is solely your responsibility to regularly check this section for any questions, queries, concerns, or instructions for the designer. Failure to actively monitor the My Account section may impede your ability to pursue a refund. If you are unsure of how to utilize the My Account area, please feel free to contact customer support at any time for prompt assistance.</p>
    </div>


    <div class="col-sm-12"> 
      <h3 class="heading"><strong>Quality Assurance Policy</strong></h3>
      <p class="paragraph">
      In order to provide you with complete satisfaction, our designers are instructed not to deviate from the specifications provided by the client in the order form.
The designs are crafted after adequate and thorough research to ensure quality and uniqueness.</p>
</div>

<div class="col-sm-12"> 
      <h3 class="heading"><strong>100% Satisfaction Guarantee</strong></h3>
      <p class="paragraph">
      We revamp the requested design and continue overhauling it until you are 100% fulfilled (depending upon your package).
    
      </p>
    </div>



      <div class="col-sm-12"> 
      <h3 class="heading"><strong>Delivery Policy</strong></h3>
      <p class="paragraph">
      All design order files are delivered to My Account as per the date specified on the “Order Confirmation”. An e-mail is also sent to inform the client about their design order delivery made to their specific account area. All policies pertaining to revision & refund are subject to date and time of design order delivered to client’s account area.
      <br>
        <br>
        All design order files are delivered to “My Account” as per the date specified on the “Order Confirmation”. An e-mail is also sent to inform the client about their design order delivery made to their specific account area. All policies pertaining to revision & refund are subject to date and time of design order delivered to client’s account area.
        <br>
        <br>
        All customized design orders are delivered via email within 5 to 7 days after receipt of order.
        <br>
        We offer a RUSH DELIVERY service through which you can have your first logo within 24 hours by paying just $100 additional! For further help, get in touch with our customer support department.

        </p>
    </div>

    
    <div class="col-sm-12"> 
      <h3 class="heading"><strong>Record Maintenance</strong></h3>
      <p class="paragraph">

      We keep your final design archived after we deliver your final files. If you wish to receive the final files again, we can email that upon request.
      </p></div>


    <div class="col-sm-12"> 
      <h3 class="heading"><strong>Customer Support</strong></h3>
      <p class="paragraph">
        We offer Online Customer Support to address your questions and queries.
        <br>
        You can get in touch with us at any time and we promise a prompt reply.
</p>
    </div>

    <div class="col-sm-12"> 
      <h3 class="heading"><strong>Correspondence Policy</strong></h3>
      <p class="paragraph">
      You concur that <?=$brand_name?> is not at risk for any correspondence from email address (es) other than the ones took after by our own particular area i.e. 
      <?=$brand_email?> or/and any toll-free number that is not specified on our site. <?=$brand_name?> should not be considered in charge of any damage(s) brought about by such correspondence. We just assume the liability of any correspondence through email address (es) under our own space name or/and by means of toll-free number i.e. as of now specified on <?=$brand_name?> Website.
        <br>
        <br>
        </p>    
    
    </div>



    <div class="col-sm-12"> 
      <h3 class="heading"><strong>100% Unique Design Guarantee</strong></h3>
      <p class="paragraph">
      
        At <?=$brand_name?> we promise that all of our logos are produced from scratch. We will provide you with a logo that is proficient and in complete compliance with your design brief.
        </p>    
    
    </div>


    <div class="col-md-4 col-md-offset-4" style="text-align: center;"> 
        
    <img src="<?=$signatures['signature']?>" width="200" height="100" />
    <hr/>
    <p>Signature</p>
    </div>






  </div>
  
<br>


  <br>
  </main>
  
</div>
<script
  src="https://code.jquery.com/jquery-3.6.4.min.js"
  integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8="
  crossorigin="anonymous"></script>
<?php
if ($_SERVER['REMOTE_ADDR'] != '::1')
{
    include '../../include/chat-code.php';
}
?>







<script>

  
var API_URL = "https://crm.trivexlabs.com/api/" ;

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
