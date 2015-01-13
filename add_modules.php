#!/usr/bin/php
<?php
/***********************************/
/******* CONFIGURATION START *******/
/***********************************/

//Add additional sites here in same format as below
$sites = array('asiowacom','capitalmoderncom','berwicksofficecom','corpbussystemscom','datamaxstlcom','dntstlcom','edwardsbusinesscom','prime-officecom','theotgcom','wpssolutionscom');
//Name of Module
$module_name = 'article_import_client';

/***********************************/
/******** CONFIGURATION END ********/
/***********************************/



include_once('/root/.drush/pantheon.aliases.drushrc.php');
$siteData = array();
$i=0;
foreach($sites as $data) {
  $uuid = $aliases["$data.dev"]['remote-user'];
  $uuid = explode('.', $uuid);
  $uuid = $uuid[1];
  $siteData[$i]['SITE_NAME'] = $data;
  $siteData[$i]['SITE_UUID'] = $uuid;
  $siteData[$i]['MODULE'] = $module_name;
  $i++;
}

//Colors
$red = "\033[31m";
$coloroff = "\033[37m";
$green = "\033[32m";
$cyan = "\033[36m";
$yellow="\033[33m";

foreach($siteData as $data) {
  print $green.'Cloning Live to Dev for '.$coloroff.$cyan.$data['SITE_NAME'].$coloroff."\r\n\r\n";
  system('/usr/bin/drush -y psite-clone '.$data['SITE_UUID'].' live dev --db --files --update');
  print "\r\n";
  sleep(50);
  print $green.'Clearing Cache for '.$coloroff.$cyan.$data['SITE_NAME'].$coloroff."\r\n\r\n";
  system('/usr/bin/drush -y @pantheon.'.$data['SITE_NAME'].'.dev cc all --strict=0');
  print "\r\n";
  sleep(10);
  print $green.'Uploading Module '.$coloroff.$cyan.$data['MODULE'].$coloroff.$green.' to '.$coloroff.$cyan.$data['SITE_NAME'].$coloroff."\r\n\r\n";
  system('/usr/bin/drush -y -r . rsync /var/www/custom_modules/'.$data['MODULE'].' @pantheon.'.$data['SITE_NAME'].'.dev:code/sites/all/modules/custom/');
  print "\r\n";
  sleep(20);
  print $green.'Commiting Changes for new uploaded module'.$coloroff."\r\n";
  system('/usr/bin/drush -y psite-commit '.$data['SITE_UUID'].' dev --message=\'Added '.$data['MODULE'].' module\'');
  print $yellow."..... DONE!".$coloroff."\r\n\r\n";
  sleep(5);
  print $green.'Enabling module '.$coloroff.$cyan.$data['MODULE'].$coloroff.$green." on ".$coloroff.$cyab.$data['SITE_NAME'].$coloroff."\r\n\r\n";
  system('/usr/bin/drush -y @pantheon.'.$data['SITE_NAME'].'.dev en '.$data['MODULE'].' --strict=0');
  print "\r\n";
  sleep(10);
  print $green.'Clearing Cache for '.$coloroff.$cyan.$data['SITE_NAME'].$coloroff."\r\n\r\n";
  system('/usr/bin/drush -y @pantheon.'.$data['SITE_NAME'].'.dev cc all --strict=0');
  print "\r\n";
  sleep(10);
  print $green.'Deploying code from dev enviroment to test enviroment'.$coloroff."\r\n\r\n";
  system('/usr/bin/drush -y psite-deploy '.$data['SITE_UUID'].' test');
  sleep(30);
  print $yellow."..... DONE!".$coloroff."\r\n\r\n";
  print $green.'Deploying code from test enviroment to live enviroment'.$coloroff."\r\n\r\n";
  system('/usr/bin/drush -y psite-deploy '.$data['SITE_UUID'].' live');
  sleep(30);
  print $yellow."..... DONE!".$coloroff."\r\n\r\n";
  print $green.'Cloning Dev to Live for '.$coloroff.$cyan.$data['SITE_NAME'].$coloroff."\r\n\r\n";
  system('/usr/bin/drush -y psite-clone '.$data['SITE_UUID'].' dev live --db --files --update');
  print "\r\n";
  sleep(50);
  print $green.'Clearing Cache for '.$coloroff.$cyan.$data['SITE_NAME'].$coloroff."\r\n\r\n";
  system('/usr/bin/drush -y @pantheon.'.$data['SITE_NAME'].'.live cc all --strict=0');
  print "\r\n";
  sleep(10);
  //die("SDF");
}
print $red.'Operation is complete!'.$coloroff."\r\n\r\n";


?>
