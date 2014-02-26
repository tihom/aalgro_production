<!DOCTYPE html>
<html>
<head>
<meta http-equiv="cache-control" content="max-age=0" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />
<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
<meta http-equiv="pragma" content="no-cache" />
<link href='http://fonts.googleapis.com/css?family=Oxygen:400,700' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css" href="css/font-awesome.css" />
<style type="text/css">

body {
	font-family: "Oxygen","Lucida Sans Unicode","Lucida Grande",Tahoma;
	font-size: 14px;
	color: #333333;
	margin: 0 auto 0 auto;
}

#whole-document {
	overflow: hidden;
	position: relative;
	width: 960px;
	margin: 0 auto;
}

#header {
	overflow: hidden;
	margin: 25px 0 70px 0;
}

#header-1 {
	border-radius: 5px 0 0 5px;
	float: left;
	width: 461px;
	background-color: white;
	padding: 10px 0;
	color: white;
}

.header-section {
	float: left;
	box-sizing: border-box;
	-moz-box-sizing: border-box;
	text-align: center;
	color: white;
	padding: 10px 0;
	cursor: pointer;
	background-color: #79c500;
	background-image: -webkit-linear-gradient(to bottom, #79c500, #5dae00);
	background-image: -moz-linear-gradient(to bottom, #79c500, #5dae00);
	background-image: linear-gradient(to bottom, #79c500, #5dae00);
}

.header-section-active {
	background-color: #58cfff !important;
	background-image: -webkit-linear-gradient(to bottom, #58cfff, #139de8) !important;
	background-image: -moz-linear-gradient(to bottom, #58cfff, #139de8) !important;
	background-image: linear-gradient(to bottom, #58cfff, #139de8) !important;
}

#header-2 {
	width: 160px;
	border-radius: 3px 0 0 3px;
	border-right: 1px solid rgb(93, 174, 0);
	border-right: 1px solid rgba(93, 174, 0, 0.80);
}

#header-3 {
	width: 180px;
	border-right: 1px solid rgb(93, 174, 0);
	border-right: 1px solid rgba(93, 174, 0, 0.80);
}

#header-4 {
	width: 159px;
	border-radius: 0 3px 3px 0;
}

#logo-container {
	position: absolute;
	top: 26px;
	left: 0px;
}

#header-logo {
	width: 162px;
}

.page-sections-container {
	position: relative;
}

.page-header {
	font-size: 15px;
	margin: 0 0 20px 0;
	color: #5dae00;
	font-size: 15px;
	font-weight: 700;
}

.page-section {
	width: 450px;
	box-sizing: border-box;
	-moz-box-sizing: border-box;
	line-height: 1.75;
	position: absolute;
	font-size: 14px;
	color: #000000;
}

.page-section-header {
	padding: 0px;
	font-weight: 700;
	color: #139de8;
	font-size: 14px;
	margin: 0 0 5px 0;
}

.page-section-header div:first-child {
	display: table;
	padding: 5px 5px 5px 0px;
}

.page-section-header-highlighted {
	background-color: #58cfff;
	background-image: -webkit-linear-gradient(to bottom, #58cfff, #139de8);
	background-image: -moz-linear-gradient(to bottom, #58cfff, #139de8);
	background-image: linear-gradient(to bottom, #58cfff, #139de8);
	color: white;
	border-radius: 3px;
	padding-left: 5px !important;
}

.page-section-content {
	text-align: justify;
	border-radius: 5px;
	font-size: 13px;
}

.ul-header {
	font-weight: 700;
	margin: 0 0 5px 0;
	color: #333333;
}

#services-container {
	display: none;
	position: relative;
}

#services-1 {
	top: 0px;
	left: 0px;
}

#services-2 {
	top: 0px;
	left: 510px;
}

ul {
	margin: 0px;
	padding: 0 0 0 15px;
}

li {
	margin: 0 0 5px 0;
}

#contact-container {
	display: none;
}

.email {
	color: #139DE8;
	font-weight: 700;
}

#header-2-hover,#header-3-hover {
	position: absolute;
	width: 160px;
	box-sizing: border-box;
	-moz-box-sizing: border-box;
	display: none;
	padding: 2px 0 0 0; 
}

#header-3-hover {
	width: 180px;
}

#header-2-hover div,#header-3-hover div {
	display: block;
	font-family: inherit;
	font-size: 13px;
	padding: 7px;
	margin: 0 0 1px 0;
	color: white;
	background-color: #79c500;
	background-image: -webkit-linear-gradient(to bottom, #79c500, #5dae00);
	background-image: -moz-linear-gradient(to bottom, #79c500, #5dae00);
	background-image: linear-gradient(to bottom, #79c500, #5dae00);
	border-radius: 2px;
	cursor: pointer;
}

#header-3-hover div {
	border-radius: 0px !important;	
}

#header-4-hover {
	position: absolute;
	width: 159px;
	text-align: right;
	letter-spacing: 1px;
	font-family: Verdana;
	box-sizing: border-box;
	-moz-box-sizing: border-box;
	color: #5DAE00;
}

#header-border {
	position: absolute;
	top: 25px;
	padding-bottom: 25px;
	width: 100%;
	border-bottom: 1px solid #e9e9e9;
	z-index: -1;
}

.contact-header {
	background-color: #58cfff;
	background-image: -webkit-linear-gradient(to bottom, #58cfff, #139de8);
	background-image: -moz-linear-gradient(to bottom, #58cfff, #139de8);
	background-image: linear-gradient(to bottom, #58cfff, #139de8);
	padding: 5px;
	color: white;
	border-radius: 3px;
	margin: 0 10px 0 0;
	display: inline-block;
	width: 170px;
	text-align: center;
	font-weight: 700;
}

</style>

<script type="text/javascript" src="jquery-1.8.2.min.js"></script>
</head>

<body>
<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	ga('create', 'UA-45319357-1', 'aalgro.com');
	ga('send', 'pageview');
</script>

<div id="whole-document">
	<div id="header">
		<div id="header-1">&nbsp;</div>
		<div class="header-section header-section-active" id="header-2">About Us</div>
		<div class="header-section" id="header-3">Our Clients</div>
		<div class="header-section" id="header-4">Contact Us</div>
	</div>
	<div id="logo-container"><img src="img/aalgro.png" id="header-logo" /></div>
	<div class="page-container" id="about-us-container">
		<div class="page-header">ABOUT US</div>
		<div class="page-sections-container">
			<div class="page-section" id="about-us-1">
				<div class="page-section-header"><div>WHO WE ARE</div></div>
				<div class="page-section-content">We are Wholesalers & Distributors of Fresh Fruits and Vegetables. We believe in Technology enabled Supply Chain and Distribution Network right from the Producers&rsquo; Farm Gate through our Trade Suppliers to our Retail Partners and Wholesale Buyers.<br /><br />We also believe in continuously building strong relationship with our business partners. With the help of technology enabled supply chain and distribution infrastructure, strong relationship with our business partners, we continuously thrive to develop deep know-how in each step of supply chain including Harvest, Procure, Grade, Pack, Store & Deliver with the aim to service our customers better.<br /><br />We currently have operations only in Bangalore.</div>
			</div>	
			<div class="page-section" id="about-us-2">
				<div class="page-section-header"><div>BUYING SOURCES</div></div>
				<div class="page-section-content">Our Customers have planned & timed demands as well as unplanned & immediate demands. For this, we buy from various sources and have strong partnership across the Fruits and Vegetable Supply Chain. This includes Producers at Farm Gate, Producers&rsquo; Group & Associations, Own & Others Collection Centres, APMC regulated FnV Mandis & Other Market Yards including Safal.</div>
			</div>
			<div class="page-section" id="about-us-3">
				<div class="page-section-header"><div>LICENSE & COMPLIANCE</div></div>
				<div class="page-section-content">We are license holders in APMC Market Yards for FnV in Bangalore and are fully compliant in our trading.</div>
			</div>
			<div class="page-section" id="about-us-4">
				<div class="page-section-header"><div>RANGE</div></div>
				<div class="page-section-content">Our Product Range includes most kinds & varieties of Fresh Fruit and Vegetables.</div>
			</div>
		</div>
	</div>
	<div class="page-container" id="services-container">
		<div class="page-header">OUR CLIENTS</div>
		<div class="page-sections-container">
			<div class="page-section" id="services-1">
				<div class="page-section-header"><div>RETAILERS</div><div style="margin-top:5px;font-weight:400;font-size:14px;">SUPER MARKETS, HYPER MARKETS, INDIVIDUAL STORES</div></div>
				<div class="page-section-content">
					<div class="ul-header">Services</div>
					<ul>
						<li>Diligent Sorting, Grading along with Stringent Quality Control</li>
						<li>Logistics - Delivery to Retailers&rsquo; Warehouse or Individual Stores</li>
						<li>Related Services like Packing, Category Management, Private Labels & Other Special Projects</li>
						<li>Shrink and Dump Management - Pick Up and Dump of FnV Waste</li>
						<li>SIS (Shop in Shop Model) for Super Store, Hyper Market</li>
					</ul>
				</div>
			</div>	
			<div class="page-section" id="services-2">
				<div class="page-section-header"><div>INSTITUTIONAL BUYERS</div><div style="margin-top:5px;font-weight:400;font-size:14px;">RESTARAUNTS, FOOD CATERERS & LARGE INSTITUTIONAL KITCHENS</div></div>
				<div class="page-section-content">
					<div class="ul-header">Services</div>
					<ul>
						<li>Logistics- Delivery to your Kitchen</li>
						<li>Diligent Sorting, Grading along with Stringent Quality Control</li>
						<li>Related Services like specific Requirements of Size, Shape,  Colour of an Item, Specific Packing for Storage</li>
						<li>Deals in various Exotic Vegetables</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div class="page-container" id="contact-container">
		<div class="page-header">CONTACT US</div>
		<div class="page-sections-container">
			<div class="page-section-content">
				<div><span class="contact-header">Business Queries</span>Email at <span class="email">business@aalgro.com</span></div>
				<div style="margin-top:30px"><span class="contact-header">Order Related Queries</span>Email at <span class="email">cs@aalgro.com</span></div>
			</div>
		</div>
	</div>
</div>
<div id="header-2-hover"><div>Who We Are</div><div>Buying Sources</div><div>License & Compliance</div><div>Range</div></div>
<div id="header-3-hover"><div>Retailers</div><div>Institutional Buyers</div></div>
<div id="header-4-hover"><i style="margin:0 10px 0 0" class="fa fa-phone fa-lg"></i>9243511110</div>
<div id="header-border"></div>


<script type="text/javascript">

$("#about-us-1").css({'top': '0px', 'left': '0px'});
$("#about-us-2").css({'top': '0px', 'left': '510px'});
$("#about-us-3").css({'top': ($("#about-us-2").outerHeight() + 30) + 'px', 'left': '510px'});
$("#about-us-4").css({'top': ($("#about-us-2").outerHeight() + 30 + $("#about-us-3").outerHeight() + 30) + 'px', 'left': '510px'});
$("#about-us-container .page-sections-container").height($("#about-us-2").outerHeight() + $("#about-us-3").outerHeight() + $("#about-us-4").outerHeight() + 60 + 20);

var offset = $("#header-4").offset();
$("#header-4-hover").css({top: (offset.top + $("#header-4").outerHeight() + 15) + 'px', left: offset.left + 'px'});
$("#header-border").height($("#header").outerHeight() + 15);

$('.header-section').click(function() {
	$(".header-section").removeClass('header-section-active');
	$(this).addClass('header-section-active');
	$(".page-container").hide();
	$(".page-section-header div").removeClass('page-section-header-highlighted');

	var id = $(this).attr('id');
	switch(id) {
		case 'header-2':
			$("#about-us-container").show();
			if($("#" + id + "-hover").is(':visible')) {
				$("#" + id + "-hover div").addClass('header-section-active');
			}
			break;

		case 'header-3':
			$("#services-container").show();
			$("#services-container .page-sections-container").height($("#services-1").outerHeight() + 20);
			if($("#" + id + "-hover").is(':visible')) {
				$("#" + id + "-hover div").addClass('header-section-active');
			}
			break;

		case 'header-4':
			$("#contact-container").show();
	}
});

var timeout;
$("#header-2, #header-3").on('mouseenter', function() { 
	var id = $(this).attr('id');
	if($("#" + id + "-hover").is(':visible')) {
		clearTimeout(timeout);
		return false;
	}

	var offset = $("#" + id).offset();
	$("#" + id + "-hover div").removeClass('header-section-active')
	$("#" + id + "-hover").css({top: (offset.top + $("#" + id).outerHeight()) + 'px', left: offset.left + 'px'}).slideDown();
	
	if($("#" + id).hasClass('header-section-active')) {
		$("#" + id + "-hover div").addClass('header-section-active');
	}
});

$("#header-2, #header-3").on('mouseleave', function() { 
	var id = $(this).attr('id');
	timeout = setTimeout(function() {
		$("#" + id + "-hover").hide();
	}, 100);
});

$("#header-2-hover, #header-3-hover").on('mouseenter', function() { 
	clearTimeout(timeout);
});

$("#header-2-hover, #header-3-hover").on('mouseleave', function() { 
	var id = $(this).attr('id');
	timeout = setTimeout(function() {
		$("#" + id).hide();
	}, 100);
});

$("#header-2-hover div").on('click', function() { 
	$(".page-container").hide();
	$("#about-us-container").show();

	$("#header-2-hover").hide();
	$(".header-section").removeClass('header-section-active');
	$("#header-2").addClass('header-section-active');
	
	var index = $("#header-2-hover div").index($(this));
	$(".page-section-header div").removeClass('page-section-header-highlighted');
	$("#about-us-" + (index+1) + " .page-section-header div:eq(0)").addClass('page-section-header-highlighted');
});

$("#header-3-hover div").on('click', function() { 
	$(".page-container").hide();
	$("#services-container").show();
	$("#services-container .page-sections-container").height($("#services-1").outerHeight() + 20);

	$("#header-3-hover").hide();
	$(".header-section").removeClass('header-section-active');
	$("#header-3").addClass('header-section-active');
	
	var index = $("#header-3-hover div").index($(this));
	$(".page-section-header div").removeClass('page-section-header-highlighted');
	$("#services-" + (index+1) + " .page-section-header div:eq(0)").addClass('page-section-header-highlighted');
});

</script>


<!-- Google Code for Remarketing Tag -->
<!--------------------------------------------------
Remarketing tags may not be associated with personally identifiable information or placed on pages related to sensitive categories. See more information and instructions on how to setup the tag on: http://google.com/ads/remarketingsetup
--------------------------------------------------->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 975415935;
var google_custom_params = window.google_tag_params;
var google_remarketing_only = true;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/975415935/?value=0&amp;guid=ON&amp;script=0"/>
</div>
</noscript>

</body>
</html>
