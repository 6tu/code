<?php

/*
  *http://www.jb51.net/article/67411.htm
  // Some example queries:
  *print whois_query('jonasjohn.de');
  *print whois_query('example.com');
  *print whois_query('example.org');
*/
if(!isset($_POST['name'])){
    htmlform();
    exit(0);
}else{
    htmlform();
    $domain = $_POST['name'];
    $res = whois_query($domain);
    
    //$res_array = explode("\r\n", $res);
    //print_r($res_array);
    //$ds = "";
    //foreach ($res_array as $key => $value) {
    //    if(strpos($value,'Domain Name:') !== false) $dn = $value;
    //    if(strpos($value,'Creation Date') !== false)  $ct = $value;
    //    if(strpos($value,'Domain Status') !== false)  $ds .= $value . "<br>\r\n";
    //}
    //
    //date_default_timezone_set('Asia/Shanghai');
    //
    //$ct = str_replace('Creation Date:', '', $ct);
    //$t1 = strtotime("+1 year",strtotime($ct));
    //$t2 = strtotime("+1 year",strtotime($ct) + 86400*60);
    //$t3 = strtotime("+1 year",strtotime($ct) + 86400*65);
    //$cbjutc = date('c', $t1);
    //$cbj = str_replace('+08:00', '', $cbjutc);
    //$cbj = str_replace('T', ' ', $cbj);
    //$dbjutc = date('c', $t2);
    //$dbj = str_replace('+08:00', '', $dbjutc);
    //$dbj = str_replace('T', ' ', $dbj);
    //$ebjutc = date('c', $t3);
    //$ebj = str_replace('+08:00', '', $ebjutc);
    //$ebj = str_replace('T', ' ', $ebj);

    //echo '<br><table border="0" align="center"><tr><td>';
    //if(strpos($ds,'redemptionPeriod') !== false) {
    //    echo "<p>目前处于删除期的赎回状态</p>";
    //    echo "<p>北京时间  " . $dbj  ." 进入删除状态，5天后将被删除</p>";
    //}else if(strpos($ds,'pendingDelete') !== false) {
    //    echo "<p>北京时间  " . $ebj  ." 将被彻底删除，或许它在数小时后的凌晨 2：00 彻底删除</p>";
 
    //}
    //echo "建立时间(UTC)： " . $ct . "<br>\r\n";
    //echo "到期(北京)时间 ： " . $cbj  . "<br>\r\n";
    //echo "当前北京时间 ： " . date('Y-m-d H:i:s') . "<br><br>\r\n";
    //
    //echo $ds . " <br>\r\n";
    //$js = <<<eof
    //<pre><small>
    //---------------
    //域名状态注释：<br>
    //  pendingDelete(待删除，到期后)
    //  <b>pendingDelete状态和下面的几种状态混用，直到最后7天单独存在</b>
    //  serverHold(暂停解析，到期后)
    //  pendingRestore(续费期，30天)
    //  redemptionPeriod(赎回期，30天)
    //  pendingDelete(等待删除，5天)
    //  
    //  字母T 表示后面跟的时间 
    //  末尾的Z 表示UTC统一时间 
    //  
    //  UTC (ISODATE): 
    //  格林尼治标准时间，协调世界标准时间，也被称为“Zulu time” 
    //  和中国时差 +8 小时
    //</small></pre>
    //eof;
    //echo  $js;
    //echo '</td></tr></table>';

    echo '<p></p>';
    //$max = count($res_array); 
    //for($i = 0; $i < $max; $i++){
    //    echo $res_array[$i] . "<br>\r\n";
    //}

    //echo '<p>'. str_replace("\r\n", "<br>\r\n",$res) . '</p><br><br>';
    
    $str = '<tr><td>'. str_replace("\r\n", "</td></tr><tr><td>\r\n",$res);
    
    echo '<table  align="center" width=50%>'. $str . '</table><br><br>';
}


function htmlform(){
    $html = <<<EOF
 <html>
 <head>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js" integrity="sha384-FzT3vTVGXqf7wRfy8k4BiyzvbNfeYjK+frTVqZeNDFl8woCbF0CYG6g2fMEFFo/i" crossorigin="anonymous"></script>
   <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-JAW99MJVpJBGcbzEuXk4Az05s/XyDdBomFqNlM3ic+I=" crossorigin="anonymous"></script>

   <script>
     // wait for the DOM to be loaded
     $(function() {
       // bind 'myForm' and provide a simple callback function
       $('#myForm').ajaxForm(function() {
           alert("searching for your domain!");
       });
     });
   </script>
 </head>
 <body><br><br><br><br>
 <center>
  <form id="myForm" action="domain.php" method="post">
   <input size=50% type="text" name="name" placeholder="please enter your domain"><br><br>
   <input type="submit" value="Submit">
 </form>
 </center>
EOF;
echo $html;
}

function whois_query($domain) {
  // fix the domain name:
  $domain = strtolower(trim($domain));
  $domain = preg_replace('/^http:\/\//i', '', $domain);
  $domain = preg_replace('/^www\./i', '', $domain);
  $domain = explode('/', $domain);
  $domain = trim($domain[0]);
  // split the TLD from domain name
  $_domain = explode('.', $domain);
  $lst = count($_domain)-1;
  $ext = $_domain[$lst];
  // You find resources and lists 
  // like these on wikipedia: 
  //
  // http://de.wikipedia.org/wiki/Whois
  //
  $servers = array(
    "biz" => "whois.neulevel.biz",
    "com" => "whois.internic.net",
    "us" => "whois.nic.us",
    "coop" => "whois.nic.coop",
    "info" => "whois.nic.info",
    "name" => "whois.nic.name",
    "net" => "whois.internic.net",
    "gov" => "whois.nic.gov",
    "edu" => "whois.internic.net",
    "mil" => "rs.internic.net",
    "int" => "whois.iana.org",
    "ac" => "whois.nic.ac",
    "ae" => "whois.uaenic.ae",
    "at" => "whois.ripe.net",
    "au" => "whois.aunic.net",
    "be" => "whois.dns.be",
    "bg" => "whois.ripe.net",
    "br" => "whois.registro.br",
    "bz" => "whois.belizenic.bz",
    "ca" => "whois.cira.ca",
    "cc" => "whois.nic.cc",
    "ch" => "whois.nic.ch",
    "cl" => "whois.nic.cl",
    "cn" => "whois.cnnic.net.cn",
    "cz" => "whois.nic.cz",
    "de" => "whois.nic.de",
    "fr" => "whois.nic.fr",
    "hu" => "whois.nic.hu",
    "ie" => "whois.domainregistry.ie",
    "il" => "whois.isoc.org.il",
    "in" => "whois.ncst.ernet.in",
    "ir" => "whois.nic.ir",
    "mc" => "whois.ripe.net",
    "to" => "whois.tonic.to",
    "tv" => "whois.tv",
    "ru" => "whois.ripn.net",
    "org" => "whois.pir.org",
    "aero" => "whois.information.aero",
    "nl" => "whois.domain-registry.nl",
    "win" => "whois.nic.win",
    "io" => "whois.nic.io",
  );
  if (!isset($servers[$ext])){
    die('Error: No matching nic server found!');
  }
  $nic_server = $servers[$ext];
  $output = '';
  // connect to whois server:
  if ($conn = fsockopen ($nic_server, 43)) {
    fputs($conn, $domain."\r\n");
    while(!feof($conn)) {
      $output .= fgets($conn,128);
    }
    fclose($conn);
  }
  else { die('Error: Could not connect to ' . $nic_server . '!'); }
  return $output;
}

?>
