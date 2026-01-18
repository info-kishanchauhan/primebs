
<?php


return array(

'db' => array(
    'driver'   => 'Pdo_Mysql', // ✅ REQUIRED
    'username' => 'root',
    'password' => '1234',
    'database' => "primebs",   // ✅ Rename from 'db' to 'database' for consistency
    'host'     => 'localhost',
),
	
	'SMTP_DETAILS' => array(

        'host' => 'smtp.hostinger.com',

        'username' => 'support@primedigitalarena.in',

        'password' => 'Razvi@78692786',

        'port' => '465',
		
    ),
	
	'SMTP_DETAILS2' => array(

        'host' => 'smtp.hostinger.com',

        'username' => 'Payout@primedigitalarena.in',

        'password' => 'Razvi@78692786',

        'port' => '465',
		
    ),
	
	'URL' => 'https://www.primebackstage.in/',
	
	'PATH' => '/home/primebackstage/htdocs/www.primebackstage.in/'

);
