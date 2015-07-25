<?php
// Database variables
$host = "localhost"; //database location
$user = ""; //database username
$pass = ""; //database password
$db_name = ""; //database name
 
// PayPal settings
$paypal_email = 'mason@cbacamps.com';
$return_url = 'http://cambridgebasketballacademy.com/registration_success.html';
$cancel_url = 'http://cambridgebasketballacademy.com/registration_cancelled.html';
$notify_url = 'http://cambridgebasketballacademy.com/payments.php';
 
$item_name = 'Cambridge Basketball Registration';
$item_amount = 3450.00;
  
// Check if paypal request or response
if (!isset($_POST["txn_id"]) && !isset($_POST["txn_type"])){
 
    // Firstly Append paypal account to querystring
    $querystring .= "?business=".urlencode($paypal_email)."&";
 
    // Append amount& currency (£) to quersytring so it cannot be edited in html
 
    //The item name and amount can be brought in dynamically by querying the $_POST['item_number'] variable.
    $querystring .= "item_name=".urlencode($item_name)."&";
    $querystring .= "amount=".urlencode($item_amount)."&";
 
    //we actually don't want to do this. we're just going to create a unique transaction identifier
    //loop for posted values and append to querystring
    //foreach($_POST as $key => $value){
    //    $value = urlencode(stripslashes($value));
    //    $querystring .= "$key=$value&";
    //}
    $key = "email_id";
    $random_transaction_id = uniqid();
    $querystring .= $key+"=".urlencode($random_transaction_id)."&";


    //send the email using godaddysform
    $request_method = $_SERVER["REQUEST_METHOD"];
    if($request_method == "GET"){
      $query_vars = $_GET;
    } elseif ($request_method == "POST"){
      $query_vars = $_POST;
    }
    $query_vars[$key] = $random_transaction_id;
    reset($query_vars);
    $t = date("U");

    $file = $_SERVER['DOCUMENT_ROOT'] . "/../data/gdform_" . $t;
    $fp = fopen($file,"w");
    while (list ($key, $val) = each ($query_vars)) {
	    if ($key != "currency_code" && $key != "cmd"){
		    fputs($fp,"<GDFORM_VARIABLE NAME=$key START>\n");
	        fputs($fp,"$val\n");
	        fputs($fp,"<GDFORM_VARIABLE NAME=$key END>\n");
	        if ($key == "redirect") { $landing_page = $val;}
	    }
    }
    fclose($fp);


 
    // Append paypal return addresses
    $querystring .= "return=".urlencode(stripslashes($return_url))."&";
    $querystring .= "cancel_return=".urlencode(stripslashes($cancel_url))."&";
    $querystring .= "notify_url=".urlencode(stripslashes($notify_url))."&";

    $querystring .= "cmd=".urlencode($query_vars["cmd"])."&";
    $querystring .= "currency_code=".urlencode($query_vars["currency_code"])."&";
    $querystring .= "no_shipping=".urlencode(1)."&";
	$querystring .= "custom=".urlencode($random_transaction_id)."&";
 

    // Append querystring with custom field
    //$querystring .= "&custom=".USERID;
 
    // Redirect to paypal IPN
    header('location:https://www.paypal.com/cgi-bin/webscr'.$querystring);
    exit();
 
}else{
    // Response from PayPal
 
    // read the post from PayPal system and add 'cmd'
    $req = 'cmd=_notify-validate';
    foreach ($_POST as $key => $value) {
        $value = urlencode(stripslashes($value));
        $value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i','${1}%0D%0A${3}',$value);// IPN fix
        $req .= "&$key=$value";
    }
 
    // assign posted variables to local variables
    $data['txn_id']             = $_POST['txn_id'];
    $data['custom']             = $_POST['custom'];
    $data['subject']            = "Payment received for email_id";
 
    // post back to PayPal system to validate
    $header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
    $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
 
    $fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
 
    if (!$fp) {
        header("Location: http://".$_SERVER["HTTP_HOST"]."/$cancel_url");
    } 
    else {
	    fputs ($fp, $header . $req);
		$verified = 0;
	    while (!feof($fp)) {
			$res = fgets ($fp, 1024);
			if (strcmp ($res, "VERIFIED") == 0) {
				$verified = 1;
			}
			else if (strcmp ($res, "INVALID") == 0) {
				// PAYMENT INVALID & INVESTIGATE MANUALY!
				// E-mail admin or alert user
				header("Location: http://".$_SERVER["HTTP_HOST"]."/$cancel_url");
			}
        }	    
	    fclose ($fp);
	    if ($verified == 1){
			//send the email using godaddysform			   
		    $t = date("U");

		    $file = $_SERVER['DOCUMENT_ROOT'] . "/../data/gdform_" . $t;
		    $email_fp = fopen($file,"w");
		    while (list ($key, $val) = each ($data)) {
				fputs($email_fp,"<GDFORM_VARIABLE NAME=$key START>\n");
			    fputs($email_fp,"$val\n");
			    fputs($email_fp,"<GDFORM_VARIABLE NAME=$key END>\n");
		    }
		    fclose($email_fp);
			
		    header("Location: http://".$_SERVER["HTTP_HOST"]."/$return_url");
	    }
	}  
}
?>