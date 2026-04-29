<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="icon" href="<?php echo WEBSITE_FAV;?>" sizes="16x16">
    <link href="https://fonts.googleapis.com/css?family=Poppins:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap" rel="stylesheet">
    <link href="assets/bootstrap.min.css" rel="stylesheet">
	<link href="assets/helpers.css" rel="stylesheet">
	<link href="assets/app.css" rel="stylesheet">
	<style>
	    .home-page {background-color:#f1f1f1;}
	    .home-page header{background-color:#fff; padding:30px 0;}
	    .home-page .btn{border-radius:18px;}
	    .home-page .form-pay {background-color:#fff; padding:20px; box-shadow:0px 0px 20px #ddd;}
	    .home-page .form-pay-box h3 {margin-top:0;}
	    .home-page footer{background-color:#fff; padding:20px 0;}
	    .home-page footer a{color:#333;}
	</style>
  </head>
  <body class="home-page">
    <header>
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-3">
            <a href="../index.php" class="header-logo">
                <img src="<?php echo WEBSITE_LOGO;?>" alt="">
            </a>
          </div>
          <div class="col-md-9">
            <div class="phoneInfo alignright">
                <a href="#" class="btn btn-info chat">Start Live Chat</a>
                <a href="<?php echo PHONE_HREF ?>" class="btn btn-info"><i class="tell-icon for-sprite"></i> Call 24/7: <span><?php echo PHONE ?></span></a>
              <ul class="hidden">
                <li>
                  <a href="javascript:;" class="chat"><i class="chat-icon for-sprite"></i>
                  Start Live Chat</a>
                </li>
                <li>
                  <a href="<?php echo PHONE_HREF ?>"><i class="tell-icon for-sprite"></i> Call 24/7: <span><?php echo PHONE ?></span></a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </header>