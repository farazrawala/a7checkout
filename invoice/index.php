<?php

include '../include/sasFunction.php';

// paid invoice data
if (isset($_GET['invoicekey']) && $_GET['invoicekey'] != "")
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiBaseUrl . '/show_paid_invoice/' . $_GET['invoicekey'] . "?" . mt_rand());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
    ]);
    
    $response = curl_exec($ch);
    $cxmMerchantArr = json_decode($response, true);

    $cxm_merchants = array();
    if (!empty($cxmMerchantArr['data']))
    {
        $cxm_merchants = $cxmMerchantArr['data'];
    }
    else
    {
        $brand_url = preg_replace('#^(https?://)www\.#i', '$1', $cxmMerchantArr['brandData']['brand_url']);
        $base_url = $brand_url."/checkout/index.php?invoicekey=" . $_GET['invoicekey'];
        header('Location: ' . $base_url);
    }
}
else
{
    $base_url = preg_replace('#^(https?://)www\.#i', '$1', $cxmMerchantArr['brandData']['brand_url']);
    header('Location: ' . $base_url);
}

$symbols = array(
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£',
    'JPY' => '¥',
);

$brandData = $cxmMerchantArr['brandData'];
$currencySym = $symbols[$cxmMerchantArr['invoiceData']['cur_symbol']];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="shortcut icon" href="<?php echo $brandData['logo']; ?>" type="image/x-icon" />
<title>Payment Invoice - <?php echo $brandData['name']; ?></title>
<meta name="author" content="<?php echo $brandData['name']; ?>">

<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Poppins'>
<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.2.0/css/font-awesome.css'>

<link rel="stylesheet" href="style/bootstrap.min.css"/>
<link rel="stylesheet" href="style/toaster.css"/>
<link rel="stylesheet" href="style/stylesheet.css"/>

<style>
#signatureCanvas { border:1px solid #000; }
</style>

</head>
<body>

<div class="container-fluid invoice-container">

<header>
<div class="row">
<div class="col-sm-7">
<img src="<?php echo $brandData['logo']; ?>" width="250"/>
</div>
<div class="col-sm-5 text-end">
<h4>Invoice</h4>
</div>
</div>
<hr>
</header>

<main>

<p><strong>Date:</strong> <?php echo date('d-M-Y', strtotime($cxm_merchants['created_at'])); ?></p>

<p><strong>Invoice No:</strong> <?php echo $cxm_merchants['invoice_id']; ?></p>

<p><strong>Pay To:</strong> <?php echo $brandData['name']; ?></p>

<p><strong>Invoiced To:</strong> <?php echo $cxm_merchants['name']; ?></p>

<table class="table">
<tr>
<th>Description</th>
<th>Amount</th>
</tr>

<tr>
<td><?php echo $cxm_merchants['payment_notes']; ?></td>
<td><?php echo $currencySym . number_format($cxmMerchantArr['invoiceData']['total_amount'],2); ?></td>
</tr>

</table>

</main>

</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<script>
const API_URL = "https://crm.trivexlabs.com/api/";

const BrandID = "<?php echo $cxmMerchantArr['brandData']['brand_key']; ?>";

$(document).ready(function(){

$("#submitButton").click(function(){

var canvas = document.getElementById("signatureCanvas");
var signatureData = canvas.toDataURL();

$.ajax({
type:"POST",
url: API_URL+"add-signature-to-invoice",
data:{
invoice_id:"<?php echo $_GET['invoicekey']; ?>",
signature:signatureData
},
success:function(res){
console.log(res);
}
});

});

});
</script>

</body>
</html>