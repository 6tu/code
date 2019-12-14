#!/www/perl/bin/perl
use strict ;
use Socket ;
use vars qw(
   $TEXT_ONLY
   $REMOVE_COOKIES  $REMOVE_SCRIPTS  $FILTER_ADS  $HIDE_REFERER
   $INSERT_ENTRY_FORM  $ALLOW_USER_CONFIG
   @ALLOWED_SERVERS  @BANNED_SERVERS  @BANNED_NETWORKS
   $NO_COOKIE_WITH_IMAGE  @ALLOWED_COOKIE_SERVERS  @BANNED_COOKIE_SERVERS
   @ALLOWED_SCRIPT_SERVERS  @BANNED_SCRIPT_SERVERS
   @BANNED_IMAGE_URL_PATTERNS  $RETURN_EMPTY_GIF
   $USER_IP_ADDRESS_TEST  $DESTINATION_SERVER_TEST
   $INSERT_HTML  $INSERT_FILE  $ANONYMIZE_INSERTION  $FORM_AFTER_INSERTION
   $INSERTION_FRAME_HEIGHT
   $RUNNING_ON_SSL_SERVER  $NOT_RUNNING_AS_NPH
   $HTTP_PROXY  $SSL_PROXY  $NO_PROXY  $PROXY_AUTH  $SSL_PROXY_AUTH
   $MINIMIZE_CACHING
   $SESSION_COOKIES_ONLY  $COOKIE_PATH_FOLLOWS_SPEC  $RESPECT_THREE_DOT_RULE
   @PROXY_GROUP
   $USER_AGENT  $USE_PASSIVE_FTP_MODE  $SHOW_FTP_WELCOME
   $PROXIFY_SCRIPTS  $PROXIFY_SWF  $ALLOW_UNPROXIFIED_SCRIPTS  $PROXIFY_COMMENTS
   $ENCODE_DECODE_BLOCK_IN_JS
   $USE_POST_ON_START  $ENCODE_URL_INPUT
   $REMOVE_TITLES  $NO_BROWSE_THROUGH_SELF  $NO_LINK_TO_START  $MAX_REQUEST_SIZE
   @TRANSMIT_HTML_IN_PARTS_URLS
   $QUIETLY_EXIT_PROXY_SESSION
   $OVERRIDE_SECURITY
   @SCRIPT_MIME_TYPES  @OTHER_TYPES_TO_REGISTER  @TYPES_TO_HANDLE
   $NON_TEXT_EXTENSIONS
   $PROXY_VERSION
   @MONTH  @WEEKDAY  %UN_MONTH
   @BANNED_NETWORK_ADDRS
   $USER_IP_ADDRESS_TEST_H  $DESTINATION_SERVER_TEST_H
   $RUNNING_ON_IIS
   @NO_PROXY
   $NO_CACHE_HEADERS
   @ALL_TYPES  %MIME_TYPE_ID  $SCRIPT_TYPE_REGEX  $TYPES_TO_HANDLE_REGEX
   $THIS_HOST  $ENV_SERVER_PORT  $ENV_SCRIPT_NAME  $THIS_SCRIPT_URL
   $HAS_BEGUN
   $CUSTOM_INSERTION  %IN_CUSTOM_INSERTION
   $RE_JS_WHITE_SPACE  $RE_JS_LINE_TERMINATOR  $RE_JS_COMMENT
   $RE_JS_IDENTIFIER_START  $RE_JS_IDENTIFIER_PART  $RE_JS_IDENTIFIER_NAME
   $RE_JS_PUNCTUATOR  $RE_JS_DIV_PUNCTUATOR
   $RE_JS_NUMERIC_LITERAL  $RE_JS_ESCAPE_SEQUENCE
   $RE_JS_STRING_LITERAL_START  $RE_JS_STRING_REMAINDER_1  $RE_JS_STRING_REMAINDER_2
   $RE_JS_REGULAR_EXPRESSION_LITERAL
   $RE_JS_TOKEN  $RE_JS_INPUT_ELEMENT_DIV  $RE_JS_INPUT_ELEMENT_REG_EXP
   $RE_JS_SKIP  $RE_JS_SKIP_NO_LT
   %RE_JS_SET_TRAPPED_PROPERTIES %RE_JS_SET_RESERVED_WORDS_NON_EXPRESSION
   %RE_JS_SET_ALL_PUNCTUATORS
   $JSLIB_BODY
   $HTTP_VERSION  $HTTP_1_X
   $URL
   $now
   $packed_flags  $encoded_URL  $doing_insert_here  $env_accept
   $e_remove_cookies  $e_remove_scripts  $e_filter_ads  $e_insert_entry_form
   $e_hide_referer
   $images_are_banned_here  $scripts_are_banned_here  $cookies_are_banned_here
   $scheme  $authority  $path  $host  $port  $username  $password
   $cookie_to_server  %auth
   $script_url  $url_start  $url_start_inframe  $url_start_noframe
   $is_in_frame  $expected_type
   $base_url  $base_scheme  $base_host  $base_path  $base_file  $base_unframes
   $default_style_type  $default_script_type
   $status  $headers  $body  $is_html  $is_utf8  $response_sent
   %in_mini_start_form
   $needs_jslib  $does_write
   $swflib
   $debug ) ;
unless ($HAS_BEGUN) {
$TEXT_ONLY= 0 ;      # set to 1 to allow only text data, 0 to allow all
$REMOVE_COOKIES= 0 ;
$REMOVE_SCRIPTS= 0 ;
$FILTER_ADS= 0 ;
$HIDE_REFERER= 0 ;
$INSERT_ENTRY_FORM= 1 ;
$ALLOW_USER_CONFIG= 1 ;
sub proxy_encode {
    my($URL)= @_ ;
    $URL=~ s#^([\w+.-]+)://#$1/# ;                 # http://xxx -> http/xxx
#    $URL=~ s/(.)/ sprintf('%02x',ord($1)) /ge ;   # each char -> 2-hex
#    $URL=~ tr/a-zA-Z/n-za-mN-ZA-M/ ;              # rot-13
    return $URL ;
}
sub proxy_decode {
    my($enc_URL)= @_ ;
#    $enc_URL=~ tr/a-zA-Z/n-za-mN-ZA-M/ ;        # rot-13
#    $enc_URL=~ s/([\da-fA-F]{2})/ sprintf("%c",hex($1)) /ge ;
    $enc_URL=~ s#^([\w+.-]+)/#$1://# ;           # http/xxx -> http://xxx
    return $enc_URL ;
}
sub cookie_encode {
    my($cookie)= @_ ;
#    $cookie=~ s/(.)/ sprintf('%02x',ord($1)) /ge ;   # each char -> 2-hex
#    $cookie=~ tr/a-zA-Z/n-za-mN-ZA-M/ ;              # rot-13
    $cookie=~ s/(\W)/ '%' . sprintf('%02x',ord($1)) /ge ; # simple URL-encoding
    return $cookie ;
}
sub cookie_decode {
    my($enc_cookie)= @_ ;
    $enc_cookie=~ s/%([\da-fA-F]{2})/ pack('C', hex($1)) /ge ;  # URL-decode
#    $enc_cookie=~ tr/a-zA-Z/n-za-mN-ZA-M/ ;          # rot-13
#    $enc_cookie=~ s/([\da-fA-F]{2})/ sprintf("%c",hex($1)) /ge ;
    return $enc_cookie ;
}
$ENCODE_DECODE_BLOCK_IN_JS= <<'EOB' ;
function _proxy_jslib_proxy_encode(URL) {
    URL= URL.replace(/^([\w\+\.\-]+)\:\/\//, '$1/') ;
    return URL ;
}
function _proxy_jslib_proxy_decode(enc_URL) {
    enc_URL= enc_URL.replace(/^([\w\+\.\-]+)\//, '$1://') ;
    return enc_URL ;
}
function _proxy_jslib_cookie_encode(cookie) {
    cookie= cookie.replace(/(\W)/g, function (s,p1) { return '%'+p1.charCodeAt(0).toString(16) } ) ;
    return cookie ;
}
function _proxy_jslib_cookie_decode(enc_cookie) {
    enc_cookie= enc_cookie.replace(/%([\da-fA-F]{2})/g, function (s,p1) { return String.fromCharCode(eval('0x'+p1)) } ) ;
    return enc_cookie ;
}
EOB
@ALLOWED_SERVERS= () ;
@BANNED_SERVERS= () ;
@BANNED_NETWORKS= ('172', '192.168', '172', '10', '169.254', '244.0.0') ;
@BANNED_COOKIE_SERVERS= (
    '\.doubleclick\.net$',
    '\.preferences\.com$',
    '\.imgis\.com$',
    '\.adforce\.com$',
    '\.focalink\.com$',
    '\.flycast\.com$',
    '\.avenuea\.com$',
    '\.linkexchange\.com$',
    '\.pathfinder\.com$',
    '\.burstnet\.com$',
    '\btripod\.com$',
    '\bgeocities\.yahoo\.com$',
    '\.mediaplex\.com$',
    ) ;
$NO_COOKIE_WITH_IMAGE= 1 ;
@ALLOWED_SCRIPT_SERVERS= () ;
@BANNED_SCRIPT_SERVERS= () ;
@BANNED_IMAGE_URL_PATTERNS= (
    'ad\.doubleclick\.net/ad/',
    '\b[a-z](\d+)?\.doubleclick\.net(:\d*)?/',
    '\.imgis\.com\b',
    '\.adforce\.com\b',
    '\.avenuea\.com\b',
    '\.go\.com(:\d*)?/ad/',
    '\.eimg\.com\b',
    '\bexcite\.netscape\.com(:\d*)?/.*/promo/',
    '/excitenetscapepromos/',
    '\.yimg\.com(:\d*)?.*/promo/',
    '\bus\.yimg\.com/[a-z]/(\w\w)/\1',
    '\bus\.yimg\.com/[a-z]/\d-/',
    '\bpromotions\.yahoo\.com(:\d*)?/promotions/',
    '\bcnn\.com(:\d*)?/ads/',
    'ads\.msn\.com\b',
    '\blinkexchange\.com\b',
    '\badknowledge\.com\b',
    '/SmartBanner/',
    '\bdeja\.com/ads/',
    '\bimage\.pathfinder\.com/sponsors',
    'ads\.tripod\.com',
    'ar\.atwola\.com/image/',
    '\brealcities\.com/ads/',
    '\bnytimes\.com/ad[sx]/',
    '\busatoday\.com/sponsors/',
    '\busatoday\.com/RealMedia/ads/',
    '\bmsads\.net/ads/',
    '\bmediaplex\.com/ads/',
    '\batdmt\.com/[a-z]/',
    '\bview\.atdmt\.com/',
    '\bADSAdClient31\.dll\b',
    ) ;
$RETURN_EMPTY_GIF= 0 ;
$USER_IP_ADDRESS_TEST= '' ;
$DESTINATION_SERVER_TEST= '' ;
#$INSERT_HTML= "<h1>This is an inserted header</h1><hr>" ;
#$INSERT_FILE= 'insert_file_name' ;
$ANONYMIZE_INSERTION= 0 ;
$FORM_AFTER_INSERTION= 0 ;
$INSERTION_FRAME_HEIGHT= $ALLOW_USER_CONFIG   ? 80   : 50 ;
$RUNNING_ON_SSL_SERVER= '' ;
$NOT_RUNNING_AS_NPH= 0 ;
#$HTTP_PROXY= $ENV{'http_proxy'} ;
#$SSL_PROXY= 'firewall.example.com:3128' ;
#$NO_PROXY= $ENV{'no_proxy'} ;
#$PROXY_AUTH= 'Aladdin:open sesame' ;
#$SSL_PROXY_AUTH= $PROXY_AUTH ;
#@PROXY_GROUP= ('http://www.example.com/~grommit/proxy/nph-proxy.cgi',
#	        'http://www.fnord.mil/langley/bavaria/atlantis/nph-proxy.cgi',
#	        'http://www.nothinghere.gov/No/Such/Agency/nph-proxy.cgi',
#	        ) ;
$MINIMIZE_CACHING= 0 ;
$SESSION_COOKIES_ONLY= 0 ;
$COOKIE_PATH_FOLLOWS_SPEC= 0 ;
$RESPECT_THREE_DOT_RULE= 0 ;
$USE_PASSIVE_FTP_MODE= 1 ;
$SHOW_FTP_WELCOME= 1 ;
$PROXIFY_SCRIPTS= 1 ;
$PROXIFY_SWF= 0 ;
$ALLOW_UNPROXIFIED_SCRIPTS= 0 ;
$PROXIFY_COMMENTS= 0 ;
$USE_POST_ON_START= 1 ;
$ENCODE_URL_INPUT= 0 ;
$REMOVE_TITLES= 0 ;
$NO_BROWSE_THROUGH_SELF= 0 ;
$NO_LINK_TO_START= 0 ;
$MAX_REQUEST_SIZE= 4194304 ;  # that's 4 Meg to you and me
#@TRANSMIT_HTML_IN_PARTS_URLS= (
#    '^https?://search3\.webfeat\.org/cgi-bin/WebFeat\.dll',
#    ) ;
$QUIETLY_EXIT_PROXY_SESSION= 0 ;
$OVERRIDE_SECURITY= 0 ;
@SCRIPT_MIME_TYPES= ('application/x-javascript', 'application/x-ecmascript',
		     'application/x-vbscript',   'application/x-perlscript',
		     'application/javascript',   'application/ecmascript',
		     'text/javascript',  'text/ecmascript', 'text/jscript',
		     'text/livescript',  'text/vbscript',   'text/vbs',
		     'text/perlscript',  'text/tcl',
		     'text/x-scriptlet', 'text/scriptlet',
		     'application/hta',   'application/x-shockwave-flash',
		    ) ;
@OTHER_TYPES_TO_REGISTER= ('text/css', 'text/xml') ;
@TYPES_TO_HANDLE= ('text/css',
		   'application/x-javascript', 'application/x-ecmascript',
		   'application/javascript',   'application/ecmascript',
		   'text/javascript',          'text/ecmascript',
		   'text/livescript',          'text/jscript',
		   'application/x-shockwave-flash',
		  ) ;
$NON_TEXT_EXTENSIONS=
	  'gif|jpeg|jpe|jpg|tiff|tif|png|bmp|xbm'   # images
	. '|mp2|mp3|wav|aif|aiff|au|snd'            # audios
	. '|avi|qt|mov|mpeg|mpg|mpe'                # videos
	. '|gz|Z|exe|gtar|tar|zip|sit|hqx|pdf'      # applications
	. '|ram|rm|ra|swf' ;                        # others
# $PROXY_VERSION= '2.1beta19' ;
@MONTH=   qw(Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec) ;
@WEEKDAY= qw(Sun Mon Tue Wed Thu Fri Sat Sun) ;
%UN_MONTH= map { lc($MONTH[$_]), $_ }  0..$#MONTH ;   # look up by month name
&set_RE_JS  if $PROXIFY_SCRIPTS ;
$ENV{SCRIPT_NAME}=~ s#^/?#/# ;
if ($ENV{SERVER_SOFTWARE}=~ /^Apache\b/i) {
    my($zero)= $0=~ m#([^/]*)$# ;
    ($ENV{SCRIPT_NAME})= $ENV{SCRIPT_NAME}=~ /^(.*?\Q$zero\E)/ if $zero ne '' ;
}
$ENV_SERVER_PORT= $ENV{SERVER_PORT} ;
$ENV_SCRIPT_NAME= $ENV{SCRIPT_NAME} ;
@BANNED_NETWORK_ADDRS= () ;
for (@BANNED_NETWORKS) {
    push(@BANNED_NETWORK_ADDRS, pack('C*', /(\d+)/g)) ;
}
@{$USER_IP_ADDRESS_TEST_H}{qw(host port path socket open)}=
	(lc($1), ($2 eq '' ? 80 : $2), $3, 'S_USERTEST', 0)
    if ($USER_IP_ADDRESS_TEST=~ m#http://([^/?:]*):?(\d*)(.*)#i) ;
@{$DESTINATION_SERVER_TEST_H}{qw(host port path socket open)}=
	(lc($1), ($2 eq '' ? 80 : $2), $3, 'S_DESTTEST', 0)
    if ($DESTINATION_SERVER_TEST=~ m#http://([^/?:]*):?(\d*)(.*)#i) ;
$RUNNING_ON_SSL_SERVER= ($ENV_SERVER_PORT==443) if $RUNNING_ON_SSL_SERVER eq '' ;
$RUNNING_ON_IIS= ($ENV{'SERVER_SOFTWARE'}=~ /IIS/) ;
@NO_PROXY= split(/\s*,\s*/, $NO_PROXY) ;
$PROXY_AUTH=     &base64($PROXY_AUTH)      if $PROXY_AUTH=~ /:/ ;
$SSL_PROXY_AUTH= &base64($SSL_PROXY_AUTH)  if $SSL_PROXY_AUTH=~ /:/ ;
foreach (@PROXY_GROUP) { s#/$## }
$NO_CACHE_HEADERS= $MINIMIZE_CACHING
    ? "Cache-Control: no-cache\015\012Pragma: no-cache\015\012"
    : '' ;
for (@SCRIPT_MIME_TYPES)        { $_= lc }
for (@OTHER_TYPES_TO_REGISTER)  { $_= lc }
@ALL_TYPES= ('', @SCRIPT_MIME_TYPES, @OTHER_TYPES_TO_REGISTER) ;
&HTMLdie("Too many MIME types to register.")  if @ALL_TYPES > 64 ;
@MIME_TYPE_ID{@ALL_TYPES}=  0..$#ALL_TYPES ;
$SCRIPT_TYPE_REGEX= '(' . join("|", @SCRIPT_MIME_TYPES) . ')' ;
$TYPES_TO_HANDLE_REGEX= '(' . join("|", @TYPES_TO_HANDLE) . ')' ;
if ($ENV{'HTTP_HOST'} ne '') {
    ($THIS_HOST)= $ENV{'HTTP_HOST'}=~ m#^(?:[\w+.-]+://)?([^:/?]*)# ;
    $THIS_HOST= $ENV{'SERVER_NAME'}   if $THIS_HOST eq '' ;
} else {
    $THIS_HOST= $ENV{'SERVER_NAME'} ;
}
$THIS_SCRIPT_URL= $RUNNING_ON_SSL_SERVER
	    ? 'https://' . $THIS_HOST
	      . ($ENV_SERVER_PORT==443  ? ''  : ':' . $ENV_SERVER_PORT)
	      . $ENV_SCRIPT_NAME
	    : 'http://' . $THIS_HOST
	      . ($ENV_SERVER_PORT==80   ? ''  : ':' . $ENV_SERVER_PORT)
	      . $ENV_SCRIPT_NAME ;
$HAS_BEGUN= 1 ;
}  # unless ($HAS_BEGUN)
#$starttime= time ;
#my($sutime,$sstime)= (times)[0,1] ;
local($|)= 1 ;
reset 'a-z' ;
$URL= '' ;     # (almost) only uppercase variable that varies from run to run
#$needs_jslib= 0 ;
$now= time ;    # for (@goodmen)
($HTTP_VERSION)= $ENV{'SERVER_PROTOCOL'}=~ m#^HTTP/(\d+\.\d+)#i ;
$HTTP_VERSION= '1.0' unless $HTTP_VERSION=~ /^1\.[01]$/ ;
$HTTP_1_X=  $NOT_RUNNING_AS_NPH   ? 'Status:'   : "HTTP/$HTTP_VERSION" ;
if ($ENV{'SERVER_SOFTWARE'}=~ m#^Apache/(\d+)\.(\d+)(?:\.(\d+))?#i) {
    if (($1<=>1 or $2<=>3 or $3<=>6) < 0) {
	$SIG{'ALRM'} = \&timeexit ;
	eval { alarm(600) } ;     # use where it works, ignore where it doesn't
    }
}
sub timeexit { $ENV{'MOD_PERL'}  ? goto EXIT  : exit 1 }
$ENV{'PATH_INFO'} =~ s/^$ENV_SCRIPT_NAME//   if $RUNNING_ON_IIS ;
if ($ENV{'PATH_INFO'}=~ / /) {
    $ENV{'PATH_INFO'} =~ s/%/%25/g ;
    $ENV{'PATH_INFO'} =~ s/ /%20/g ;
}
$env_accept= $ENV{'HTTP_ACCEPT'} || '*/*' ;     # may be modified later
($packed_flags, $encoded_URL)= $ENV{'PATH_INFO'}=~ m#/([^/]*)/?(.*)# ;
if ( $ALLOW_USER_CONFIG && ($packed_flags ne '') ) {
    ($e_remove_cookies, $e_remove_scripts, $e_filter_ads, $e_hide_referer,
     $e_insert_entry_form, $is_in_frame, $expected_type)=
	 &unpack_flags($packed_flags) ;
} else {
    ($e_remove_cookies, $e_remove_scripts, $e_filter_ads, $e_hide_referer,
     $e_insert_entry_form, $is_in_frame, $expected_type)=
	 ($REMOVE_COOKIES, $REMOVE_SCRIPTS, $FILTER_ADS, $HIDE_REFERER,
	  $INSERT_ENTRY_FORM, (&unpack_flags($packed_flags))[5..6] ) ;
}
$doing_insert_here= !$is_in_frame && 
    ( $e_insert_entry_form || ($INSERT_FILE ne '') || ($INSERT_HTML ne '') ) ;
binmode STDOUT ;
if (@PROXY_GROUP) {
    $script_url= $PROXY_GROUP[ rand(scalar @PROXY_GROUP) ] ;
} else {
    $script_url= $THIS_SCRIPT_URL ;
}
$url_start_inframe= $script_url . '/' .
    &pack_flags($e_remove_cookies, $e_remove_scripts, $e_filter_ads,
		$e_hide_referer, $e_insert_entry_form, 1, '') . '/' ;
$url_start_noframe= $script_url . '/' .
    &pack_flags($e_remove_cookies, $e_remove_scripts, $e_filter_ads,
		$e_hide_referer, $e_insert_entry_form, 0, '') . '/' ;
$url_start=  $is_in_frame   ? $url_start_inframe   : $url_start_noframe ;
&show_start_form() if $encoded_URL eq '' ;
$URL= &wrap_proxy_decode($encoded_URL) ;
$URL=~ s/(\?.*)?$/?$ENV{'QUERY_STRING'}/   if $ENV{'QUERY_STRING'} ne '' ;
($scheme, $authority, $path)= ($URL=~ m#^([\w+.-]+)://([^/?]*)(.*)$#i) ;
$scheme= lc($scheme) ;
$path= "/$path" if $path!~ m#^/# ;   # if path is '' or contains only query
&xproxy($URL) if $scheme eq 'x-proxy' ;
if ($ENV{'HTTP_USER_AGENT'}=~ /MSIE/) {
    $is_html= 1  if $path=~ /\.html?(\?|$)/i ;
} else {
    $is_html= 1  if $path=~ /^[^?]*\.html?(\?|$)/i ;
}
&unsupported_warning($URL) unless ($scheme=~ /^(http|https|ftp)$/) ;
&HTMLdie('The target URL cannot contain an empty host name.')
    unless $authority=~ /^\w/ ;
if ($scheme eq 'ftp') {
    if ($authority=~ /@/) {
	($username, $password, $host, $port)=
	    $authority=~ /([^:@]*):?([^@]*)@([^:]*):?(.*)/ ;
    } else {
	($username, $password)= ('anonymous', 'not@available.com') ;
	($host, $port)= $authority=~ /^([^:]*):?(.*)$/ ;
    }
} else {
    if ($authority=~ /@/) {
	($username, $password, $host, $port)=
	    $authority=~ /([^:@]*):?([^@]*)@([^:]*):?(.*)/ ;
    } else {
	($host, $port)= $authority=~ /^([^:]*):?(.*)$/ ;
    }
}
$host= lc($host) ;      # hostnames are case-insensitive
$host=~ s/\.*$//g ;     # removes trailing dots to close a potential exploit
if ($NO_BROWSE_THROUGH_SELF) {
    my($port2)=  $port || ( $scheme eq 'https'  ? 443  : 80 ) ;
    &loop_disallowed_die($URL)
	if     ($scheme=~ /^https?/)
	    && ($host=~ /^$THIS_HOST$/i)
	    && ($port2 == $ENV_SERVER_PORT)
	    && ($path=~ /^$ENV_SCRIPT_NAME\b/) ;
}
if ($USER_IP_ADDRESS_TEST) {
    my($ok) ;
    if ($USER_IP_ADDRESS_TEST_H) {
	$ok= &http_get2($USER_IP_ADDRESS_TEST_H,
			$USER_IP_ADDRESS_TEST_H->{path} . $ENV{REMOTE_ADDR}) ;
    } else {
	$ok= `$USER_IP_ADDRESS_TEST $ENV{REMOTE_ADDR}` ;
    }
    &banned_user_die if $ok==0 ;
}
if ($DESTINATION_SERVER_TEST) {
    my($ok) ;
    my($safehost)= $host ;
    if ($DESTINATION_SERVER_TEST_H) {
	$safehost=~ s/(\W)/ '%' . sprintf('%02x', ord($1)) /ge ;
	$ok= &http_get2($DESTINATION_SERVER_TEST_H,
			$DESTINATION_SERVER_TEST_H->{path} . $safehost) ;
    } else {
	$safehost=~ s/\\/\\\\/g ;
	$safehost=~ s/'/\\'/g ;
	$ok= `$DESTINATION_SERVER_TEST '$safehost'` ;
    }
    &banned_server_die($URL) if $ok==0 ;
}
if (@ALLOWED_SERVERS) {
    my($server_is_allowed) ;
    foreach (@ALLOWED_SERVERS) {
	$server_is_allowed= 1, last   if $host=~ /$_/ ;
    }
    &banned_server_die($URL) unless $server_is_allowed ;
}
foreach (@BANNED_SERVERS) {
    &banned_server_die($URL) if $host=~ /$_/ ;
}
if ($e_filter_ads) {
    foreach (@BANNED_IMAGE_URL_PATTERNS) {
	$images_are_banned_here= 1, last if $URL=~ /$_/ ;
    }
}
$scripts_are_banned_here= $e_remove_scripts ;
unless ($scripts_are_banned_here) {
    if (@ALLOWED_SCRIPT_SERVERS) {
	$scripts_are_banned_here= 1 ;
	foreach (@ALLOWED_SCRIPT_SERVERS) {
	    $scripts_are_banned_here= 0, last   if $host=~ /$_/ ;
	}
    }
    unless ($scripts_are_banned_here) {
	foreach (@BANNED_SCRIPT_SERVERS) {
	    $scripts_are_banned_here= 1, last   if $host=~ /$_/ ;
	}
    }
}
$cookies_are_banned_here= $e_remove_cookies ;
unless ($cookies_are_banned_here) {
    if (@ALLOWED_COOKIE_SERVERS) {
	$cookies_are_banned_here= 1 ;
	foreach (@ALLOWED_COOKIE_SERVERS) {
	    $cookies_are_banned_here= 0, last   if $host=~ /$_/ ;
	}
    }
    unless ($cookies_are_banned_here) {
	foreach (@BANNED_COOKIE_SERVERS) {
	    $cookies_are_banned_here= 1, last   if $host=~ /$_/ ;
	}
    }
}
if ($scripts_are_banned_here && $expected_type ne '') {
    &script_content_die if $expected_type=~ /^$SCRIPT_TYPE_REGEX$/io ;
}
if ($TEXT_ONLY) {
    &non_text_die if ($path=~ /\.($NON_TEXT_EXTENSIONS)(;|\?|$)/i) ;
    $env_accept=~ s#\*/\*#text/*#g ;    # not strictly perfect
    $env_accept= join(', ', grep(m#^text/#i, split(/\s*,\s*/, $env_accept)) ) ;
    &non_text_die unless $env_accept ne '' ;
}
if ($images_are_banned_here) {
    &skip_image unless grep(m#^(text|\*)/#i, split(/\s*,\s*/, $env_accept) ) ;
}
$base_url= $URL ;
&fix_base_vars ;   # must be called whenever $base_url is set
$default_style_type= 'text/css' ;
$default_script_type= 'application/x-javascript' ;
($cookie_to_server, %auth)=
    &parse_cookie($ENV{'HTTP_COOKIE'}, $path, $host, $port, $scheme) ;
if ($scheme eq 'http') {
    &http_get ;
} elsif ($scheme eq 'https') {
    &http_get ;
} elsif ($scheme eq 'ftp') {
    &ftp_get ;
}
if ( $is_html  && ($body ne '') && !$response_sent ) {
    $body= &proxify_html(\$body, 1) ;
    eval { utf8::encode($body) } if ($is_utf8) ;
    if ($ENV{HTTP_ACCEPT_ENCODING}=~ /\bgzip\b/i) {
	eval { require Compress::Zlib } ;
	if (!$@) {
	    my $body_pre= $body ;
	    my $body2= $body= Compress::Zlib::memGzip($body_pre) ;
	    my $body_post= Compress::Zlib::memGunzip($body2) ;
	    if ($body_pre eq $body_post) {
		$headers= "Content-Encoding: gzip\015\012" . $headers ;
	    } else {
		$body= $body_pre ;
	    }
	}
    }
    $headers=~ s/^Content-Length:.*\012/
		 'Content-Length: ' . length($body) . "\015\012"/mie ;
}
if (!$response_sent) {
    if ($ENV{'REQUEST_METHOD'} eq 'HEAD') {
	print $status, $headers ;
    } elsif ($is_html) {
	print $status, $headers, $body ;
    } else {
	print $status, $headers, $body ;
    }
}
EXIT:
close(S) ;
untie(*S) ;
eval { alarm(0) } ;   # use eval{} to avoid failing where alarm() is missing
exit unless $ENV{'MOD_PERL'} ;    # mod_perl scripts must not exit
sub proxify_html {
    my($body_ref, $is_full_page, $no_exit_on_frameset)= @_ ;
    my(@out, $start, $comment, $script_block, $style_block, $decl_bang, $decl_question, $tag,
       $body_pos, $html_pos, $head_pos, $first_script_pos, $out_start,
       $has_content, $in_noscript, $in_title, $title, $full_insertion, $body2,
       $current_object_classid) ;
    my($ua_is_MSIE)= $ENV{'HTTP_USER_AGENT'}=~ /MSIE/ ;   # used in tight loops
    $body_ref= \(join('', @$body_ref)) if ref($body_ref) eq 'ARRAY' ;
    if (!ref($body_ref)) {
	$body2= $body_ref ;
	$body_ref= \$body2 ;
    }
    $full_insertion= &full_insertion($URL,0)   if $is_full_page ;
    while ( $$body_ref=~ m{\G([^<]*)
			     (?:(<!--(?=.*?-->).*?--\s*> | <!--(?!.*?-->).*?> )
			       |(<\s*script\b.*?<\s*/script\b.*?>)
			       |(<\s*style\b.*?<\s*/style\b.*?>)
			       |(<![^>]*>?)
			       |(<\?[^>]*>?)
			       |(<[^>]*>?)
			     )?
			  }sgix )
    {
	($start, $comment, $script_block, $style_block, $decl_bang, $decl_question, $tag)=
	    ($1, $2, $3, $4, $5, $6, $7) ;
	if ($tag && !$body_pos && $tag=~ m#^<\s*/\s*title\b#i) {
	    $start= ''  if $REMOVE_TITLES ;
	    $title= $start ;
	}
	push(@out, $start) ;
	$out_start= @out ;
	$has_content||= $start=~ /\S/ unless $in_noscript || $in_title ;
	if ($tag) {
	    my($tag_name, $attrs, %attr, $name, $rebuild) ;
	    ($tag_name, $attrs)= $tag=~ /^<\s*(\/?\s*[A-Za-z][\w.:-]*)\s*([^>]*)/ ;
	    $tag_name=~ s#^/\s*#/# ;
	    $tag_name= lc($tag_name) ;
	    if ($tag_name eq 'noscript') {
		$in_noscript++ ;
		if ($scripts_are_banned_here) {
		    $tag=~ s/^<\s*noscript\b/<div/i ;
		    $tag_name= 'div' ;
		    $rebuild= 1 ;
		}
	    } elsif ($tag_name eq '/noscript') {
		$in_noscript-- if $in_noscript>0 ;
		push(@out, '</div>'), next if $scripts_are_banned_here ;
	    } elsif ($tag_name eq 'title') {
		$in_title++ ;
	    } elsif ($tag_name eq '/title') {
		$in_title-- ;
	    }
	    $html_pos= @out+1  if !$html_pos && ($tag_name eq 'html') ;
	    $head_pos= @out+1  if !$head_pos && ($tag_name eq 'head') ;
	    $body_pos= @out+1  if !$body_pos && ($tag_name eq 'body') ;
	    $current_object_classid= ''  if $tag_name eq '/object' ;
	    &return_frame_doc(&wrap_proxy_encode($URL), $title)
		if ($tag_name eq 'frameset') && $doing_insert_here  && !$is_in_frame
		   && !$no_exit_on_frameset ;
	    push(@out, "</div>\n") if $tag_name eq '/body' ;
	    push(@out, $tag), next   if ($attrs eq '') ;
	    PARSE_ATTRS: {
		while ($attrs=~ /([A-Za-z][\w.:-]*)\s*(?:=\s*(?:"([^"]*)"|'([^']*)'|([^'"][^\s>]*)|(['"])))?/g ) {
		    if (defined($5)) {
			$$body_ref=~ /\G([^>]*)(>?)/gc ;
			my($extra, $close)= ($1, $2) ;
			last if ($extra eq '') and ($close eq '') ;
			$attrs.= '>' . $extra ;
			$tag.=   $extra . $close ;
			%attr= () ;
			redo PARSE_ATTRS ;
		    }
		    $name= lc($1) ;
		    $rebuild= 1, next if exists($attr{$name}) ; # duplicate attr
		    $attr{$name}= &HTMLunescape(defined($2) ? $2
					      : defined($3) ? $3
					      : defined($4) ? $4
					      : '' ) ;
		}
	    }
	    if ($scripts_are_banned_here) {
		my(@remove_attrs)= grep(/^on/ || $attr{$_}=~ /&{/ , keys %attr) ;
		delete @attr{ @remove_attrs }, $rebuild=1   if @remove_attrs ;
	    } elsif ($PROXIFY_SCRIPTS) {
		foreach (keys %attr) {
		    $attr{$_}=~ s/&{(.*?)};/
				 '&{' . (&proxify_block($1, $default_script_type))[0] . '};'
				 /sge
			&& ($rebuild= 1) ;
		}
		foreach (grep(/^on/, keys %attr)) {
		    $attr{$_}= (&proxify_block($attr{$_}, $default_script_type))[0] ;
		    $rebuild= 1 ;
		}
	    }
	    if (defined($attr{style})) {
		if ($ua_is_MSIE) {
		    if ($scripts_are_banned_here) {
			delete($attr{style}), $rebuild=1
			    if $attr{style}=~ /(?:expression|function)\s*\(/i ;
		    } elsif ($PROXIFY_SCRIPTS) {
			$attr{style}= &proxify_expressions_in_css($attr{style}), $rebuild= 1
			    if $attr{style}=~ /(?:expression|function)\s*\(/i ;
		    }
		}
		$attr{style}= (&proxify_block($attr{style}, $default_style_type))[0], $rebuild=1 ;
	    }
	    if ($tag_name eq 'a') {
		delete $attr{type}, $rebuild=1   if defined($attr{type});
		if (defined($attr{href})) {
		    if (   ($base_unframes && !defined($attr{target}))
			 || $attr{target}=~ /^_(top|blank)$/i         )
		    {
			$attr{href}= &full_url_by_frame($attr{href},0), $rebuild=1 ;
		    } else {
			$attr{href}= &full_url($attr{href}), $rebuild=1 ;
		    }
		}
	    } elsif ($tag_name eq 'img' or $tag_name eq 'image') {
		$tag_name= 'img',                        $rebuild=1  if $tag_name eq 'image' ;
		if ($TEXT_ONLY && !$RETURN_EMPTY_GIF) {
		    delete($attr{src}) ;
		    delete($attr{lowsrc}) ;
		    $rebuild= 1 ;
		} else {
		    $attr{src}=    &full_url($attr{src}),    $rebuild=1  if defined($attr{src}) ;
		    $attr{lowsrc}= &full_url($attr{lowsrc}), $rebuild=1  if defined($attr{lowsrc}) ;
		}
		$attr{longdesc}= &full_url($attr{longdesc}), $rebuild=1  if defined($attr{longdesc}) ;
		$attr{usemap}= &full_url($attr{usemap}),     $rebuild=1  if defined($attr{usemap}) ;
		$attr{dynsrc}= &full_url($attr{dynsrc}),     $rebuild=1  if defined($attr{dynsrc}) ;
	    } elsif ($tag_name eq 'body') {
		$attr{background}= &full_url($attr{background}), $rebuild=1 if defined($attr{background}) ;
	    } elsif ($tag_name eq 'base') {
		$base_url= $attr{href}, &fix_base_vars
		    if defined($attr{href}) && $attr{href}=~ m#^[\w+.-]+://# ;
		$base_unframes= $attr{target}=~ /^_(top|blank)$/i ;
		$attr{href}= &full_url($attr{href}), $rebuild=1  if defined($attr{href}) ;
	    } elsif ($tag_name eq 'frame') {
		$attr{src}=      &full_url_by_frame($attr{src},1), $rebuild=1 if defined($attr{src}) ;
		$attr{longdesc}= &full_url($attr{longdesc}),       $rebuild=1 if defined($attr{longdesc}) ;
	    } elsif ($tag_name eq 'iframe') {
		$attr{src}=      &full_url_by_frame($attr{src},1), $rebuild=1 if defined($attr{src}) ;
		$attr{longdesc}= &full_url($attr{longdesc}),       $rebuild=1 if defined($attr{longdesc}) ;
	    } elsif ($tag_name eq 'head') {
		$attr{profile}= join(' ', map {&full_url($_)} split(" ", $attr{profile})),
		    $rebuild=1  if defined($attr{profile}) ;
	    } elsif ($tag_name eq 'layer') {
		$attr{src}=  &full_url($attr{src}),  $rebuild=1  if defined($attr{src}) ;
	    } elsif ($tag_name eq 'input') {
		$attr{src}=    &full_url($attr{src}),    $rebuild=1  if defined($attr{src}) ;
		$attr{usemap}= &full_url($attr{usemap}), $rebuild=1  if defined($attr{usemap}) ;
	    } elsif ($tag_name eq 'form') {
		if (   ($base_unframes && !defined($attr{target}))
		     || $attr{target}=~ /^_(top|blank)$/i         )
		{
		    $attr{action}= &full_url_by_frame($attr{action},0), $rebuild=1 if defined($attr{action}) ;
		} else {
		    $attr{action}= &full_url($attr{action}),            $rebuild=1 if defined($attr{action}) ;
		}
		if ($scripts_are_banned_here) {
		    delete($attr{script}), $rebuild=1 if defined($attr{script}) ;
		} else {
		    $attr{script}= &full_url($attr{script}), $rebuild=1  if defined($attr{script}) ;
		}
	    } elsif ($tag_name eq 'area') {
		if (   ($base_unframes && !defined($attr{target}))
		     || $attr{target}=~ /^_(top|blank)$/i         )
		{
		    $attr{href}= &full_url_by_frame($attr{href},0), $rebuild=1  if defined($attr{href}) ;
		} else {
		    $attr{href}= &full_url($attr{href}), $rebuild=1  if defined($attr{href}) ;
		}
	    } elsif ($tag_name eq 'link') {
		($attr{type})= $attr{type}=~ m#^\s*([\w.+\$-]*/[\w.+\$-]*)#, $rebuild=1
		    if  defined($attr{type}) && $attr{type}!~ m#^[\w.+\$-]+/[\w.+\$-]+$# ;
		my($type)= lc($attr{type}) ;
		$type= 'text/css' if ($type eq '') && $attr{rel}=~ /\bstylesheet\b/i ;
		next if $scripts_are_banned_here && $type=~ /^$SCRIPT_TYPE_REGEX$/io ;
		my($link_unframe) ;
		$link_unframe=  ($base_unframes && !defined($attr{target}))
			      || $attr{target}=~ /^_(top|blank)$/i
		    if $is_in_frame ;
		local($url_start)= $url_start ;
		if ($type ne '') {
		    $url_start= $script_url . '/' .
			&pack_flags($e_remove_cookies, $e_remove_scripts, $e_filter_ads,
				    $e_hide_referer, $e_insert_entry_form,
				    $link_unframe  ? 0  : $is_in_frame,
				    $type)
			. '/' ;
		} elsif ($link_unframe) {
		    $url_start= $url_start_noframe ;
		}
		$attr{href}= &full_url($attr{href}), $rebuild=1  if defined($attr{href}) ;
		$attr{src}=  &full_url($attr{src}),  $rebuild=1  if defined($attr{src}) ;   # Netscape?
		$attr{urn}=  &full_url($attr{urn}),  $rebuild=1  if defined($attr{urn}) ;
	    } elsif ($tag_name eq 'meta') {
		$attr{url}= &full_url($attr{url}), $rebuild=1  if defined($attr{url}) ;   # Netscape
		if (defined($attr{'http-equiv'}) && defined($attr{content})) {
		    $attr{content}= &new_header_value(@attr{'http-equiv', 'content'}) ;
		    delete($attr{'http-equiv'}) unless defined($attr{content}) ;
		    $rebuild= 1 ;
		}
	    } elsif ($tag_name eq 'param') {
		if ($current_object_classid=~
		    /^\s*clsid:\{?D27CDB6E-AE6D-11CF-96B8-444553540000\}?\s*$/i)
		{
		    if (lc($attr{name}) eq 'movie') {
			$attr{value}= &full_url($attr{value}, 1) ;
			$rebuild= 1 ;
		    }
		} elsif (lc($attr{valuetype}) eq 'ref') {
		    ($attr{type})= $attr{type}=~ m#^\s*([\w.+\$-]*/[\w.+\$-]*)#, $rebuild=1
			if  defined($attr{type}) && $attr{type}!~ m#^[\w.+\$-]+/[\w.+\$-]+$# ;
		    my($type)= lc($attr{type}) ;
		    next if $scripts_are_banned_here && $type=~ /^$SCRIPT_TYPE_REGEX$/io ;
		    if (defined($attr{value}) && ($attr{value}=~ /^[\w.+-]+:/)) {
			local($url_start)= $url_start ;
			if ($type ne '') {
			    $url_start= $script_url . '/' .
				&pack_flags($e_remove_cookies, $e_remove_scripts, $e_filter_ads,
					    $e_hide_referer, $e_insert_entry_form,
					    $is_in_frame, $type)
				. '/' ;
			}
			$attr{value}= &full_url($attr{value}) ;
			$rebuild= 1 ;
		    }
		}
	    } elsif ($tag_name eq 'applet') {
		my($codebase_url)= $attr{codebase} ;
		if ($codebase_url ne '') {
		    $codebase_url= 
			  $codebase_url=~ m#^[\w+.-]*:#i ? $codebase_url
			: $codebase_url=~ m#^//#         ? $base_scheme . $codebase_url
			: $codebase_url=~ m#^/#          ? $base_host . $codebase_url
			: $codebase_url=~ m#^\?#         ? $base_file . $codebase_url
			:                                  $base_path . $codebase_url ;
		}
		$attr{codebase}= &full_url($attr{codebase}), $rebuild=1  if defined($attr{codebase}) ;
		local($base_url, $base_scheme, $base_host, $base_path, $base_file)=
		    ($base_url, $base_scheme, $base_host, $base_path, $base_file) ;
		$base_url= $codebase_url, &fix_base_vars  if $codebase_url ne '' ;
		$attr{code}=   &full_url($attr{code}),   $rebuild=1  if defined($attr{code}) ;
		$attr{object}= &full_url($attr{object}), $rebuild=1  if defined($attr{object}) ;
		$attr{archive}= join(',', map {&full_url($_)} split(/\s*,\s*/, $attr{archive})),
		    $rebuild=1  if defined($attr{archive}) ;
	    } elsif ($tag_name eq 'object') {
		$current_object_classid= $attr{classid} ;
		($attr{type})= $attr{type}=~ m#^\s*([\w.+\$-]*/[\w.+\$-]*)#, $rebuild=1
		    if  defined($attr{type}) && $attr{type}!~ m#^[\w.+\$-]+/[\w.+\$-]+$# ;
		($attr{codetype})= $attr{codetype}=~ m#^\s*([\w.+\$-]*/[\w.+\$-]*)#, $rebuild=1
		    if  defined($attr{codetype}) && $attr{codetype}!~ m#^[\w.+\$-]+/[\w.+\$-]+$# ;
		my($type)=     lc($attr{type}) ;
		my($codetype)= lc($attr{codetype}) ;
		my($codebase_url)= $attr{codebase} ;
		next if $scripts_are_banned_here &&
		    (   $type=~     /^$SCRIPT_TYPE_REGEX$/io
		     || $codetype=~ /^$SCRIPT_TYPE_REGEX$/io ) ;
		if ($codebase_url ne '') {
		    $codebase_url= 
			  $codebase_url=~ m#^[\w+.-]*:#i ? $codebase_url
			: $codebase_url=~ m#^//#         ? $base_scheme . $codebase_url
			: $codebase_url=~ m#^/#          ? $base_host . $codebase_url
			: $codebase_url=~ m#^\?#          ? $base_file . $codebase_url
			:                                  $base_path . $codebase_url ;
		}
		$attr{usemap}= &full_url($attr{usemap}), $rebuild=1  if defined($attr{usemap}) ;
		$attr{codebase}= &full_url_by_frame($attr{codebase},1), $rebuild=1
		    if defined($attr{codebase}) ;
		local($base_url, $base_scheme, $base_host, $base_path, $base_file)=
		    ($base_url, $base_scheme, $base_host, $base_path, $base_file) ;
		$base_url= $codebase_url, &fix_base_vars  if $codebase_url ne '' ;
		$attr{archive}= join(' ', map {&full_url_by_frame($_,1)} split(" ", $attr{archive})),
		    $rebuild=1  if defined($attr{archive}) ;
		if (defined($attr{data})) {
		    local($url_start)= $script_url . '/' .
			&pack_flags($e_remove_cookies, $e_remove_scripts, $e_filter_ads,
				    $e_hide_referer, $e_insert_entry_form, 1, $type)
			. '/' ;
		    $attr{data}= &full_url($attr{data}) ;
		    $rebuild= 1 ;
		}
		if (defined($attr{classid}) && ($attr{classid}!~ /^clsid:/i)) {
		    local($url_start)= $script_url . '/' .
			&pack_flags($e_remove_cookies, $e_remove_scripts, $e_filter_ads,
				    $e_hide_referer, $e_insert_entry_form, 1,
				    ($codetype ne '')   ? $codetype   : $type )
			. '/' ;
		    $attr{classid}= &full_url($attr{classid}) ;
		    $rebuild= 1 ;
		}
	    } elsif ($tag_name eq 'script') {
		next if $scripts_are_banned_here ;
		($attr{type})= $attr{type}=~ m#^\s*([\w.+\$-]*/[\w.+\$-]*)#, $rebuild=1
		    if  defined($attr{type}) && $attr{type}!~ m#^[\w.+\$-]+/[\w.+\$-]+$# ;
		if (defined($attr{src})) {
		    my($type, $language) ;
		    $type= lc($attr{type}) ;
		    if (!$type && ($language= $attr{language})) {
			$type= $language=~ /javascript|ecmascript|livescript|jscript/i
							 ? 'application/x-javascript'
			     : $language=~ /css/i        ? 'text/css'
			     : $language=~ /vbscript/i   ? 'application/x-vbscript'
			     : $language=~ /perl/i       ? 'application/x-perlscript'
			     : $language=~ /tcl/i        ? 'text/tcl'
			     :                             ''
		    }
		    $type||= $default_script_type ;
		    local($url_start)= $url_start ;
		    if ($type) {
			$url_start= $script_url . '/' .
			    &pack_flags($e_remove_cookies, $e_remove_scripts,
					$e_filter_ads, $e_hide_referer,
					$e_insert_entry_form, $is_in_frame, $type)
			    . '/' ;
		    }
		    $attr{src}= &full_url($attr{src}) ;
		    $rebuild= 1 ;
		    $needs_jslib= 1
			if ($type || $default_script_type)=~
			    m#^(?:application/x-javascript|application/x-ecmascript|application/javascript|application/ecmascript|text/javascript|text/ecmascript|text/livescript|text/jscript)$#i ;
		}
	    } elsif ($tag_name eq 'style') {
		($attr{type})= $attr{type}=~ m#^\s*([\w.+\$-]*/[\w.+\$-]*)#, $rebuild=1
		    if  defined($attr{type}) && $attr{type}!~ m#^[\w.+\$-]+/[\w.+\$-]+$# ;
	    } elsif ($tag_name eq 'select') {     # HTML 3.0
		$attr{src}=  &full_url($attr{src}),  $rebuild=1  if defined($attr{src}) ;
	    } elsif ($tag_name eq 'hr') {         # HTML 3.0
		$attr{src}=  &full_url($attr{src}),  $rebuild=1  if defined($attr{src}) ;
	    } elsif ($tag_name eq 'td') {         # Netscape extension?
		$attr{background}= &full_url($attr{background}), $rebuild=1 if defined($attr{background}) ;
	    } elsif ($tag_name eq 'th') {         # Netscape extension?
		$attr{background}= &full_url($attr{background}), $rebuild=1 if defined($attr{background}) ;
	    } elsif ($tag_name eq 'tr') {         # Netscape extension?
		$attr{background}= &full_url($attr{background}), $rebuild=1 if defined($attr{background}) ;
	    } elsif ($tag_name eq 'table') {      # Netscape extension?
		$attr{background}= &full_url($attr{background}), $rebuild=1 if defined($attr{background}) ;
	    } elsif ($tag_name eq 'bgsound') {    # Microsoft only
		$attr{src}=  &full_url($attr{src}),  $rebuild=1  if defined($attr{src}) ;
	    } elsif ($tag_name eq 'blockquote') {
		$attr{cite}= &full_url($attr{cite}), $rebuild=1  if defined($attr{cite}) ;
	    } elsif ($tag_name eq 'del') {
		$attr{cite}= &full_url($attr{cite}), $rebuild=1  if defined($attr{cite}) ;
	    } elsif ($tag_name eq 'embed') {      # Netscape only
		next if $scripts_are_banned_here
		     && $attr{type}=~ /^$SCRIPT_TYPE_REGEX$/io ;
		$attr{src}=  &full_url($attr{src}, (lc($attr{type}) eq 'application/x-shockwave-flash')),  $rebuild=1  if defined($attr{src}) ;
		$attr{pluginspage}= &full_url($attr{pluginspage}),  $rebuild=1  if defined($attr{pluginspage}) ;
	    } elsif ($tag_name eq 'fig') {        # HTML 3.0
		$attr{src}=      &full_url($attr{src}),      $rebuild=1  if defined($attr{src}) ;
		$attr{imagemap}= &full_url($attr{imagemap}), $rebuild=1  if defined($attr{imagemap}) ;
	    } elsif ($tag_name=~ /^h[1-6]$/) {    # HTML 3.0
		$attr{src}=  &full_url($attr{src}),  $rebuild=1  if defined($attr{src}) ;
	    } elsif ($tag_name eq 'ilayer') {
		$attr{src}=  &full_url($attr{src}),  $rebuild=1  if defined($attr{src}) ;
	    } elsif ($tag_name eq 'ins') {
		$attr{cite}= &full_url($attr{cite}), $rebuild=1  if defined($attr{cite}) ;
	    } elsif ($tag_name eq 'note') {       # HTML 3.0
		$attr{src}=  &full_url($attr{src}),  $rebuild=1  if defined($attr{src}) ;
	    } elsif ($tag_name eq 'overlay') {    # HTML 3.0
		$attr{src}=      &full_url($attr{src}),      $rebuild=1  if defined($attr{src}) ;
		$attr{imagemap}= &full_url($attr{imagemap}), $rebuild=1  if defined($attr{imagemap}) ;
	    } elsif ($tag_name eq 'q') {
		$attr{cite}= &full_url($attr{cite}), $rebuild=1  if defined($attr{cite}) ;
	    } elsif ($tag_name eq 'ul') {         # HTML 3.0
		$attr{src}=  &full_url($attr{src}),  $rebuild=1  if defined($attr{src}) ;
	    } elsif ($tag_name eq 'video') {      # HTML 5
		$attr{src}=     &full_url($attr{src}),     $rebuild=1  if defined($attr{src}) ;
		$attr{poster}=  &full_url($attr{poster}),  $rebuild=1  if defined($attr{poster}) ;
	    } elsif ($tag_name eq 'audio') {      # HTML 5
		$attr{src}=     &full_url($attr{src}),     $rebuild=1  if defined($attr{src}) ;
	    } elsif ($tag_name eq 'source') {     # HTML 5
		$attr{src}=     &full_url($attr{src}),     $rebuild=1  if defined($attr{src}) ;
	    }   #####   END OF TAG-SPECIFIC PROCESSING   #####
	    if ($rebuild) {
		my($name, $value, $attrs, $end_slash) ;
		while (($name, $value)= each %attr) {
		    next unless defined($value) ;
		    $value=~ s/&/&amp;/g ;
		    $value=~ s/([^\x00-\x7f])/'&#' . ord($1) . ';'/ge ;
		    $value=~ s/</&lt;/g ;
		    $value=~ s/>/&gt;/g ;
		    if ($value!~ /"/ || $value=~ /'/) {
			$value=~ s/"/&quot;/g ;  # only needed when using double quotes
			$attrs.= join('', ' ', $name, '="', $value, '"') ;
		    } else {
			$attrs.= join('', ' ', $name, "='", $value, "'") ;
		    }
		}
		$end_slash= $tag=~ m#/\s*>?$#   ? ' /'   : '' ;
		$tag= "<$tag_name$attrs$end_slash>" ;
	    }
	    push(@out, $tag) ;
	} elsif ($comment) {
	    if ( $comment=~ /^<!--\s*&{/ ) {
		next if $scripts_are_banned_here ;  # remove it by not doing push(@out)
		my($condition, $contents, $end)=
		    $comment=~ /^<!--\s*&{(.*?)}\s*;(.*?)(--\s*)?>$/s ;
		$condition= (&proxify_block($condition, $default_script_type))[0]
		    if $PROXIFY_SCRIPTS ;
		$contents=  &proxify_html(\$contents, 0, $no_exit_on_frameset) ;
		$comment= join('', '<!--&{', $condition, '};', $contents, $end, '>') ;
	    } elsif ( $comment=~ /^<!--\s*\[\s*if/i ) {
		my($start, $contents, $end)=
		    $comment=~ /^(<!--[^>]*?>)(.*?)(<!\s*\[\s*endif[^>]*?>)$/is ;
		$contents=  &proxify_html(\$contents, 0, 1) ;
		$comment= "$start$contents$end" ;
	    } elsif ($PROXIFY_COMMENTS) {
		my($contents, $end)= $comment=~ /^<!--(.*?)(--\s*)?>$/s ;
		$contents=  &proxify_html(\$contents, 0, 1) ;
		$comment= "<!--$contents$end>" ;
	    }
	    push(@out, $comment) ;
	} elsif ($script_block) {
	    my($tag, $script, $attrs, %attr, $type, $language, $name, $remainder) ;
	    next if $scripts_are_banned_here ;
	    ($tag, $script)=
		$script_block=~ m#^(<\s*script\b[^>]*>)(.*)<\s*/script\b.*?>\z#si ;
	    $tag= &proxify_html(\$tag, 0) ;
	    ($attrs)= $tag=~ /^<\s*script\b([^>]*)>/i ;
	    while ($attrs=~ /([A-Za-z][\w.:-]*)\s*(?:=\s*(?:"([^">]*)"?|'([^'>]*)'?|([^'"][^\s>]*)))?/g ) {
		$name= lc($1) ;
		next if exists($attr{$name}) ;   # duplicate attr
		$attr{$name}= &HTMLunescape(defined($2) ? $2
					  : defined($3) ? $3
					  : defined($4) ? $4
					  : '' ) ;
	    }
	    $type= lc($attr{type}) ;
	    if (!$type && ($language= $attr{language})) {
		$type= $language=~ /javascript|ecmascript|livescript|jscript/i
						 ? 'application/x-javascript'
		     : $language=~ /css/i        ? 'text/css'
		     : $language=~ /vbscript/i   ? 'application/x-vbscript'
		     : $language=~ /perl/i       ? 'application/x-perlscript'
		     : $language=~ /tcl/i        ? 'text/tcl'
		     :                             ''
	    }
	    $type||= $default_script_type ;
	    if ($type=~ m#^(application/x-javascript|application/x-ecmascript|application/javascript|application/ecmascript|text/javascript|text/ecmascript|text/livescript|text/jscript)$#i) {
		my($new_script) ;
		while ($PROXIFY_SCRIPTS) {
		    eval { $new_script= (&proxify_block($script, $type))[0] } ;
		    last unless $@ ;
		    if ($@ eq "end_of_input\n") {
			my($more)= $$body_ref=~ m#\G(.*?)<\s*/script\b.*?>#sgci ;
			$new_script= '', last unless $more ;
			$script.= "<\\/script>" . $more ;
		    } else {
			die $@ ;    # pass through any other error
		    }
		}
		$script= $new_script ;
	    }
	    push(@out, $tag, $script, '</script>') ;
	} elsif ($style_block) {
	    my($tag, $attrs, $stylesheet, $type) ;
	    ($tag, $stylesheet)=
		$style_block=~ m#^(<\s*style\b[^>]*>)(.*?)<\s*/style\b.*?>#si ;
	    $tag= &proxify_html(\$tag, 0) ;
	    ($attrs)= $tag=~ /^<\s*style\b([^>]*)>/ ;
	    while ($attrs=~ /([A-Za-z][\w.:-]*)\s*(?:=\s*(?:"([^">]*)"?|'([^'>]*)'?|([^'"][^\s>]*)))?/g ) {
		$type= lc(&HTMLunescape(defined($2) ? $2
				      : defined($3) ? $3
				      : defined($4) ? $4
				      : '' )), last
		    if lc($1) eq 'type' ;
	    }
	    $type||= $default_style_type ;
	    next if $scripts_are_banned_here && $type=~ /^$SCRIPT_TYPE_REGEX$/io ;
	    $stylesheet= (&proxify_block($stylesheet, $type))[0] ;
	    push(@out, $tag, $stylesheet, '</style>') ;
	} elsif ($decl_bang) {
	    my($inside, @words, $q, $rebuild) ;
	    ($inside)= $decl_bang=~ /^<!([^>]*)/ ;
	    @words= $inside=~ /\s*("[^">]*"?|'[^'>]*'?|[^'"][^\s>]*)/g ;
	    foreach (@words) {
		next if m#^['"]?http://www\.w3\.org/#i ;
		if (m#^["']?[\w+.-]+://#) {
		    if    (/^"/)  { $q= '"' ; s/^"|"$//g }
		    elsif (/^'/)  { $q= "'" ; s/^'|'$//g }
		    else          { $q= '' }
		    $_= $q . &HTMLescape(&full_url(&HTMLunescape($_))) . $q ;
		    $rebuild= 1 ;
		}
	    }
	    $decl_bang= '<!' . join(' ', @words) . '>'   if $rebuild ;
	    push(@out, $decl_bang) ;
	} elsif ($decl_question) {
	    push(@out, $decl_question) ;
	}  # end of main if comment/script/style/declaration/tag block
    } continue {
	$first_script_pos= $out_start
	    if $needs_jslib && !defined($first_script_pos) ;
    }  # end of main while loop
    if ($is_full_page) {
	my($after_decl, $i) ;
	for ($i= 0; $i<@out; $i++) {
	    next unless $out[$i]=~ /^</ ;
	    $after_decl= $i+1, next if $out[$i]=~ /^<\s*(?:\?|!)/ ;
	    last ;   # if it's any other tag
	}
	splice(@out, ($body_pos || $html_pos || $after_decl), 0, $full_insertion)
	    if $doing_insert_here && $has_content ;
	if ($PROXIFY_SCRIPTS && $needs_jslib) {
	    my($jslib_block)= &js_insertion() ;
	    my($insert_pos)= $head_pos || $html_pos || $after_decl ;
	    $insert_pos= $first_script_pos
		if defined($first_script_pos) && $first_script_pos<$insert_pos ;
	    splice(@out, $insert_pos, 0, $jslib_block) ;
	}
    }
    return join('', @out) ;
}  # sub proxify_html()
sub proxify_html_part {
    my($body_ref)= @_ ;
    my(@out, $block) ;
    while ( $$body_ref=~ m{\G([^<]*
			      (?:<!--.*?--\s*>
				|<\s*script\b.*?<\s*/script\b.*?>
				|<\s*style\b.*?<\s*/style\b.*?>
				|<!(?!--)[^>]*>
				|<\?[^>]*>
				|<\s*(?!script\b|style\b|!)[^>]*>
			      )
			   )
			  }sgcix )
    {
	$block= $1 ;
	push(@out, &proxify_html(\$block)) ;
	push(@out, &js_insertion())          if $block=~ /^[^<]*<\s*head\b/i ;
	push(@out, &full_insertion($URL,0))  if $block=~ /^[^<]*<\s*body\b/i ;
    }
    return ( join('', @out), substr($$body_ref, pos($$body_ref)) ) ;
}
sub transmit_html_in_parts {
    my($headers, $S)= @_ ;
    my($buf, $length, $numread, $thisread, $out) ;
    if ($headers=~ /^Transfer-Encoding:[ \t]*chunked\b/mi) {
	my($chunk_size, $chunk, $footers) ;
	print $headers ;
	while ($chunk_size= hex(<$S>) ) {
	    $buf.= $chunk= &read_socket($S, $chunk_size) ;
	    return undef unless length($chunk) == $chunk_size ;
	    $_= <$S> ;         # clear CRLF after chunk
	    ($out, $buf)= &proxify_html_part(\$buf) ;
	    print sprintf('%x', length($out)), "\015\012", $out, "\015\012"
		if $out ne '' ;
	}
	print sprintf('%x', length($buf)), "\015\012", $buf, "\015\012"
	    if $buf ne '' ;
	print "0\015\012" ;
	while (<$S>) {
	    $footers.= $_ ;
	    last if /^(\015\012|\012)/  || $_ eq '' ;  # lines end w/ LF or CRLF
	}
	$footers=~ s/(\015\012|\012)[ \t]+/ /g ;       # unwrap long footer lines
	print $footers ;
    } elsif ($headers=~ /^Content-Length:[ \t]*(\d+)/mi) {
	$length= $1 ;
	$headers=~ s/^Content-Length:.*/Transfer-Encoding: chunked\015/mi ;
	print $headers ;
	while (    ($numread<$length)
		&& ($thisread= read($S, $buf, $length-$numread, length($buf)) ) )
	{
	    return undef unless defined($thisread) ;
	    $numread+= $thisread ;
	    ($out, $buf)= &proxify_html_part(\$buf) ;
	    print sprintf('%x', length($out)), "\015\012", $out, "\015\012"
		if $out ne '' ;
	}
	print sprintf('%x', length($buf)), "\015\012", $buf, "\015\012"
	    if $buf ne '' ;
	print "0\015\012\015\012" ;   # no footers
    } else {
	local($/)= '>' ;
	print $headers ;
	while (<$S>) {
	    last if $_ eq '' ;
	    $buf.= $_ ;
	    ($out, $buf)= &proxify_html_part(\$buf) ;
	    print $out ;
	}
	return undef unless defined($thisread) ;
	print $buf ;
    }
}
sub full_url {
    my($uri_ref, $retain_query)= @_ ;
    $retain_query= 0 ;
    $uri_ref=~ s/^\s+|\s+$//g ;  # remove leading/trailing whitespace
    return $uri_ref if ($uri_ref=~ /^about:\s*blank$/i) ;
    return undef if $uri_ref=~ m#^x-proxy:#i ;
    if ($uri_ref=~ /^(?:javascript|livescript):/i) {
	return undef if $scripts_are_banned_here ;
	return $uri_ref unless $PROXIFY_SCRIPTS ;
	my($script)= $uri_ref=~ /^(?:javascript|livescript):(.*)$/si ;
	my($rest, $last)= &separate_last_js_statement(\$script) ;
	$last=~ s/\s*;\s*$// ;
	return 'javascript:' . (&proxify_js($rest, 1))[0]
			     . '; _proxy_jslib_proxify_html(' . (&proxify_js($last, 0))[0] . ')[0]' ;
    }
    my($uri, $frag)= $uri_ref=~ /^([^#]*)(#.*)?/ ;
    return $uri_ref if $uri eq '' ;  # allow bare fragments to pass unchanged
    $uri=~ s/[\015\012]//g ;
    my($query) ;
    ($uri, $query)= split(/\?/, $uri)  if $retain_query ;
    $query= '?' . $query   if $query ;
    my($absurl)=
	    $uri=~ m#^[\w+.-]*:#i   ?  $uri                 # absolute URL
	  : $uri=~ m#^//#           ?  $base_scheme . $uri  # net_path (rare)
	  : $uri=~ m#^/#            ?  $base_host . $uri    # abs_path, rel URL
	  : $uri=~ m#^\?#           ?  $base_file . $uri    # abs_path, rel URL
	  :                            $base_path . $uri ;  # relative path
    return $url_start . &wrap_proxy_encode($absurl) . $query . $frag ;
}
sub full_url_by_frame {
    my($uri_ref, $is_frame)= @_ ;
    local($url_start)= $is_frame   ? $url_start_inframe  : $url_start_noframe ;
    return &full_url($uri_ref) ;
}
sub fix_base_vars {
    $base_url=~ s/\A\s+|\s+\Z//g ;  # remove leading/trailing spaces
    $base_url=~ s#^([\w+.-]+://[^/?]+)/?#$1/# ;
    ($base_scheme)= $base_url=~ m#^([\w+.-]+:)//# ;
    ($base_host)=   $base_url=~ m#^([\w+.-]+://[^/?]+)# ; # no ending slash
    ($base_path)=   $base_url=~ m#^([^?]*/)# ;            # use greedy matching
    ($base_file)=   $base_url=~ m#^([^?]*)# ;
}
sub wrap_proxy_encode {
    my($URL)= @_ ;
    my($uri, $frag)= $URL=~ /^([^#]*)(.*)/ ;
    $uri= &proxy_encode($uri) ;
    $uri=~ s/=/=3d/g ;
    $uri=~ s/\?/=3f/g ;
    $uri=~ s/%/=25/g ;
    $uri=~ s/&/=26/g ;
    $uri=~ s/;/=3b/g ;
    1 while $uri=~ s#//#/=2f#g ;    # work around Apache PATH_INFO bug
    return $uri . $frag ;
}
sub wrap_proxy_decode {
    my($enc_URL)= @_ ;
    my($uri, $query, $frag)= $enc_URL=~ /^([^?#]*)([^#]*)(.*)/ ;
    $uri=~ s/=(..)/chr(hex($1))/ge ;
    $uri= &proxy_decode($uri) ;
    return $uri . $query . $frag ;
}
sub proxify_block {
    my($s, $type)= @_ ;
    if ($scripts_are_banned_here) {
	return undef if $type=~ /^$SCRIPT_TYPE_REGEX$/io ;
    }
    if ($type eq 'text/css') {
	$s=~ s/url\s*\(\s*(([^)]*\\\))*[^)]*)(\)|$)/
	      'url(' . &css_full_url($1) . ')'     /gie ;
	$s=~ s#\@import\s*("[^"]*"|'[^']*'|(?!url\s*\()[^;\s<]*)#
	       '@import ' . &css_full_url($1)                   #gie ;
	$s= &proxify_expressions_in_css($s)
	    if $s=~ /(?:expression|function)\s*\(/i ;
	return ($s, '') ;
    } elsif ($type=~ m#^(application/x-javascript|application/x-ecmascript|application/javascript|application/ecmascript|text/javascript|text/ecmascript|text/livescript|text/jscript)$#i) {
	return ($s, '') unless $PROXIFY_SCRIPTS ;
	return &proxify_js($s, 1) ;   # ... which returns two values
    } elsif ($type eq 'application/x-shockwave-flash') {
	return (&proxify_swf($s), '') if $PROXIFY_SWF ;
	return ($s, '') if $ALLOW_UNPROXIFIED_SCRIPTS ;
	return ('', '') ;
    } elsif ($type=~ /^$SCRIPT_TYPE_REGEX$/io) {
	return $ALLOW_UNPROXIFIED_SCRIPTS  ? ($s, '')  : ('', '') ;
    } else {
	return ($s, '') ;
    }
}
sub css_full_url {
    my($url)= @_ ;
    my($q) ;
    $url=~ s/\s+$// ;       # leading spaces already stripped above
    if    ($url=~ /^"/)  { $q= '"' ; $url=~ s/^"|"$//g }  # strip quotes
    elsif ($url=~ /^'/)  { $q= "'" ; $url=~ s/^'|'$//g }
    $url=~ s/\\(.)/$1/g ;   # "\"-unescape
    $url=~ s/^\s+|\s+$//g ; # finally, strip spaces once more
    $url= &full_url($url) ;
    $url=~ s/([(),\s'"\\])/\\$1/g ;    # put "\"-escaping back in
    return $q . $url . $q ;
}
sub proxify_expressions_in_css {
    my($s)= @_ ;
    my(@out) ;
    while ($s=~ /(\G.*?(?:expression|function)\s*\()/gcis) {
	push(@out, $1) ;
	push(@out, &proxify_js(&get_next_js_expr(\$s, 1))) ;
	return undef unless $s=~ /\G\)/gc ;
	push(@out, ')') ;
    }
    return join('', @out, substr($s, pos($s))) ;
}
sub http_get {
    my($default_port, $portst, $realhost, $realport, $request_uri,
       $realm, $tried_realm, $auth,
       $proxy_auth_header, $accept_language_header, $content_type, $charset,
       $lefttoget, $postblock, @postbody, $body_too_big, $rin,
       $status_code, $footers) ;
    local($/)= "\012" ;
    local(*S, *S_PLAIN) ;
    if ($scheme eq 'https') {
	eval { require Net::SSLeay } ;  # don't check during compilation
	&no_SSL_warning($URL) if $@ ;
	&insecure_die  if !$RUNNING_ON_SSL_SERVER && !$OVERRIDE_SECURITY ;
    }
    $default_port= $scheme eq 'https'  ? 443  : 80 ;
    $port= $default_port if $port eq '' ;
    $portst= ($port==$default_port)  ? ''  : ":$port" ;
    $realhost= $host ;
    $realport= $port ;
    $request_uri= $path ;
    if ($scheme eq 'http' && $HTTP_PROXY) {
	my($dont_proxy) ;
	foreach (@NO_PROXY) {
	    $dont_proxy= 1, last if $host=~ /$_$/i ;
	}
	unless ($dont_proxy) {
	    ($realhost, $realport)=
		$HTTP_PROXY=~ m#^(?:http://)?([^/?:]*):?([^/?]*)#i ;
	    $realport= 80 if $realport eq '' ;
	    $request_uri= $URL ;
	    $proxy_auth_header= "Proxy-Authorization: Basic $PROXY_AUTH\015\012"
	       if $PROXY_AUTH ne '' ;
	}
    }
    $accept_language_header= "Accept-Language: $ENV{HTTP_ACCEPT_LANGUAGE}\015\012"
	if $ENV{HTTP_ACCEPT_LANGUAGE} ne '' ;
    HTTP_GET: {
	if ($scheme eq 'https') {
	    my($dont_proxy) ;
	    if ($SSL_PROXY) {
		foreach (@NO_PROXY) {
		    $dont_proxy= 1, last if $host=~ /$_$/i ;
		}
	    }
	    if ($SSL_PROXY && !$dont_proxy) {
		($realhost, $realport)=
		    $SSL_PROXY=~ m#^(?:http://)?([^/?:]*):?([^/?]*)#i ;
		$realport= 80 if $realport eq '' ;
		&newsocketto('S_PLAIN', $realhost, $realport) ;
		print S_PLAIN "CONNECT $host:$port HTTP/$HTTP_VERSION\015\012",
			      'Host: ', $host, $portst, "\015\012" ;
		print S_PLAIN "Proxy-Authorization: Basic $SSL_PROXY_AUTH\015\012"
		    if $SSL_PROXY_AUTH ne '' ;
		print S_PLAIN "\015\012" ;
		vec($rin= '', fileno(S_PLAIN), 1)= 1 ;
		select($rin, undef, undef, 60)
		    || &HTMLdie("No response from SSL proxy") ;
		my($response, $status_code) ;
		do {
		    $response= '' ;
		    do {
			$response.= $_= <S_PLAIN> ;
		    } until (/^(\015\012|\012)$/) ; #lines end w/ LF or CRLF
		    ($status_code)= $response=~ m#^HTTP/\d+\.\d+\s+(\d+)# ;
		} until $status_code ne '100' ;
		&HTMLdie("SSL proxy error; response was:<p><pre>$response</pre>")
		    unless $status_code=~ /^2/ ;
	    } else {
		&newsocketto('S_PLAIN', $realhost, $realport) ;
	    }
	    tie(*S, 'SSL_Handle', \*S_PLAIN) ;
	} else {
	    &newsocketto('S', $realhost, $realport) ;
	}
	binmode S ;   # see note with "binmode STDOUT", above
	print S $ENV{'REQUEST_METHOD'}, ' ', $request_uri, " HTTP/$HTTP_VERSION\015\012",
		'Host: ', $host, $portst, "\015\012",    # needed for multi-homed servers
		'Accept: ', $env_accept, "\015\012",     # possibly modified
		'User-Agent: ', $USER_AGENT || $ENV{'HTTP_USER_AGENT'}, "\015\012",
		$accept_language_header,
		$proxy_auth_header ;                     # empty if not needed
	if ($ENV{HTTP_ACCEPT_ENCODING}=~ /\bgzip\b/i) {
	    eval { require Compress::Zlib } ;  # don't check during compilation
	    print S ($@  ? "Accept-Encoding: ,\015\012"
			 : "Accept-Encoding: gzip\015\012") ;
	} else {
	    print S "Accept-Encoding: ,\015\012" ;
	}
	if (!$e_hide_referer) {
	    my($referer)= $ENV{'HTTP_REFERER'} ;
	    if (@PROXY_GROUP) {
		foreach (@PROXY_GROUP) {
		    print(S 'Referer: ', &wrap_proxy_decode($referer), "\015\012"), last
			if  $referer=~ s#^$_(/[^/]*/?)?##  &&  ($referer ne '') ;
		    last if $referer eq '' ;
		}
	    } else {
		print S 'Referer: ', &wrap_proxy_decode($referer), "\015\012"
		    if  $referer=~ s#^$THIS_SCRIPT_URL(/[^/]*/?)?##  &&  ($referer ne '') ;
	    }
	}
	print S "Connection: close\015\012"  if $HTTP_VERSION eq '1.1' ;
	print S 'Cookie: ', $cookie_to_server, "\015\012"
	    if !$cookies_are_banned_here && ($cookie_to_server ne '') ;
	print S "Pragma: $ENV{HTTP_PRAGMA}\015\012" if $ENV{HTTP_PRAGMA} ne '' ;
	print S "Cache-Control: $ENV{HTTP_CACHE_CONTROL}\015\012"
	    if $ENV{HTTP_CACHE_CONTROL} ne '' ;
	if ($realm ne '') {
	    print S 'Authorization: Basic ', $auth{$realm}, "\015\012" ;
	    $tried_realm= $realm ;
	} else {
	    if ($username ne '') {
		print S 'Authorization: Basic ',
			&base64($username . ':' . $password),
			"\015\012" ;
	    } elsif ( ($tried_realm,$auth)= each %auth ) {
		print S 'Authorization: Basic ', $auth, "\015\012" ;
	    }
	}
	if ($ENV{'REQUEST_METHOD'} eq 'POST') {
	    if ($body_too_big) {
		&HTMLdie("Sorry, this proxy can't handle a request larger "
		       . "than $MAX_REQUEST_SIZE bytes at a password-protected"
		       . " URL.  Try reducing your submission size, or submit "
		       . "it to an unprotected URL.", 'Submission too large',
			 '413 Request Entity Too Large') ;
	    }
	    $lefttoget= $ENV{'CONTENT_LENGTH'} ;
	    print S 'Content-type: ', $ENV{'CONTENT_TYPE'}, "\015\012",
		    'Content-length: ', $lefttoget, "\015\012\015\012" ;
	    if (@postbody) {
		print S @postbody ;
	    } else {
		$body_too_big= ($lefttoget > $MAX_REQUEST_SIZE) ;
		do {
		    $lefttoget-= read(STDIN, $postblock, $lefttoget) ;
		    print S $postblock ;
		    push(@postbody, $postblock) unless $body_too_big ;
		} while $lefttoget && ($postblock ne '') ;
	    }
	} else {
	    print S "\015\012" ;
	}
	vec($rin= '', fileno(S), 1)= 1 ;
	select($rin, undef, undef, 60)
	    || &HTMLdie("No response from $realhost:$realport") ;
	$status= <S> ;  # first line, which is the status line in HTTP 1.x
	unless ($status=~ m#^HTTP/#) {
	    $is_html= 1 ;   # HTTP 0.9 by definition implies an HTML response
	    $content_type= 'text/html' ;
	    undef $/ ;
	    $body= $status . <S> ;
	    $status= '' ;
	    close(S) ;
	    untie(*S) if $scheme eq 'https' ;
	    return ;
	}
	do {
	    ($status_code)= $status=~ m#^HTTP/\d+\.\d+\s+(\d+)# ;
	    $headers= '' ;   # could have been set by first attempt
	    do {
		$headers.= $_= <S> ;    # $headers includes last blank line
	    } until (/^(\015\012|\012)$/) || $_ eq '' ; #lines end w/ LF or CRLF
	    $status= <S> if $status_code == 100 ;  # re-read for next iteration
	} until $status_code != 100 ;
	$headers=~ s/(\015\012|\012)[ \t]+/ /g ;
	if ($status=~ m#^HTTP/\d+\.\d+\s+401\b#) {
	    ($realm)=
		$headers=~ /^WWW-Authenticate:\s*Basic\s+realm="([^"\n]*)/mi ;
	    &HTMLdie("Error by target server: no WWW-Authenticate header.")
		unless $realm ne '' ;
	    if ($auth{$realm} eq '') {
		&get_auth_from_user("$host$portst", $realm, $URL) ;
	    } elsif ($realm eq $tried_realm) {
		&get_auth_from_user("$host$portst", $realm, $URL, 1) ;
	    }
	    close(S) ;
	    untie(*S) if $scheme eq 'https' ;
	    redo HTTP_GET ;
	}
	($content_type, $charset)=
	    $headers=~ m#^Content-Type:\s*([\w/.+\$-]*)\s*;?\s*(?:charset\s*=\s*([\w-]+))?#mi ;
	$content_type= lc($content_type) ;
	$is_utf8= ($charset=~ /^utf-?8$/i) ;
	if ($TEXT_ONLY) {
	    if ( ($content_type ne '') && ($content_type!~ m#^text/#i) ) {
		&non_text_die ;
	    }
	}
	if ($scripts_are_banned_here) {
	    &script_content_die  if $content_type=~ /^$SCRIPT_TYPE_REGEX$/io ;
	}
	if ($images_are_banned_here) {
	    &skip_image  unless $content_type=~ m#^text/#i ;
	}
	if        ($headers=~ m#^Content-Base:\s*([\w+.-]+://\S+)#mi) {
	    $base_url= $1, &fix_base_vars ;
	} elsif   ($headers=~ m#^Content-Location:\s*([\w+.-]+://\S+)#mi) {
	    $base_url= $1, &fix_base_vars ;
	} elsif   ($headers=~ m#^Location:\s*([\w+.-]+://\S+)#mi) {
	    $base_url= $1, &fix_base_vars ;
	}
	&http_fix ;
	if ($MINIMIZE_CACHING) {
	    my($new_value)= 'no-cache' ;
	    $new_value.= ', no-store'
		if $headers=~ /^Cache-Control:.*?\bno-store\b/mi ;
	    $new_value.= ', no-transform'
	      if $headers=~ /^Cache-Control:.*?\bno-transform\b/mi ;
	    my($no_cache_headers)=
		"Cache-Control: $new_value\015\012Pragma: no-cache\015\012" ;
	    $headers=~ s/^Cache-Control:[^\012]*\012?//mig ;
	    $headers=~ s/^Pragma:[^\012]*\012?//mig ;
	    $headers=~ s/^Expires:[^\012]*\012?//mig ;
	    $headers= $no_cache_headers . $headers ;
	}
	$is_html= 1  if   $content_type eq 'text/html'
		       or $content_type eq 'application/xhtml+xml' ;
	$is_html= 1  if ($content_type eq '') ;
	$is_html= 0  if ($expected_type eq 'text/xml') ;
	$status=~ s#^\S+#Status:#  if $NOT_RUNNING_AS_NPH ;
	my $may_be_swf= ($content_type eq 'text/plain'
			 and $headers=~ /^Server:\s*Sun-ONE/mi) ;
	if ($ENV{'REQUEST_METHOD'} ne 'HEAD') {
	    if ( ($expected_type ne 'text/xml') &&
		 (   ($expected_type=~ /^$TYPES_TO_HANDLE_REGEX$/io)
		  || ($content_type=~  /^$TYPES_TO_HANDLE_REGEX$/io)
		  || $may_be_swf )  )
	    {
		my($type) ;
		if ( ($expected_type eq 'text/css') || ($content_type eq 'text/css') ) {
		    $type= 'text/css' ;
		} elsif ($expected_type=~ /^$TYPES_TO_HANDLE_REGEX$/io) {
		    $type= $expected_type ;
		} else {
		    $type= $content_type ;
		}
		if ($headers=~ /^Transfer-Encoding:[ \t]*chunked\b/mi) {
		    ($body, $footers)= &get_chunked_body('S') ;
		    &HTMLdie(&HTMLescape("Error reading chunked response from $URL ."))
			unless defined($body) ;
		    $headers=~ s/^Transfer-Encoding:[^\012]*\012?//mig ;
		    $headers=~ s/^(\015\012|\012)/$footers$1/m ;
		} elsif ($headers=~ /^Content-Length:[ \t]*(\d+)/mi) {
		    $body= &read_socket('S', $1) ;
		} else {
		    undef $/ ;
		    $body= <S> ;
		    shutdown(S, 0) ;  # without this, IIS+MSIE hangs
		}
		if ($headers=~ /^Content-Encoding:.*\bgzip\b/mi) {
		    eval { require Compress::Zlib } ;
		    &no_gzip_die if $@ ;
		    $body= Compress::Zlib::memGunzip($body) ;
		    $headers=~ s/^Content-Encoding:.*?\012//gims ;
		}
		if ($body=~ s/^\xef\xbb\xbf//) {
		    $is_utf8= 1 ;
		}
		eval { utf8::decode($body) } if ($is_utf8) ;
		un_utf16(\$body) if ($body=~ /^(?:\376\377|\377\376)/) ;
		if ($may_be_swf && $body=~ /^[FC]WS[\x01-\x09]/) {
		    $type= 'application/x-shockwave-flash' ;
		}
		my($leading_html_comments)= $body=~ /^(\s*(?:<!--.*-->\s*)*)/ ;
		$body= substr($body, length($leading_html_comments))
		    if $leading_html_comments ;
		if (($content_type eq 'text/html') and $body=~ /^\s*<(?:\!(?!--\s*\n)|html)/) {
		    $type= 'text/html' ;
		    $is_html= 1 ;
		    $body= $leading_html_comments . $body ;
		} else {
		    $body= (&proxify_block($body, $type))[0] ;
		    eval { utf8::encode($body) } if ($is_utf8) ;
		    if ($ENV{HTTP_ACCEPT_ENCODING}=~ /\bgzip\b/i) {
			eval { require Compress::Zlib } ;
			if (!$@) {
			    $body= Compress::Zlib::memGzip($body) ;
			    $headers= "Content-Encoding: gzip\015\012" . $headers ;
			}
		    }
		    $headers=~ s/^Content-Length:.*/
				 'Content-Length: ' . length($body) /mie ;
		    print $status, $headers, $body ;
		    $response_sent= 1 ;
		}
	    } elsif ($is_html) {
		my($transmit_in_parts) ;
		foreach (@TRANSMIT_HTML_IN_PARTS_URLS) {
		    $transmit_in_parts= 1, last  if $URL=~ /$_/ ;
		}
		if ($transmit_in_parts) {
		    print $status ;
		    &transmit_html_in_parts($headers, 'S') ;
		   $response_sent= 1 ;
		} else {
		    if ($headers=~ /^Transfer-Encoding:[ \t]*chunked\b/mi) {
			($body, $footers)= &get_chunked_body('S') ;
			&HTMLdie(&HTMLescape("Error reading chunked response from $URL ."))
			    unless defined($body) ;
			$headers=~ s/^Transfer-Encoding:[^\012]*\012?//mig ;
			$headers=~ s/^(\015\012|\012)/$footers$1/m ;
		    } elsif ($headers=~ /^Content-Length:[ \t]*(\d+)/mi) {
			$body= &read_socket('S', $1) ;
		    } else {
			undef $/ ;
			$body= <S> ;
			shutdown(S, 0) ;  # without this, IIS+MSIE hangs
		    }
		    if ($headers=~ /^Content-Encoding:.*\bgzip\b/mi) {
			eval { require Compress::Zlib } ;  # don't check during compilation
			&no_gzip_die if $@ ;
			$body= Compress::Zlib::memGunzip($body) ;
			$headers=~ s/^Content-Encoding:.*?\012//gims ;
		    }
		    eval { utf8::decode($body) } if ($is_utf8) ;
		    un_utf16(\$body) if ($body=~ /^(?:\376\377|\377\376)/) ;
		}
	    } else {
		my($buf) ;
		print $status, $headers ;
		print $buf while read(S, $buf, 16384) ;
		$response_sent= 1 ;
	    }
	} else {
	    $body= '' ;
	}
	close(S) ;
	untie(*S) if $scheme eq 'https' ;
    }  # HTTP_GET:
}  # sub http_get()
BEGIN {
    package SSL_Handle ;
    use vars qw($SSL_CONTEXT  $DEFAULT_READ_SIZE) ;
    $DEFAULT_READ_SIZE= 512 ;
    sub TIEHANDLE {
	my($class, $socket, $unbuffered)= @_ ;
	my($ssl) ;
	unless ($SSL_CONTEXT) {
	    Net::SSLeay::load_error_strings() if $ENV{'MOD_PERL'} ;
	    Net::SSLeay::SSLeay_add_ssl_algorithms() ;
	    Net::SSLeay::randomize() ;
	    $SSL_CONTEXT= Net::SSLeay::CTX_new()
		or &main::HTMLdie("Can't create SSL context: $!") ;
	    Net::SSLeay::CTX_set_options($SSL_CONTEXT, &Net::SSLeay::OP_ALL)
		and &main::HTMLdie("Can't set options on SSL context: $!");
	}
	$ssl = Net::SSLeay::new($SSL_CONTEXT)
	    or &main::HTMLdie("Can't create SSL connection: $!");
	Net::SSLeay::set_fd($ssl, fileno($socket))
	    or &main::HTMLdie("Can't set_fd: $!") ;
	Net::SSLeay::connect($ssl) or &main::HTMLdie("Can't SSL connect: $!") ;
	bless { SSL      => $ssl,
		socket   => $socket,
		readsize => ($unbuffered  ? 0  : $DEFAULT_READ_SIZE),
		buf      => '',
		eof      => '',
	      },
	    $class ;  # returns reference
    }
    sub PRINT {
	my($self)= shift ;
	my($written, $errs)=
	    Net::SSLeay::ssl_write_all($self->{SSL}, join($, , @_) . $\ ) ;
	&main::HTMLdie("Net::SSLeay::ssl_write_all error: $errs") if $errs ne '' ;
	return 1 ;   # to keep consistent with standard print()
    }
    sub READ {
	my($self)= shift ;
	return 0 if $self->{eof} ;
	my($dummy, $len, $offset)= @_ ;   # $_[0] is handled explicitly below
	my($read, $errs) ;
	if ($len > length($self->{buf})) {
	    if ( $offset || ($self->{buf} ne '') ) {
		$len-= length($self->{buf}) ;
		($read, $errs)= &ssl_read_all_fixed($self->{SSL}, $len) ;
		&main::HTMLdie("ssl_read_all_fixed() error: $errs") if $errs ne '' ;
		return undef unless defined($read) ;
		$self->{eof}= 1  if length($read) < $len ;
		my($buflen)= length($_[0]) ;
		$_[0].= "\0" x ($offset-$buflen)  if $offset>$buflen ;
		substr($_[0], $offset)= $self->{buf} . $read ;
		$self->{buf}= '' ;
		return length($_[0])-$offset ;
	    } else {
		($_[0], $errs)= &ssl_read_all_fixed($self->{SSL}, $len) ;
		&main::HTMLdie("ssl_read_all_fixed() error: $errs") if $errs ne '' ;
		return undef unless defined($_[0]) ;
		$self->{eof}= 1  if length($_[0]) < $len ;
		return length($_[0]) ;
	    }
	} else {
	    ($offset  ? substr($_[0], $offset)  : $_[0])=
		substr($self->{buf}, 0, $len) ;
	    substr($self->{buf}, 0, $len)= '' ;
	    return $len ;
	}
    }
    sub READLINE {
	my($self)= shift ;
	my($read, $errs) ;
	if (defined($/)) {
	    if (wantarray) {
		return () if $self->{eof} ;
		($read, $errs)= &ssl_read_all_fixed($self->{SSL}) ;
		&main::HTMLdie("ssl_read_all_fixed() error: $errs") if $errs ne '' ;
		$self->{eof}= 1 ;
		return ($self->{buf} . $read)=~ m#(.*?\Q$/\E|.+?\Z(?!\n))#sg ;
	    } else {
		return '' if $self->{eof} ;
		my($pos, $read, $ret) ;
		while ( ($pos= index($self->{buf}, $/)) == -1 ) {
		  $read= Net::SSLeay::read($self->{SSL}, $self->{readsize} || 1 ) ;
		  return undef if $errs = Net::SSLeay::print_errs('SSL_read') ;
		  $self->{eof}= 1, return $self->{buf}  if $read eq '' ;
		  $self->{buf}.= $read ;
		}
		$pos+= length($/) ;
		$ret= substr($self->{buf}, 0, $pos) ;
		substr($self->{buf}, 0, $pos)= '' ;
		return $ret ;
	    }
	} else {
	    return '' if $self->{eof} ;
	    ($read, $errs)= &ssl_read_all_fixed($self->{SSL}) ;
	    &main::HTMLdie("ssl_read_all_fixed() error: $errs") if $errs ne '' ;
	    $self->{eof}= 1 ;
	    return  $self->{buf} . $read ;
	}
    }
    sub CLOSE {
	my($self)= shift ;
	my($errs) ;
	$self->{eof}= 1 ;
	$self->{buf}= '' ;
	if (defined($self->{SSL})) {
	    Net::SSLeay::free($self->{SSL}) ;
	    delete($self->{SSL}) ;  # to detect later if we've free'd it or not
	    &main::HTMLdie("Net::SSLeay::free error: $errs")
		if $errs= Net::SSLeay::print_errs('SSL_free') ;
	    close($self->{socket}) ;
	}
    }
    sub UNTIE {
	my($self)= shift ;
	$self->CLOSE ;
    }
    sub DESTROY {
	my($self)= shift ;
	$self->CLOSE ;
    }
    sub FILENO {
	my($self)= shift ;
	return fileno($self->{socket}) ;
    }
    sub EOF {
	my($self)= shift ;
	return 1 if $self->{eof} ;        # overrides anything left in {buf}
	return 0 if $self->{buf} ne '' ;
	return eof($self->{socket}) ;
    }
    sub BINMODE {
	my($self)= shift ;
	binmode($self->{socket}) ;
    }
    sub ssl_read_all_fixed {
	my ($ssl,$how_much) = @_;
	$how_much = 2000000000 unless $how_much;
	my ($got, $errs);
	my $reply = '';
	while ($how_much > 0) {
	    $got = Net::SSLeay::read($ssl,$how_much);
	    last if $errs = Net::SSLeay::print_errs('SSL_read');
	    $how_much -= Net::SSLeay::blength($got);
	    last if $got eq '';  # EOF
	    $reply .= $got;
	}
	return wantarray ? ($reply, $errs) : $reply;
    }
}
sub ftp_get {
    my($is_dir, $rcode, @r, $dataport, $remote_addr,
       $ext, $content_type, %content_type, $content_length, $enc_URL,
       @welcome, @cwdmsg) ;
    local($/)= "\012" ;
    $port= 21 if $port eq '' ;
    %content_type=
	  ('txt',  'text/plain',
	   'text', 'text/plain',
	   'htm',  'text/html',
	   'html', 'text/html',
	   'css',  'text/css',
	   'png',  'image/png',
	   'jpg',  'image/jpeg',
	   'jpeg', 'image/jpeg',
	   'jpe',  'image/jpeg',
	   'gif',  'image/gif',
	   'xbm',  'image/x-bitmap',
	   'mpg',  'video/mpeg',
	   'mpeg', 'video/mpeg',
	   'mpe',  'video/mpeg',
	   'qt',   'video/quicktime',
	   'mov',  'video/quicktime',
	   'aiff', 'audio/aiff',
	   'aif',  'audio/aiff',
	   'au',   'audio/basic',
	   'snd',  'audio/basic',
	   'wav',  'audio/x-wav',
	   'mp2',  'audio/x-mpeg',
	   'mp3',  'audio/mpeg',
	   'ram',  'audio/x-pn-realaudio',
	   'rm',   'audio/x-pn-realaudio',
	   'ra',   'audio/x-pn-realaudio',
	   'gz',   'application/x-gzip',
	   'zip',  'application/zip',
	   ) ;
    $is_dir= $path=~ m#/$# ;
    $is_html= 0 if $is_dir ;   # for our purposes, do not treat dirs as HTML
    ($ext)= $path=~ /\.(\w+)$/ ;  # works for FTP, not for URLs with query etc.
    $content_type= ($is_html || $is_dir)  ? 'text/html'
					  : $content_type{lc($ext)}
					    || 'text/plain' ;
    if ($scripts_are_banned_here) {
	&script_content_die if $content_type=~ /^$SCRIPT_TYPE_REGEX$/io ;
    }
    local($path)= $path ;
    $path=~ s/%20/ /g ;
    $status= "$HTTP_1_X 200 OK\015\012" ;
    $headers= $NO_CACHE_HEADERS . "Date: " . &rfc1123_date($now,0) . "\015\012"
	. ($content_type  ? "Content-type: $content_type\015\012"  : '') . "\015\012" ;
    &newsocketto('S', $host, $port) ;
    binmode S ;   # see note with "binmode STDOUT", above
    ($rcode)= &ftp_command('', '120|220') ;
    &ftp_command('', '220') if $rcode==120 ;
    ($rcode, @welcome)= &ftp_command("USER $username\015\012", '230|331') ;
    ($rcode, @welcome)= &ftp_command("PASS $password\015\012", '230|202')
	if $rcode==331 ;
    &ftp_command("TYPE I\015\012", '200') ;
    if ($USE_PASSIVE_FTP_MODE) {
	my(@p) ;
	($rcode, @r)= &ftp_command("PASV\015\012", '227') ;
	@p= (join('',@r))=~
		/(\d+),\s*(\d+),\s*(\d+),\s*(\d+),\s*(\d+),\s*(\d+)/ ;
	$dataport= ($p[4]<<8) + $p[5] ;
	&newsocketto('DATA_XFER', $host, $dataport) ;
	binmode DATA_XFER ;   # see note with "binmode STDOUT", above
    } else {
	socket(DATA_LISTEN, AF_INET, SOCK_STREAM, (getprotobyname('tcp'))[2])
	    || &HTMLdie("Couldn't create FTP data socket: $!") ;
	bind(DATA_LISTEN, pack_sockaddr_in(0, INADDR_ANY))
	    || &HTMLdie("Couldn't bind FTP data socket: $!") ;
	$dataport= (unpack_sockaddr_in(getsockname(DATA_LISTEN)))[0] ;
	listen(DATA_LISTEN,1)
	    || &HTMLdie("Couldn't listen on FTP data socket: $!") ;
	select((select(DATA_LISTEN), $|=1)[0]) ;    # unbuffer the socket
	&ftp_command( sprintf("PORT %d,%d,%d,%d,%d,%d\015\012",
			      unpack('C4', substr(getsockname(S),4,4)),
			      $dataport>>8, $dataport & 255),
		      '200') ;
    }
    if ($is_dir) {
	($rcode, @cwdmsg)= &ftp_command("CWD $path\015\012", '250') ;
	($rcode, @r)= &ftp_command("LIST\015\012", '125|150') ;
    } else {
	($rcode, @r)= &ftp_command("RETR $path\015\012", '125|150|550') ;
	if ($rcode==550) {
	    ($rcode)= &ftp_command("CWD $path\015\012", '') ;
	    &ftp_error(550,@r) unless $rcode==250 ;
	    ($enc_URL= $URL)=~ s/ /%20/g ;  # URL-encode any spaces
	    print "$HTTP_1_X 301 Moved Permanently\015\012", $NO_CACHE_HEADERS,
		  "Date: ", &rfc1123_date($now,0), "\015\012",
		  "Location: ", $url_start, &wrap_proxy_encode($enc_URL . '/'),
		  "\015\012\015\012" ;
	    close(S) ; close(DATA_LISTEN) ; close(DATA_XFER) ;
	    goto EXIT ;
	}
    }
    if (!$USE_PASSIVE_FTP_MODE) {
	($remote_addr= accept(DATA_XFER, DATA_LISTEN))
	    || &HTMLdie("Error accepting FTP data socket: $!") ;
	select((select(DATA_XFER), $|=1)[0]) ;      # unbuffer the socket
	close(DATA_LISTEN) ;
	&HTMLdie("Intruder Alert!  Someone other than the server is trying "
	       . "to send you data.")
	    unless (substr($remote_addr,4,4) eq substr(getpeername(S),4,4)) ;
    }
    if ( !$is_dir && !$is_html &&
	 (    ($expected_type=~ /^$TYPES_TO_HANDLE_REGEX$/io)
	   || ($content_type=~  /^$TYPES_TO_HANDLE_REGEX$/io)   ) ) {
	my($type) ;
	if ( ($expected_type eq 'text/css') || ($content_type eq 'text/css') ) {
	    $type= 'text/css' ;
	} elsif ($expected_type=~ /^$TYPES_TO_HANDLE_REGEX$/io) {
	    $type= $expected_type ;
	} else {
	    $type= $content_type ;
	}
	undef $/ ;
	$body= <DATA_XFER> ;
	$body= (&proxify_block($body, $type))[0] ;
	$headers= "Content-Length: " . length($body) . "\015\012" . $headers ;
	print $status, $headers, $body ;
	$response_sent= 1 ;
    } elsif ($is_html) {
	undef $/ ;
	$body= <DATA_XFER> ;
    } elsif ($is_dir) {
	undef $/ ;            # This was used for all non-HTML before streaming
	$body= <DATA_XFER> ;  #   was supported.
    } else {
	foreach (grep(/^(125|150)/, @r)) {
	    if ( ($content_length)= /\((\d+)[ \t]+bytes\)/ ) {
		$headers= "Content-Length: $content_length\015\012" .$headers ;
		last ;
	    }
	}
	my($buf) ;
	print $status, $headers ;
	print $buf while read(DATA_XFER, $buf, 16384) ;
	$response_sent= 1 ;
    }
    close(DATA_XFER) ;
    &ftp_command('', '226|250') ;
    &ftp_command("QUIT\015\012") ;   # don't care how they answer
    close(S) ;
    if ($is_dir) {
	&ftp_dirfix(\@welcome, \@cwdmsg) ;
	$headers= "Content-Length: " . length($body) . "\015\012" . $headers ;
    }
}  # sub ftp_get()
sub ftp_command {
    my($cmd, $ok_response)= @_ ;
    my(@r, $rcode) ;
    local($/)= "\012" ;
    print S $cmd ;
    $_= $r[0]= <S> ;
    $rcode= substr($r[0],0,3) ;
    until (/^$rcode /) {      # this catches single- and multi-line responses
	push(@r, $_=<S>) ;
    }
    &ftp_error($rcode,@r) if $ok_response ne '' && $rcode!~ /$ok_response/ ;
    return $rcode, @r ;
}
sub ftp_dirfix {
    my($welcome_ref, $cwdmsg_ref)= @_ ;
    my($newbody, $parent_link, $max_namelen,
       @f, $is_dir, $is_link, $link, $name, $size, $size_type, $file_type,
       $welcome, $cwdmsg, $insertion, $enc_path) ;
    $max_namelen= 16 ;
    my(@body)= split(/\015?\012/, $body) ;
    foreach (@body) {
	@f= split(" ", $_, 8) ;   # Note special use of " " pattern.
	next unless $#f>=7 ;
	@f[7,8]= $f[7]=~ /^(\S*) (.*)/ ;  # handle leading spaces in filenames
	next if $f[8]=~ /^\.\.?$/ ;
	$file_type= '' ;
	$is_dir=  $f[0]=~ /^d/i ;
	$is_link= $f[0]=~ /^l/i ;
	$file_type= $is_dir     ? 'Directory'
		  : $is_link    ? 'Symbolic link'
		  :               '' ;
	$name= $f[8] ;
	$name=~ s/^(.*) ->.*$/$1/ if $is_link ;   # remove symlink's " -> xxx"
	$name.= '/' if $is_dir ;
	$max_namelen= length($name) if length($name)>$max_namelen ;
	if ($is_dir || $is_link) {
	    ($size, $size_type)= () ;
	} else {
	    ($size, $size_type)= ($f[4], 'bytes') ;
	    ($size, $size_type)= ($size>>10, 'Kb') if $size > 10240 ;
	}
	($enc_path= $base_path . $name)=~ s/ /%20/g ;  # URL-encode any spaces
	$link=  &HTMLescape( $url_start . &wrap_proxy_encode($enc_path) ) ;
	$newbody.=
	    sprintf("  <a href=\"%s\">%s</a>%s %5s %-5s %3s %2s %5s  %s\012",
			   $link, $name, "\0".length($name),
			   $size, $size_type,
			   @f[5..7],
			   $file_type) ;
    }
    $newbody=~ s/\0(\d+)/ ' ' x ($max_namelen-$1) /ge ;
    if ($path eq '/') {
	$parent_link= '' ;
    } else {
	($enc_path= $base_path)=~ s#[^/]*/$## ;
	$enc_path=~ s/ /%20/g ;  # URL-encode any spaces
	$link=  &HTMLescape( $url_start . &wrap_proxy_encode($enc_path) ) ;
	$parent_link= "<a href=\"$link\">Up to higher level directory</a>" ;
    }
    if ($SHOW_FTP_WELCOME && $welcome_ref) {
	$welcome= &HTMLescape(join('', grep(s/^230-//, @$welcome_ref))) ;
	$welcome=~ s#\b([\w+.-]+://[^\s"']+[\w/])(\W)#
	    '<a href="' . &full_url($1) . "\">$1</a>$2" #ge ;
	$welcome.= "<hr>" if $welcome ne '' ;
    } else {
	$welcome= '' ;
    }
    if ($cwdmsg_ref) {
	$cwdmsg= &HTMLescape(join('', grep(s/^250-//, @$cwdmsg_ref))) ;
	$cwdmsg=~ s#\b([\w+.-]+://[^\s"']+[\w/])(\W)#
	    '<a href="' . &full_url($1) . "\">$1</a>$2" #ge ;
	$cwdmsg.= "<hr>" if $cwdmsg ne '' ;
    }
    $insertion= &full_insertion($URL,0)  if $doing_insert_here ;
    $body= <<EOS ;
<html>
<title>FTP directory of $URL</title>
<body>
$insertion
<h1>FTP server at $host</h1>
<h2>Current directory is $path</h2>
<hr>
<pre>
$welcome$cwdmsg
$parent_link
$newbody
</pre>
<hr>
</body>
</html>
EOS
}
sub ftp_error {
    my($rcode,@r)= @_ ;
    close(S) ; close(DATA_LISTEN) ; close(DATA_XFER) ;
    my($date_header)= &rfc1123_date($now, 0) ;
    print <<EOH ;
$HTTP_1_X 200 OK
${NO_CACHE_HEADERS}Date: $date_header
Content-type: text/html
<html>
<head><title>FTP Error</title></head>
<body>
<h1>FTP Error</h1>
<h3>The FTP server at $host returned the following error response:</h3>
<pre>
EOH
    print @r, "</pre>\n" ;
    &footer ;
    goto EXIT ;
}
sub http_fix {
    my($name, $value, $new_value) ;
    my(@headers)= $headers=~ /^([^\012]*\012?)/mg ;  # split into lines
    foreach (@headers) {
	next unless ($name, $value)= /^([\w.-]+):\s*([^\015\012]*)/ ;
	$new_value= &new_header_value($name, $value) ;
	$_= defined($new_value)
	    ? "$name: $new_value\015\012"
	    : '' ;
    }
    $headers= join('', @headers) ;
}
sub new_header_value {
    my($name, $value)= @_ ;
    $name= lc($name) ;
    return undef if $name eq '' ;
    return &full_url($value)
	if    $name eq 'content-base'
	   || $name eq 'content-location' ;
    if ($name eq 'location') {
	local($url_start)= $script_url . '/' . $packed_flags . '/' ;
	return &full_url($value) ;
    }
    if ($name eq 'set-cookie') {
	return undef if $cookies_are_banned_here ;
	if ($NO_COOKIE_WITH_IMAGE || $e_filter_ads) {
	    return undef
		if ($headers=~ m#^Content-Type:\s*(\S*)#mi  &&  $1!~ m#^text/#i)
		   || ! grep(m#^(text|\*)/#i, split(/\s*,\s*/, $env_accept)) ;
	}
	return &cookie_to_client($value, $path, $host) ;
    }
    if ($name eq 'content-style-type') {
	$default_style_type= lc($1)  if $value=~ m#^\s*([/\w.+\$-]+)# ;
	return $value ;
    }
    if ($name eq 'content-script-type') {
	$default_script_type= lc($1)  if $value=~ m#^\s*([/\w.+\$-]+)# ;
	return $value ;
    }
    if ($name eq 'p3p') {
	$value=~ s/\bpolicyref\s*=\s*['"]?([^'"\s]*)['"]?/
		   'policyref="' . &full_url($1) . '"' /gie ;
	return $value ;
    }
    $value=~ s/(;\s*URL\s*=)\s*(\S*)/ $1 . &full_url($2) /ie,   return $value
	if $name eq 'refresh' ;
    $value=~ s/<(\s*[^>\015\012]*)>/ '<'.&full_url($1).'>' /gie, return $value
	if $name eq 'uri' ;
    if ($name eq 'link') {
	my($v, @new_values) ;
	my(@values)= $value=~ /(<[^>]*>[^,]*)/g ;
	foreach $v (@values) {
	    my($type)= $v=~ m#[^\w.\/?&-]type\s*=\s*["']?\s*([/\w.+\$-]+)#i ;
	    $type= lc($type) ;
	    if ($type eq '') {
		my($rel) ;
		$rel= $+
		    if $v=~ /[^\w.\/?&-]rel\s*=\s*("([^"]*)"|'([^']*)'|([^'"][^\s]*))/i ;
		$type= 'text/css' if $rel=~ /\bstylesheet\b/i ;
	    }
	    return undef
		if $scripts_are_banned_here && $type=~ /^$SCRIPT_TYPE_REGEX$/io ;
	    local($url_start)= $url_start ;
	    if ($type ne '') {
		$url_start= $script_url . '/' .
		    &pack_flags($e_remove_cookies, $e_remove_scripts, $e_filter_ads,
				$e_hide_referer, $e_insert_entry_form,
				$is_in_frame, $type)
			. '/' ;
	    }
	    $v=~ s/<(\s*[^>\015\012]*)>/ '<' . &full_url($1) . '>' /gie ;
	    push(@new_values, $v) ;
	}
	return join(', ', @new_values) ;
    }
    return $value ;
}
sub xproxy {
    my($URL)= @_ ;
    $URL=~ s/^x-proxy://i ;
    my($family, $function, $qs)=  $URL=~ m#^//(\w+)(/?[^?]*)\??(.*)#i ;
    if ($family eq 'auth') {
	if ($function eq '/make_auth_cookie') {
	    my(%in)= &getformvars() ; # must use () or will pass current @_!
	    my($location)= $url_start . $in{'l'} ;  # was already encoded
	    my($cookie)= &auth_cookie(@in{'u', 'p', 'r', 's'}) ;
	    &redirect_to($location, "Set-Cookie: $cookie\015\012") ;
	}
    } elsif ($family eq 'start') {
	&startproxy ;
    } elsif ($family eq 'cookies') {
	if ($function eq '/clear') {
	    my($location)=
		$url_start . &wrap_proxy_encode('x-proxy://cookies/manage') ;
	    $location.= '?' . $qs    if $qs ne '' ;
	    &redirect_to($location, &cookie_clearer($ENV{'HTTP_COOKIE'})) ;
	} elsif ($function eq '/manage') {
	    &manage_cookies($qs) ;
	} elsif ($function eq '/update') {
	    my(%in)= &getformvars() ; # must use () or will pass current @_!
	    my($location)=
		$url_start . &wrap_proxy_encode('x-proxy://cookies/manage') ;
	    if ($in{'from'} ne '') {
		my($from_param)= $in{'from'} ;
		$from_param=~ s/([^\w.-])/ '%' . sprintf('%02x',ord($1)) /ge ;
		$location.=  '?from=' . $from_param ;
	    }
	    my(@cookies_to_delete) ;
	    foreach ( split(/\0/, $in{'delete'}) ) {
		push(@cookies_to_delete, &unbase64($_)) ; # use map{} in Perl 5
	    }
	    &redirect_to($location, &cookie_clearer(@cookies_to_delete)) ;
	}
    } elsif ($family eq 'frames') {
	my(%in)= &getformvars($qs) ;
	if ($function eq '/topframe') {
	    &return_top_frame($in{'URL'}) ;
	} elsif ($function eq '/framethis') {
	    &return_frame_doc($in{'URL'}, &HTMLescape(&wrap_proxy_decode($in{'URL'}))) ;
	}
    } elsif ($family eq 'scripts') {
	if ($function eq '/jslib') {
	    &return_jslib ;
	}
    } elsif ($family eq 'flash') {
warn "Now starting 'flash' block in xproxy()..." ;
	if ($function eq '/redir') {
	    my(%in)= &getformvars() ; # must use () or will pass current @_!
	    local($base_url, $base_scheme, $base_host, $base_path, $base_file) ;
	    $base_url= &wrap_proxy_decode($in{base}) ;
	    &fix_base_vars() ;
warn "In x-proxy://flash/redir; full URL=[".&full_url($in{URI}).']' ;
	    &redirect_to(&full_url($in{URI})) ;
	} elsif ($function eq '/fullurl') {
	    my(%in)= &getformvars() ;
	    local($base_url, $base_scheme, $base_host, $base_path, $base_file) ;
	    $base_url= &wrap_proxy_decode($in{base}) ;
	    &fix_base_vars() ;
	    my($proxified_url)= &full_url($in{URI}) ; # jsm-- must unwrap first?
warn "in{URI}=[$in{URI}]\nproxified_url=[$proxified_url]\n" ;
	    $proxified_url=~ s/(\W)/'%'.sprintf('%x', ord($1))/ge ;
	    &return_flash_vars("_proxy_swflib_proxified_url=$proxified_url") ;
	}
    }
warn "no such function as x-proxy://$family$function\n" ;
    &HTMLdie("Sorry, no such function as //". &HTMLescape("$family$function."),
	     '', '404 Not Found') ;
}
sub return_flash_vars {
    my($s)= @_ ;
    my($len)= length($s) ;
    my($date_header)= &rfc1123_date($now, 0) ;
warn "in return_flash_vars($s)" ;                   # this indicates success...  :?
    print <<EOF ;
$HTTP_1_X 200 OK
${NO_CACHE_HEADERS}Date: $date_header
Content-Type: application/x-www-form-urlencoded
Content-Length: $len
$s
EOF
    goto EXIT ;
}
sub startproxy {
    my(%in)= &getformvars() ;  # must use () or will pass current @_!
    $in{'URL'}= &wrap_proxy_decode($in{'URL'})
	if $ENCODE_URL_INPUT && $in{'URL'}=~ s/^\x01// ;
    $in{'URL'}=~ s/^\s+|\s+$//g ;    # strip leading or trailing spaces
    &show_start_form('Enter the URL you wish to visit in the box below.')
	if $in{'URL'} eq '' ;
    &unsupported_warning($in{URL}) if $in{URL}=~ /^mailto:/i ;
    my($scheme, $authority, $path, $query, $fragment)=
	$in{URL}=~ m{^(?:([^:/?#]+)://)?([^/?#]*)([^?#]*)(\?[^#]*)?(#.*)?$} ;
    $scheme= lc($scheme) ;
    $path= '/' if $path eq '' ;
    my($auth, $host, $portst)= $authority=~ /^([^@]*@)?([^:@]*)(:[^@]*)?$/ ;
    &show_start_form('The URL you entered has an invalid host name.', $in{URL})
	if !defined($host) ;
    $host= lc($host) ;   # must be after testing defined().
    &show_start_form('The URL must contain a valid host name.', $in{URL})
	if $host eq '' ;
    $scheme= ($host=~ /^ftp\./i)  ? 'ftp'  : 'http'   if $scheme eq '' ;
    &show_start_form('Sorry, only HTTP and FTP are currently supported.', $in{URL})
	unless $scheme=~ /^(http|https|ftp|x-proxy)$/ ;
    $host= join('.', $host>>24 & 255, $host>>16 & 255, $host>>8 & 255, $host & 255)
	if $host=~ /^\d+$/ ;
    if ($scheme eq 'http') {
	$host= "www.$host.com"  if ($host!~ /\./) && !gethostbyname($host) ;
    } elsif ($scheme eq 'ftp') {
	$host= "ftp.$host.com"
	    if ($auth eq '') && ($host!~ /\./) && !gethostbyname($host) ;
    }
    ($portst)= $portst=~ /^(:\d+)/ ;
    $authority= $auth . $host . $portst ;
    $url_start=~ s#[^/]*/$## ;   # remove old flag segment from $url_start
    $url_start.= &pack_flags(@in{'rc', 'rs', 'fa', 'br', 'if'}, $is_in_frame, '') . '/' ;
    &redirect_to( $url_start . &wrap_proxy_encode("$scheme://$authority$path$query") . $fragment ) ;
}
sub pack_flags {
    my($remove_cookies, $remove_scripts, $filter_ads, $hide_referer,
	  $insert_entry_form, $is_in_frame, $expected_type)= @_ ;
    my($flags) ;
    $flags=   $remove_cookies     ? 1  : 0  ;
    $flags.=  $remove_scripts     ? 1  : 0  ;
    $flags.=  $filter_ads         ? 1  : 0  ;
    $flags.=  $hide_referer       ? 1  : 0  ;
    $flags.=  $insert_entry_form  ? 1  : 0  ;
    $flags.=  $is_in_frame        ? 1  : 0  ;
    $expected_type= pack('C', $MIME_TYPE_ID{lc($expected_type)}) ;
    $expected_type=~ tr#\x00-\x3f#A-Za-z0-9+-# ; # almost same as base64 chars
    $flags.=  $expected_type ;
    return $flags ;
}
sub unpack_flags {
    my($flags)= @_ ;
    my($remove_cookies, $remove_scripts, $filter_ads, $hide_referer,
       $insert_entry_form, $is_in_frame, $expected_type) ;
    ($remove_cookies, $remove_scripts, $filter_ads, $hide_referer,
     $insert_entry_form, $is_in_frame, $expected_type)=
	split(//, $flags) ;
    $remove_cookies=    $remove_cookies     ? 1  : 0  ;
    $remove_scripts=    $remove_scripts     ? 1  : 0  ;
    $filter_ads=        $filter_ads         ? 1  : 0  ;
    $hide_referer=      $hide_referer       ? 1  : 0  ;
    $insert_entry_form= $insert_entry_form  ? 1  : 0  ;
    $is_in_frame=       $is_in_frame        ? 1  : 0  ;
    $expected_type=~ tr#A-Za-z0-9+-#\x00-\x3f# ;
    $expected_type= $ALL_TYPES[unpack('C', $expected_type)] ;
    return ($remove_cookies, $remove_scripts, $filter_ads, $hide_referer,
	    $insert_entry_form, $is_in_frame, $expected_type) ;
}
sub parse_cookie {
    my($cookie, $target_path, $target_server, $target_port, $target_scheme)= @_ ;
    my($name, $value, $type, @n,
       $cname, $path, $domain, $cvalue, $secure, @matches, %pathlen,
       $realm, $server, @auth) ;
    foreach ( split(/\s*;\s*/, $cookie) ) {
	($name, $value)= split(/=/, $_, 2) ;     # $value may contain "="
	$name= &cookie_decode($name) ;
	$value= &cookie_decode($value) ;
	($type, @n)= split(/;/, $name) ;
	if ($type eq 'COOKIE') {
	    ($cname, $path, $domain)= @n ;
	    $domain= lc($domain) ;
	    ($cvalue, $secure)= split(/;/, $value) ;
	    next if $secure && ($target_scheme ne 'https') ;
	    if ( ($target_server=~ /\Q$domain\E$/i or (lc('.'.$target_server) eq lc($domain)) )
		 && $target_path=~ /^\Q$path\E/ )
	    {
		push(@matches, ($cname ne '' ? $cname.'='.$cvalue : $cvalue)) ;
		$pathlen{$matches[$#matches]}= length($path) ;
	    }
	} elsif ($type eq 'AUTH') {
	    ($realm, $server)= @n ;
	    $realm=~  s/%([\da-fA-F]{2})/ pack('C', hex($1)) /ge ;
	    $server=~ s/%([\da-fA-F]{2})/ pack('C', hex($1)) /ge ;
	    my($portst)= ($target_port eq '')  ? ''  : ":$target_port" ;
	    push(@auth, $realm, $value)
		if  $server eq "$target_server$portst" ;
	}
    }
    $cookie= join('; ', sort { $pathlen{$b} <=> $pathlen{$a} } @matches) ;
    return $cookie, @auth ;
}
sub cookie_to_client {
    my($cookie, $source_path, $source_server)= @_ ;
    my($name, $value, $expires_clause, $path, $domain, $secure_clause) ;
    my($new_name, $new_value, $new_cookie) ;
    ($name, $value)=   $cookie=~ /^\s*([^=;,\s]*)\s*=?\s*([^;]*)/ ;
    ($expires_clause)= $cookie=~ /;\s*(expires\s*=[^;]*)/i ;
    ($path)=     $cookie=~ /;\s*path\s*=\s*([^;,\s]*)/i ;  # clash w/ ;-params?
    ($domain)=         $cookie=~ /;\s*domain\s*=\s*([^;,\s]*)/i ;
    ($secure_clause)=  $cookie=~ /;\s*(secure\b)/i ;
    $path=  $COOKIE_PATH_FOLLOWS_SPEC  ? $source_path  : '/'  if $path eq '' ;
    if ($domain eq '') {
	$domain= $source_server ;
    } else {
	$domain=~ s/\.*$//g ;  # removes trailing dots!
	$domain=~ tr/././s ;   # ... and double dots for good measure.
	return undef
	    if ($source_server!~ /\Q$domain\E$/) and ('.'.$source_server ne $domain) ;
	if ($RESPECT_THREE_DOT_RULE) {
	    return(undef) unless
		( ( ($domain=~ tr/././) >= 3 ) ||
		  ( ($domain=~ tr/././) >= 2 &&
		    $domain=~ /\.(com|edu|net|org|gov|mil|int)$/i )
		) ;
	} else {
	    if (($domain=~ tr/././) < 2) {
		return undef  if $domain=~ /^\./ ;
		$domain= '.' . $domain ;
		return undef  if ($domain=~ tr/././) < 2 ;
	    }
	}
    }
    $new_name= &cookie_encode("COOKIE;$name;$path;$domain") ;
    $new_value= &cookie_encode("$value;$secure_clause") ;
    if ($SESSION_COOKIES_ONLY && $expires_clause ne '') {
	my($expires_date)= $expires_clause=~ /^expires\s*=\s*(.*)$/i ;
	$expires_clause= ''  if &date_is_after($expires_date, $now) ;
    }
    $new_cookie= join('; ', grep(length,
				 $new_name . '=' . $new_value,
				 $expires_clause,
				 'path=' . $ENV_SCRIPT_NAME . '/',
				 ($RUNNING_ON_SSL_SERVER ? ('secure') : () )
		     )) ;
    return $new_cookie ;
}
sub auth_cookie {
    my($username, $password, $realm, $server)= @_ ;
    $realm=~ s/(\W)/ '%' . sprintf('%02x',ord($1)) /ge ;
    $server=~ s/(\W)/ '%' . sprintf('%02x',ord($1)) /ge ;
    return join('', &cookie_encode("AUTH;$realm;$server"), '=',
		    &cookie_encode(&base64("$username:$password")),
		    '; path=' . $ENV_SCRIPT_NAME . '/' ) ;
}
sub cookie_clearer {
    my(@cookies)= @_ ;   # may be one or more lists of cookies
    my($ret, $cname) ;
    foreach (@cookies) {
	foreach $cname ( split(/\s*;\s*/) ) {
	    $cname=~ s/=.*// ;      # change "name=value" to "name"
	    $ret.= "Set-Cookie: $cname=; expires=Thu, 01-Jan-1970 00:00:01 GMT; "
		 . "path=$ENV_SCRIPT_NAME/\015\012" ;
	}
    }
    return $ret ;
}
sub newsocketto {
    my($S, $host, $port)= @_ ;
    my($hostaddr, $remotehost) ;
    $host= join('.', $host>>24 & 255, $host>>16 & 255, $host>>8 & 255,
		     $host & 255)
	if $host=~ /^\d+$/ ;
    $hostaddr= inet_aton($host)
	|| &HTMLdie("Couldn't find address for $host: $!") ;
    $remotehost= pack_sockaddr_in($port, $hostaddr) ;
    for (@BANNED_NETWORK_ADDRS) {
	&banned_server_die() if $hostaddr=~ /^$_/ ;   # No URL forces a die
    }
    no strict 'refs' ;   # needed to use $S as filehandle
    socket($S, AF_INET, SOCK_STREAM, (getprotobyname('tcp'))[2])
	|| &HTMLdie("Couldn't create socket: $!") ;
    connect($S, $remotehost)
	|| &HTMLdie("Couldn't connect to $host:$port: $!") ;
    select((select($S), $|=1)[0]) ;      # unbuffer the socket
}
sub read_socket {
    my($S, $length)= @_ ;
    my($ret, $numread, $thisread) ;
    no strict 'refs' ;   # needed to use $S as filehandle
    while (    ($numread<$length)
	    && ($thisread= read($S, $ret, $length-$numread, $numread) ) )
    {
	$numread+= $thisread ;
    }
    return undef unless defined($thisread) ;
    return $ret ;
}
sub get_chunked_body {
    my($S)= @_ ;
    my($body, $footers, $chunk_size, $chunk) ;
    local($_) ;
    local($/)= "\012" ;
    no strict 'refs' ;     # needed to use $S as filehandle
    $body= '' ;            # to distinguish it from undef
    while ($chunk_size= hex(<$S>) ) {
	$body.= $chunk= &read_socket($S, $chunk_size) ;
	return undef unless length($chunk) == $chunk_size ;  # implies defined()
	$_= <$S> ;         # clear CRLF after chunk
    }
    while (<$S>) {
	last if /^(\015\012|\012)/  || $_ eq '' ;   # lines end w/ LF or CRLF
	$footers.= $_ ;
    }
    $footers=~ s/(\015\012|\012)[ \t]+/ /g ;       # unwrap long footer lines
    return wantarray  ? ($body, $footers)  : $body  ;
}
sub getformvars {
    my($in)= @_ ;
    my(%in, $name, $value) ;
    unless (defined($in)) {
	if ( ($ENV{'REQUEST_METHOD'} eq 'GET') ||
	     ($ENV{'REQUEST_METHOD'} eq 'HEAD') ) {
	    $in= $ENV{'QUERY_STRING'} ;
	} elsif ($ENV{'REQUEST_METHOD'} eq 'POST') {
	    return undef unless
		lc($ENV{'CONTENT_TYPE'}) eq 'application/x-www-form-urlencoded';
	    return undef unless defined($ENV{'CONTENT_LENGTH'}) ;
	    $in= &read_socket('STDIN', $ENV{'CONTENT_LENGTH'}) ;
	} else {
	    return undef ;   # unsupported REQUEST_METHOD
	}
    }
    foreach (split(/[&;]/, $in)) {
	s/\+/ /g ;
	($name, $value)= split('=', $_, 2) ;
	$name=~ s/%([\da-fA-F]{2})/ pack('C', hex($1)) /ge ;
	$value=~ s/%([\da-fA-F]{2})/ pack('C', hex($1)) /ge ;
	$in{$name}.= "\0" if defined($in{$name}) ;  # concatenate multiple vars
	$in{$name}.= $value ;
    }
    return %in ;
}
sub rfc1123_date {
    my($time, $use_dash)= @_ ;
    my($s) =  $use_dash  ? '-'  : ' ' ;
    my(@t)= gmtime($time) ;
    return sprintf("%s, %02d$s%s$s%04d %02d:%02d:%02d GMT",
		   $WEEKDAY[$t[6]], $t[3], $MONTH[$t[4]], $t[5]+1900, $t[2], $t[1], $t[0] ) ;
}
sub date_is_after {
    my($date1, $date2)= @_ ;
    my(@d1, @d2) ;
    return ($date1>$date2)  if $date1=~ /^\d+$/ && $date2=~ /^\d+$/ ;
    if ($date1=~ /^\d+$/) {
	@d1= (gmtime($date1))[3,4,5,2,1,0] ;
    } else {
	@d1= $date1=~ /^\w+,\s*(\d+)[ -](\w+)[ -](\d+)\s+(\d+):(\d+):(\d+)/ ;
	return undef unless @d1 ;
	$d1[1]= $UN_MONTH{lc($d1[1])} ;
	$d1[2]-= 1900 ;
    }
    if ($date2=~ /^\d+$/) {
	@d2= (gmtime($date2))[3,4,5,2,1,0] ;
    } else {
	@d2= $date2=~ /^\w+,\s*(\d+)[ -](\w+)[ -](\d+)\s+(\d+):(\d+):(\d+)/ ;
	return undef unless @d2 ;
	$d2[1]= $UN_MONTH{lc($d2[1])} ;
	$d2[2]-= 1900 ;
    }
    return ( ( $d1[2]<=>$d2[2] or $d1[1]<=>$d2[1] or $d1[0]<=>$d2[0] or
	       $d1[3]<=>$d2[3] or $d1[4]<=>$d2[4] or $d1[5]<=>$d2[5] )
	     > 0 ) ;
}
sub HTMLescape {
    my($s)= @_ ;
    $s=~ s/&/&amp;/g ;      # must be before all others
    $s=~ s/([^\x00-\x7f])/'&#' . ord($1) . ';'/ge ;
    $s=~ s/"/&quot;/g ;
    $s=~ s/</&lt;/g ;
    $s=~ s/>/&gt;/g ;
    return $s ;
}
sub HTMLunescape {
    my($s)= @_ ;
    $s=~ s/&quot\b;?/"/g ;
    $s=~ s/&lt\b;?/</g ;
    $s=~ s/&gt\b;?/>/g ;
    $s=~ s/&#(x)?(\w+);?/ $1 ? chr(hex($2)) : chr($2) /ge ;
    $s=~ s/&amp\b;?/&/g ;      # must be after all others
    return $s ;
}
sub base64 {
    my($s)= @_ ;
    my($ret, $p, @c, $t) ;
    while ($p<length($s)) {
	@c= unpack('C3', substr($s,$p,3)) ;
	$p+= 3 ;
	$t= ($c[0]<<16) + ($c[1]<<8) + $c[2] ;     # total 24-bit integer
	$ret.= pack('C4',     $t>>18,
			     ($t>>12)%64,
		    (@c>1) ? ($t>>6) %64 : 64,
		    (@c>2) ?  $t     %64 : 64 ) ;  # "@" is chr(64)
    }
    $ret=~ tr#\x00-\x3f@#A-Za-z0-9+/=# ;
    return $ret ;
}
sub unbase64 {
    my($s)= @_ ;
    my($ret, $p, @c, $t, $pad) ;
    $pad++ if $s=~ /=$/ ;
    $pad++ if $s=~ /==$/ ;
    $s=~ tr#A-Za-z0-9+/##cd ;          # remove non-allowed characters
    $s=~ tr#A-Za-z0-9+/#\x00-\x3f# ;   # for speed, translate to \x00-\x3f
    while ($p<length($s)) {
	@c= unpack('C4', substr($s,$p,4)) ;
	$p+= 4 ;
	$t= ($c[0]<<18) + ($c[1]<<12) + ($c[2]<<6) + $c[3] ;
	$ret.= pack('C3', $t>>16, ($t>>8) % 256, $t % 256 ) ;
    }
    chop($ret) if $pad>=1 ;
    chop($ret) if $pad>=2 ;
    return $ret ;
}
sub un_utf16 {
    my($s)= @_ ;
    eval { require Encode ; import Encode 'from_to' } ;
    &no_Encode_die if $@ ;
    from_to($$s, "utf-16", "utf-8") ;  # converts in-place
}
sub readfile {
    my($fname)= @_ ;
    my($ret) ;
    local(*F, $/) ;
    open(F, "<$fname") || return undef ;
    undef $/ ;
    $ret= <F> ;
    close(F) ;
    return $ret ;
}
sub http_get2 {
    my($c, $request_uri)= @_ ;
    my($s, $status, $status_code, $headers, $body, $footers, $rin, $win, $num_tries) ;
    local($/)= "\012" ;
    no strict 'refs' ;    # needed for symbolic references
    $s= $c->{socket} ;
    RESTART: {
	vec($win= '', fileno($s), 1)= 1 if defined(fileno($s)) ;
	if (!$c->{open} || !select(undef, $win, undef, 0)) {
	    &newsocketto($c->{socket}, $c->{host}, $c->{port}) ;
	    $c->{open}= 1 ;
	}
	print $s 'GET ', $request_uri, " HTTP/1.1\015\012",
		 'Host: ', $c->{host}, (($c->{port}==80)  ? ''  : ":$c->{port}"), "\015\012",
		 "\015\012" ;
	vec($rin= '', fileno($s), 1)= 1 ;
	select($rin, undef, undef, 60)
	    || &HTMLdie("No response from $c->{host}:$c->{port}") ;
	$status= <$s> ;
	unless ($status=~ m#^HTTP/#) {
	    $c->{open}= 0 ;
	    redo RESTART if ++$num_tries<3 ;
	    &HTMLdie("Invalid response from $c->{host}: [$status]") ;
	}
    }
    do {
	($status_code)= $status=~ m#^HTTP/\d+\.\d+\s+(\d+)# ;
	$headers= '' ;
	do {
	    $headers.= $_= <$s> ;    # $headers includes last blank line
	} until (/^(\015\012|\012)$/) || $_ eq '' ; #lines end w/ LF or CRLF
	$status= <$s> if $status_code == 100 ;  # re-read for next iteration
    } until $status_code != 100 ;
    $headers=~ s/(\015\012|\012)[ \t]+/ /g ;
    if ($headers=~ /^Transfer-Encoding:[ \t]*chunked\b/mi) {
	($body, $footers)= &get_chunked_body($s) ;
	&HTMLdie(&HTMLescape("Error reading chunked response from $c->{host} ."))
	    unless defined($body) ;
	$headers=~ s/^Transfer-Encoding:[^\012]*\012?//mig ;
	$headers=~ s/^(\015\012|\012)/$footers$1/m ;
    } elsif ($headers=~ /^Content-Length:[ \t]*(\d+)/mi) {
	$body= &read_socket($s, $1) ;
    } else {
	undef $/ ;
	$body= <$s> ;  # ergo won't be persistent connection
	close($s) ;
	$c->{open}= 0 ;
    }
    if ($headers=~ /^Connection:.*\bclose\b/mi || $status=~ m#^HTTP/1\.0#) {
	close($s) ;
	$c->{open}= 0 ;
    }
    return $body ;
}
sub full_insertion {
    my($URL, $in_top_frame)= @_ ;
    my($ret, $form, $insertion) ;
    $form= &mini_start_form($URL, $in_top_frame) if $e_insert_entry_form ;
    if (($INSERT_HTML ne '') || ($INSERT_FILE ne '')) {
	&set_custom_insertion if $CUSTOM_INSERTION eq '' ;
	if ($ANONYMIZE_INSERTION) {
	    local($base_url)= $script_url ;
	    &fix_base_vars ;
	    $insertion= &proxify_html(\$CUSTOM_INSERTION,0) ;
	} else {
	    $insertion= $CUSTOM_INSERTION ;
	}
    }
    $ret= $FORM_AFTER_INSERTION  ? $insertion . $form  : $form . $insertion ;
    my(%inc_by)= %in_mini_start_form ;
    foreach (keys %IN_CUSTOM_INSERTION) {
	$inc_by{$_}+= $IN_CUSTOM_INSERTION{$_} ;
    }
    $ret.= "<script type=\"text/javascript\">\n"
	 . "if (typeof(_proxy_jslib_increments)=='object') {\n"
	 . join('', map { "    _proxy_jslib_increments['$_']= $inc_by{$_} ;\n" }
			keys %inc_by)
	 . "}\n</script>\n"
	if %inc_by ;
    $ret= "\n<div>\n$ret</div>\n\n<div id=\"_proxy_css_main_div\" style=\"position:relative\">\n" ;
    return $ret ;
}
sub js_insertion {
    my($base_url_jsq, $default_script_type_jsq, $default_style_type_jsq,
       $p_cookies_are_banned_here, $p_doing_insert_here, $p_session_cookies_only,
       $p_cookie_path_follows_spec, $p_respect_three_dot_rule,
       $p_allow_unproxified_scripts) ;
    ($base_url_jsq=            $base_url           )=~ s/(["\\])/\\$1/g ;
    ($default_script_type_jsq= $default_script_type)=~ s/(["\\])/\\$1/g ;
    ($default_style_type_jsq=  $default_style_type )=~ s/(["\\])/\\$1/g ;
    $p_cookies_are_banned_here=   $cookies_are_banned_here   ? 'true'  : 'false' ;
    $p_doing_insert_here=         $doing_insert_here         ? 'true'  : 'false' ;
    $p_session_cookies_only=      $SESSION_COOKIES_ONLY      ? 'true'  : 'false' ;
    $p_cookie_path_follows_spec=  $COOKIE_PATH_FOLLOWS_SPEC  ? 'true'  : 'false' ;
    $p_respect_three_dot_rule=    $RESPECT_THREE_DOT_RULE    ? 'true'  : 'false' ;
    $p_allow_unproxified_scripts= $ALLOW_UNPROXIFIED_SCRIPTS ? 'true'  : 'false' ;
    return '<script type="text/javascript" src="'
	 . &HTMLescape($url_start . &wrap_proxy_encode('x-proxy://scripts/jslib'))
	 . "\"></script>\n"
	 . qq(<script type="text/javascript">_proxy_jslib_pass_vars("$base_url_jsq",$p_cookies_are_banned_here,$p_doing_insert_here,$p_session_cookies_only,$p_cookie_path_follows_spec,$p_respect_three_dot_rule,$p_allow_unproxified_scripts,"$default_script_type_jsq","$default_style_type_jsq");</script>\n) ;
}
sub set_custom_insertion {
    return if $CUSTOM_INSERTION ne '' ;
    return unless ($INSERT_HTML ne '') || ($INSERT_FILE ne '') ;
    $CUSTOM_INSERTION= ($INSERT_HTML ne '')   ? $INSERT_HTML  : &readfile($INSERT_FILE) ;
    %IN_CUSTOM_INSERTION= () ;
    foreach (qw(applet embed form id layer)) {
	$IN_CUSTOM_INSERTION{$_.'s'}++ while $CUSTOM_INSERTION=~ /<\s*$_\b/gi ;
    }
    $IN_CUSTOM_INSERTION{anchors}++ while $CUSTOM_INSERTION=~ /<\s*a\b[^>]*\bname\s*=/gi ;
    $IN_CUSTOM_INSERTION{links}++   while $CUSTOM_INSERTION=~ /<\s*a\b[^>]*\bhref\s*=/gi ;
    $IN_CUSTOM_INSERTION{images}++  while $CUSTOM_INSERTION=~ /<\s*img\b/gi ;
}
sub footer {
    my($rightlink)= $NO_LINK_TO_START
	? ''
	: qq(<a href="$script_url"><i>Restart</i></a>) ;
    print <<EOF ;
<p>
<hr>
<table width="100%"><tr>
<td align=left>
<a href="http://www.jmarshall.com/tools/cgiproxy/"><i>CGIProxy 2.1beta19</i></a>
</td>
<td align=right>
$rightlink
</td>
</tr></table>
<p>
</body>
</html>
EOF
}
sub return_top_frame {
    my($enc_URL)= @_ ;
    my($body, $insertion) ;
    my($date_header)= &rfc1123_date($now, 0) ;
    local($base_unframes)= 1 ;
    local($url_start)= $url_start_noframe ;
    $body= &full_insertion(&wrap_proxy_decode($enc_URL), 1) ;
    print <<EOF ;
$HTTP_1_X 200 OK
${NO_CACHE_HEADERS}Date: $date_header
Content-Type: text/html
<html>
<head><base target="_top"></head>
<body>
$body
</body>
</html>
EOF
    goto EXIT ;
}
sub return_frame_doc {
    my($enc_URL, $title)= @_ ;
    my($qs_URL, $top_URL, $page_URL) ;
    my($date_header)= &rfc1123_date($now, 0) ;
    ($qs_URL= $enc_URL) =~ s/([^\w.-])/ '%' . sprintf('%02x',ord($1)) /ge ;
    $top_URL= &HTMLescape($url_start_inframe
			. &wrap_proxy_encode('x-proxy://frames/topframe?URL=' . $qs_URL) ) ;
    $page_URL= &HTMLescape($url_start_inframe . $enc_URL) ;
    print <<EOF ;
$HTTP_1_X 200 OK
${NO_CACHE_HEADERS}Date: $date_header
Content-Type: text/html
<html>
<head>
<title>$title</title>
</head>
<frameset rows="$INSERTION_FRAME_HEIGHT,*">
    <frame src="$top_URL"  name="_proxy_jslib_insertion_frame">
    <frame src="$page_URL" name="_proxy_jslib_main_frame">
</frameset>
</html>
EOF
    goto EXIT ;
}
sub skip_image {
    &return_empty_gif if $RETURN_EMPTY_GIF ;
    my($date_header)= &rfc1123_date($now, 0) ;
    print "$HTTP_1_X 406 Not Acceptable\015\012${NO_CACHE_HEADERS}Date: $date_header\015\012\015\012" ;
    goto EXIT ;
}
sub return_empty_gif {
    my($date_header)= &rfc1123_date($now, 0) ;
    print <<EOF ;
$HTTP_1_X 200 OK
${NO_CACHE_HEADERS}Date: $date_header
Content-Type: image/gif
Content-Length: 43
GIF89a\x01\0\x01\0\x80\0\0\0\0\0\xff\xff\xff\x21\xf9\x04\x01\0\0\0\0\x2c\0\0\0\0\x01\0\x01\0\x40\x02\x02\x44\x01\0\x3b
EOF
    goto EXIT ;
}
sub redirect_to {
    my($location, $other_headers)= @_ ;
    print "$HTTP_1_X 302 Moved\015\012", $NO_CACHE_HEADERS,
	  "Date: ", &rfc1123_date($now,0), "\015\012",
	  $other_headers,
	  "Location: $location\015\012\015\012" ;
    goto EXIT ;
}
sub show_start_form {
    my($msg, $URL)= @_ ;
    my($method, $action, $flags, $cookies_url, $safe_URL, $jslib_block,
       $onsubmit, $onload) ;
    my($date_header)= &rfc1123_date($now, 0) ;
    $msg= "\n<h1><font color=green>$msg</font></h1>"  if $msg ne '' ;
    $method= $USE_POST_ON_START   ? 'post'   : 'get' ;
    $action=      &HTMLescape( $url_start . &wrap_proxy_encode('x-proxy://start') ) ;
    $cookies_url= &HTMLescape( $url_start . &wrap_proxy_encode('x-proxy://cookies/manage') ) ;
    $safe_URL= &HTMLescape($URL) ;
    if ($ENCODE_URL_INPUT) {
	$jslib_block= '<script src="'
		    . &HTMLescape($url_start . &wrap_proxy_encode('x-proxy://scripts/jslib'))
		    . "\"></script>\n" ;
	$onsubmit= q( onsubmit="if (!this.URL.value.match(/^\\x01/)) this.URL.value= '\x01'+_proxy_jslib_wrap_proxy_encode(this.URL.value) ; return true") ;
	$onload= q( onload="document.URLform.URL.focus() ; if (document.URLform.URL.value.match(/^\\x01/)) document.URLform.URL.value= _proxy_jslib_wrap_proxy_decode(document.URLform.URL.value.replace(/\\x01/, ''))") ;
    } else {
	$jslib_block= $onsubmit= '' ;
	$onload= ' onload="document.URLform.URL.focus()"' ;
    }
    if ($ALLOW_USER_CONFIG) {
	my($rc_on)= $e_remove_cookies     ? ' checked'  : '' ;
	my($rs_on)= $e_remove_scripts     ? ' checked'  : '' ;
	my($fa_on)= $e_filter_ads         ? ' checked'  : '' ;
	my($br_on)= $e_hide_referer       ? ' checked'  : '' ;
	my($if_on)= $e_insert_entry_form  ? ' checked'  : '' ;
	$flags= <<EOF ;
<br><input type=checkbox id="rc" name="rc"$rc_on><label for="rc"> Remove all cookies (except certain proxy cookies)</label>
<br><input type=checkbox id="rs" name="rs"$rs_on><label for="rs"> Remove all scripts (recommended for anonymity)</label>
<br><input type=checkbox id="fa" name="fa"$fa_on><label for="fa"> Remove ads</label>
<br><input type=checkbox id="br" name="br"$br_on><label for="br"> Hide referrer information</label>
<br><input type=checkbox id="if" name="if"$if_on><label for="if"> Show URL entry form</label>
EOF
    }
    print <<EOF ;
$HTTP_1_X 200 OK
${NO_CACHE_HEADERS}Date: $date_header
Content-type: text/html
<html>
<head>
$jslib_block
<title>Start Using CGIProxy</title>
</head>
<body$onload>
$msg
<h1>CGIProxy</h1>
<p>Start browsing through this CGI-based proxy by entering a URL below.
Only HTTP and FTP URLs are supported.  Not all functions will work
(e.g. some Java applets), but most pages will be fine.
<form name="URLform" action="$action" method="$method"$onsubmit>
<input name="URL" size=66 value="$safe_URL">
$flags
<p><input type=submit value="   Begin browsing   ">
</form>
<h3><a href="$cookies_url">Manage cookies</a></h3>
EOF
    &footer ;
    goto EXIT ;
}
sub mini_start_form {
    my($URL, $in_top_frame)= @_ ;
    my($method, $action, $flags, $table_open, $table_close,
       $cookies_url, $from_param, $safe_URL, $onsubmit, $onfocus) ;
    $method= $USE_POST_ON_START   ? 'post'   : 'get' ;
    $action= &HTMLescape( $url_start_noframe . &wrap_proxy_encode('x-proxy://start') ) ;
    $safe_URL= &HTMLescape($URL) ;
    $from_param= &wrap_proxy_encode($URL) ;   # don't send unencoded URL
    $from_param=~ s/([^\w.-])/ '%' . sprintf('%02x',ord($1)) /ge ;
    $cookies_url= $url_start_noframe . &wrap_proxy_encode('x-proxy://cookies/manage')
		. '?from=' . $from_param ;
    $cookies_url= &HTMLescape($cookies_url) ;
    my($scheme_authority, $up_path)= $URL=~ m{^([^:/?#]+://[^/?#]*)([^?#]*)} ;
    $up_path=~ s#[^/]*.$##s ;
    my($safe_up_URL)= &HTMLescape( $url_start_noframe . &wrap_proxy_encode("$scheme_authority$up_path") ) ;
    my($up_link)= $up_path ne ''
	? qq(&nbsp;&nbsp;<a href="$safe_up_URL" target="_top" style="color:#0000FF;">[&nbsp;UP&nbsp;]</a>)
	: '' ;
    ($table_open, $table_close)= $in_top_frame
	? ('', '')
	: ('<table border="1" cellpadding="5"><tr><td align="center" bgcolor="white"><font color="black">',
	   '</font></td></tr></table>') ;
    %in_mini_start_form= ('forms', 1, 'links', (($up_path ne '')  ? 2  : 1)) ;
    if ($ENCODE_URL_INPUT) {
	$needs_jslib= 1 ;
	$onsubmit= q( onsubmit="if (!this.URL.value.match(/^\\x01/)) this.URL.value= '\x01'+_proxy_jslib_wrap_proxy_encode(this.URL.value) ; return true") ;
	$onfocus= q( onfocus="if (this.value.match(/^\\x01/)) this.value= _proxy_jslib_wrap_proxy_decode(this.value.replace(/\\x01/, ''))") ;
    } else {
	$onsubmit= '' ;
    }
    if ($ALLOW_USER_CONFIG) {
	my($rc_on)= $e_remove_cookies     ? ' checked=""'  : '' ;
	my($rs_on)= $e_remove_scripts     ? ' checked=""'  : '' ;
	my($fa_on)= $e_filter_ads         ? ' checked=""'  : '' ;
	my($br_on)= $e_hide_referer       ? ' checked=""'  : '' ;
	my($if_on)= $e_insert_entry_form  ? ' checked=""'  : '' ;
my($safe_URL2) ;
($safe_URL2= $URL)=~ s/([^\w.-])/ '%' . sprintf('%02x',ord($1)) /ge ;
$safe_URL2= "http://jmarshall.com/bugs/report.cgi?URL=$safe_URL2&version=2.1beta19" ;
$safe_URL2= &HTMLescape(&full_url($safe_URL2)) ;
	return <<EOF ;
<form name="URLform" action="$action" method="$method" target="_top"$onsubmit>
<center>
$table_open
&nbsp;&nbsp;Location&nbsp;via&nbsp;proxy:<input name="URL" size="66" value="$safe_URL"$onfocus /><input type="submit" value="Go" />
$up_link&nbsp;&nbsp;
<br /><a href="$safe_URL2"><font color="red">[Report a bug]</font></a>
&nbsp;&nbsp;<a href="$cookies_url" target="_top" style="color:#0000FF;">[Manage&nbsp;cookies]</a>&nbsp;&nbsp;
<font size="-1"><input type="checkbox" id="rc" name="rc"$rc_on /><label for="rc" style="display: inline">&nbsp;No&nbsp;cookies</label>
&nbsp;&nbsp;<input type="checkbox" id="rs" name="rs"$rs_on /><label for="rs" style="display: inline">&nbsp;No&nbsp;scripts</label>
&nbsp;&nbsp;<input type="checkbox" id="fa" name="fa"$fa_on /><label for="fa" style="display: inline">&nbsp;No&nbsp;ads</label>
&nbsp;&nbsp;<input type="checkbox" id="br" name="br"$br_on /><label for="br" style="display: inline">&nbsp;No&nbsp;referrer</label>
&nbsp;&nbsp;<input type="checkbox" id="if" name="if"$if_on /><label for="if" style="display: inline">&nbsp;Show&nbsp;this&nbsp;form</label>&nbsp;&nbsp;
</font>
$table_close
</center>
</form>
EOF
    } else {
	return <<EOF ;
<form name="URLform" action="$action" method="$method" target="_top"$onsubmit>
<center>
$table_open
Location&nbsp;via&nbsp;proxy:<input name="URL" size=66 value="$safe_URL"$onfocus><input type=submit value="Go">
$up_link
&nbsp;&nbsp;<a href="$cookies_url" target="_top">[Manage&nbsp;cookies]</a>
$table_close
</center>
</form>
EOF
    }
}
sub manage_cookies {
    my($qs)= @_ ;
    my($return_url, $action, $clear_cookies_url, $cookie_rows, $auth_rows,
       $from_tag) ;
    my($name, $value, $type, @n, $delete_cb,
       $cname, $path, $domain, $cvalue, $secure,
       $realm, $server, $username) ;
    my($date_header)= &rfc1123_date($now, 0) ;
    my(%in)= &getformvars($qs) ;
    $return_url= &HTMLescape( $url_start . $in{'from'} ) ;
    $action=     &HTMLescape( $url_start . &wrap_proxy_encode('x-proxy://cookies/update') ) ;
    $clear_cookies_url= $url_start . &wrap_proxy_encode('x-proxy://cookies/clear') ;
    $clear_cookies_url.= '?' . $qs    if $qs ne '' ;
    $clear_cookies_url= &HTMLescape($clear_cookies_url) ;   # probably never necessary
    $from_tag= '<input type=hidden name="from" value="' . &HTMLescape($in{'from'}) . '">'
	if $in{'from'} ne '';
    foreach ( split(/\s*;\s*/, $ENV{'HTTP_COOKIE'}) ) {
	($name, $value)= split(/=/, $_, 2) ;  # $value may contain "="
	$delete_cb= '<input type=checkbox name="delete" value="'
		  . &base64($name) . '">' ;
	$name= &cookie_decode($name) ;
	$value= &cookie_decode($value) ;
	($type, @n)= split(/;/, $name) ;
	if ($type eq 'COOKIE') {
	    ($cname, $path, $domain)= @n ;
	    ($cvalue, $secure)= split(/;/, $value) ;
	    $cookie_rows.= sprintf("<tr align=center><td>%s</td>\n<td>%s</td>\n<td>%s</td>\n<td>%s</td>\n<td>%s</td>\n<td>%s</td></tr>\n",
				   $delete_cb,
				   &HTMLescape($domain),
				   &HTMLescape($path),
				   &HTMLescape($cname),
				   &HTMLescape($cvalue),
				   $secure ? 'Yes' : 'No',
				  ) ;
	} elsif ($type eq 'AUTH') {
	    ($realm, $server)= @n ;
	    $realm=~  s/%([\da-fA-F]{2})/ pack('C', hex($1)) /ge ;
	    $server=~ s/%([\da-fA-F]{2})/ pack('C', hex($1)) /ge ;
	    ($username)= split(/:/, &unbase64($value)) ;
	    $auth_rows.= sprintf("<tr align=center><td>%s</td>\n<td>%s</td>\n<td>%s</td>\n<td>%s</td></tr>\n",
				 $delete_cb,
				 &HTMLescape($server),
				 &HTMLescape($username),
				 &HTMLescape($realm),
				) ;
	}
    }
    $cookie_rows= "<tr><td colspan=6 align=center>&nbsp;<br><b><font face=Verdana size=2>You are not currently sending any cookies through this proxy.</font></b><br>&nbsp;</td></tr>\n"
	if $cookie_rows eq '' ;
    $auth_rows= "<tr><td colspan=4 align=center>&nbsp;<br><b><font face=Verdana size=2>You are not currently authenticated to any sites through this proxy.</font></b><br>&nbsp;</td></tr>\n"
	if $auth_rows eq '' ;
    print <<EOF ;
$HTTP_1_X 200 OK
Cache-Control: no-cache
Pragma: no-cache
Date: $date_header
Content-type: text/html
<html>
<head>
<title>CGIProxy Cookie Management</title>
</head>
<body>
<h3><a href="$return_url">Return to browsing</a></h3>
<h1>Here are the cookies you're using through CGIProxy:</h1>
<form action="$action" method=post>
$from_tag
<table bgcolor="#ccffff" border=1>
<tr><th>Delete this cookie?</th>
    <th>For server names ending in:</th>
    <th>... and a path starting with:</th>
    <th>Cookie name</th>
    <th>Value</th>
    <th>Secure?</th>
</tr>
$cookie_rows
</table>
<h3>Authentication cookies:</h3>
<table bgcolor="#ccffcc" border=1>
<tr><th>Delete this cookie?</th>
    <th>Server</th>
    <th>User</th>
    <th>Realm</th>
</tr>
$auth_rows
</table>
<p><font color=red>
<input type=submit value="Delete selected cookies">
</font>
</form>
<h3><a href="$clear_cookies_url">Delete all cookies</a></h3>
EOF
    &footer ;
    goto EXIT ;
}
sub get_auth_from_user {
    my($server, $realm, $URL, $tried)= @_ ;
    my($action, $msg) ;
    my($date_header)= &rfc1123_date($now, 0) ;
    $server= &HTMLescape($server) ;
    $realm=  &HTMLescape($realm) ;
    $URL=    &HTMLescape(&wrap_proxy_encode($URL)) ;
    $action= &HTMLescape( $url_start . &wrap_proxy_encode('x-proxy://auth/make_auth_cookie') ) ;
    $msg= "<h3><font color=red>Authorization failed.  Try again.</font></h3>"
	if $tried ;
    print <<EOF ;
$HTTP_1_X 200 OK
Cache-Control: no-cache
Pragma: no-cache
Date: $date_header
Content-type: text/html
<html>
<head><title>Enter username and password for $realm at $server</title></head>
<body>
<h1>Authorization Required</h1>
$msg
<form action="$action" method=post>
<input type=hidden name="s" value="$server">
<input type=hidden name="r" value="$realm">
<input type=hidden name="l" value="$URL">
<table border=1 cellpadding=5>
<tr><th bgcolor="#ff6666">
    Enter username and password for <nobr>$realm</nobr> at $server:</th></tr>
<tr><td bgcolor="#b0b0b0">
    <table cellpadding=0 cellspacing=0>
    <tr><td>Username:</td><td><input name="u" size=20></td></tr>
    <tr><td>Password:</td><td><input type=password name="p" size=20></td>
	<td>&nbsp;&nbsp;&nbsp;<input type=submit value="OK"></tr>
    </table>
</table>
</form>
<p>This requires cookie support turned on in your browser.
<p><i><b>Note:</b> Anytime you use a proxy, you're trusting the owner of that
proxy with all information you enter, including your name and password here.
This is true for <b>any</b> proxy, not just this one.
EOF
    &footer ;
    goto EXIT ;
}
sub unsupported_warning {
    my($URL)= @_ ;
    my($date_header)= &rfc1123_date($now, 0) ;
    &redirect_to($URL) if $URL eq 'about:blank' ;
    &redirect_to($URL) if $QUIETLY_EXIT_PROXY_SESSION ;
    print <<EOF ;
$HTTP_1_X 200 OK
${NO_CACHE_HEADERS}Date: $date_header
Content-type: text/html
<html>
<head><title>WARNING: Entering non-anonymous area!</title></head>
<body>
<h1>WARNING: Entering non-anonymous area!</h1>
<h3>This proxy only supports HTTP and FTP.  Any browsing to another URL will
be directly from your browser, and no longer anonymous.</h3>
<h3>Follow the link below to exit your anonymous browsing session, and
continue to the URL non-anonymously.</h3>
<blockquote><tt><a href="$URL">$URL</a></tt></blockquote>
EOF
    &footer ;
    goto EXIT ;
}
sub no_SSL_warning {
    my($URL)= @_ ;
    my($date_header)= &rfc1123_date($now, 0) ;
    &redirect_to($URL) if $QUIETLY_EXIT_PROXY_SESSION ;
    print <<EOF ;
$HTTP_1_X 200 OK
${NO_CACHE_HEADERS}Date: $date_header
Content-type: text/html
<html>
<head><title>WARNING: SSL not supported, entering non-anonymous area!</title></head>
<body>
<h1>WARNING: SSL not supported, entering non-anonymous area!</h1>
<h3>This proxy as installed does not support SSL, i.e. URLs that start
with "https://".  To support SSL, the proxy administrator needs to install
the Net::SSLeay Perl module, and then this proxy will automatically
support SSL (the
<a href="http://www.jmarshall.com/tools/cgiproxy/">CGIProxy site</a>
has more info).  In the meantime, any browsing to an "https://" URL will
be directly from your browser, and no longer anonymous.</h3>
<h3>Follow the link below to exit your anonymous browsing session, and
continue to the URL non-anonymously.</h3>
<blockquote><tt><a href="$URL">$URL</a></tt></blockquote>
EOF
    &footer ;
    goto EXIT ;
}
sub no_gzip_die {
    my($date_header)= &rfc1123_date($now, 0) ;
    print <<EOF ;
$HTTP_1_X 200 OK
${NO_CACHE_HEADERS}Date: $date_header
Content-type: text/html
<html>
<head><title>Compressed content not supported, but was sent by server.</title></head>
<body>
<h1>Compressed content not supported, but was sent by server.</h1>
<p>The server at $host:$port replied with compressed content, even though it
was told not to.  That server is either misconfigured, or has a bug.
<p>To support compressed content, the proxy administrator needs to install
the Compress::Zlib Perl module, and then this proxy will automatically support
it.
EOF
    &footer ;
    goto EXIT ;
}
sub banned_server_die {
    my($URL)= @_ ;
    my($date_header)= &rfc1123_date($now, 0) ;
    &redirect_to($URL) if $QUIETLY_EXIT_PROXY_SESSION && ($URL ne '') ;
    print <<EOF ;
$HTTP_1_X 403 Forbidden
${NO_CACHE_HEADERS}Date: $date_header
Content-type: text/html
<html>
<head><title>The proxy can't access that server, sorry.</title></head>
<body>
<h1>The proxy can't access that server, sorry.</h1>
<p>The owner of this proxy has restricted which servers it can access,
presumably for security or bandwidth reasons.  The server you just tried
to access is not on the list of allowed servers.
EOF
    &footer ;
    goto EXIT ;
}
sub banned_user_die {
    my($date_header)= &rfc1123_date($now, 0) ;
    print <<EOF ;
$HTTP_1_X 403 Forbidden
${NO_CACHE_HEADERS}Date: $date_header
Content-type: text/html
<html>
<head><title>You are not allowed to use this proxy, sorry.</title></head>
<body>
<h1>You are not allowed to use this proxy, sorry.</h1>
<p>The owner of this proxy has restricted which users are allowed to use it.
Based on your IP address, you are not an authorized user.
EOF
    &footer ;
    goto EXIT ;
}
sub loop_disallowed_die {
    my($URL)= @_ ;
    my($date_header)= &rfc1123_date($now, 0) ;
    print <<EOF ;
$HTTP_1_X 403 Forbidden
${NO_CACHE_HEADERS}Date: $date_header
Content-type: text/html
<html>
<head><title>Proxy cannot loop back through itself</title></head>
<body>
<h1>Proxy cannot loop back through itself</h1>
<p>The URL you tried to access would cause this proxy to access itself,
which is redundant and probably a waste of resources.  The owner of this
proxy has configured it to disallow such looping.
<p>Rather than telling the proxy to access the proxy to access the desired
resource, try telling the proxy to access the resource directly.  The link
below <i>may</i> do this.
<blockquote><tt><a href="$URL">$URL</a></tt></blockquote>
EOF
    &footer ;
    goto EXIT ;
}
sub insecure_die {
    my($date_header)= &rfc1123_date($now, 0) ;
    print <<EOF ;
$HTTP_1_X 200 OK
${NO_CACHE_HEADERS}Date: $date_header
Content-type: text/html
<html>
<head><title>Retrieval of secure URLs through a non-secure proxy is forbidden.</title>
<body>
<h1>Retrieval of secure URLs through a non-secure proxy is forbidden.</h1>
<p>This proxy is running on a non-secure server, which means that retrieval
of pages from secure servers is not permitted.  The danger is that the user
and the end server may believe they have a secure connection between them,
while in fact the link between the user and this proxy is insecure and
eavesdropping may occur.  That's why we have secure servers, after all.
<p>This proxy must run on a secure server before being allowed to retrieve
pages from other secure servers.
EOF
    &footer ;
    goto EXIT ;
}
sub script_content_die {
    my($date_header)= &rfc1123_date($now, 0) ;
    print <<EOF ;
$HTTP_1_X 403 Forbidden
${NO_CACHE_HEADERS}Date: $date_header
Content-type: text/html
<html>
<head><title>Script content blocked</title></head>
<body>
<h1>Script content blocked</h1>
<p>The resource you requested (or were redirected to without your knowledge)
is apparently an executable script.  Such resources have been blocked by this
proxy, presumably for your own protection.
<p>Even if you're sure you want the script, you can't get it through this
proxy the way it's configured.  If permitted, try browsing through this proxy
without removing scripts.  Otherwise, you'll need to reconfigure the proxy or
find another way to get the resource.
EOF
    &footer ;
    goto EXIT ;
}
sub non_text_die {
    &return_empty_gif if $RETURN_EMPTY_GIF ;
    my($date_header)= &rfc1123_date($now, 0) ;
    print <<EOF ;
$HTTP_1_X 403 Forbidden
${NO_CACHE_HEADERS}Date: $date_header
Content-type: text/html
<html>
<head><title>Proxy cannot forward non-text files</title></head>
<body>
<h1>Proxy cannot forward non-text files</h1>
<p>Due to bandwidth limitations, the owner of this particular proxy is
forwarding only text files.  For best results, turn off automatic image
loading if your browser lets you.
<p>If you need access to images or other binary data, route your browser
through another proxy (or install one yourself--
<a href="http://www.jmarshall.com/tools/cgiproxy/">it's easy</a>).
EOF
    &footer ;
    goto EXIT ;
}
sub no_Encode_die {
    my($date_header)= &rfc1123_date($now, 0) ;
    print <<EOF ;
$HTTP_1_X 200 OK
${NO_CACHE_HEADERS}Date: $date_header
Content-type: text/html
<html>
<head><title>Page uses UTF-16 encoding, which is unsupported by this version
      of Perl</title></head>
<body>
<h1>Page uses UTF-16 encoding, which is unsupported by this version of Perl</h1>
<p>The page you requested appears to be in Unicode's UTF-16 format.  This is
not supported by the version of Perl running on this server (more exactly, the
"Encode" Perl module could not be found).
<p>To support UTF-16, please upgrade to Perl version 5.8.0 or later.
EOF
    &footer ;
    goto EXIT ;
}
sub HTMLdie {
    my($msg, $title, $status)= @_ ;
    $title= 'CGIProxy Error' if $title eq '' ;
    $status= '200 OK' if $status eq '' ;
    my($date_header)= &rfc1123_date($now, 0) ;
    $HTTP_1_X=  $NOT_RUNNING_AS_NPH   ? 'Status:'   : "HTTP/1.0"
	if $HTTP_1_X eq '' ;
    print <<EOF ;
$HTTP_1_X $status
Cache-Control: no-cache
Pragma: no-cache
Date: $date_header
Content-Type: text/html
<html>
<head><title>$title</title></head>
<body>
<h1>$title</h1>
<h3>$msg</h3>
EOF
    &footer ;
    goto EXIT ;
}
sub proxify_js {
    my($in, $top_level, $with_level, $in_new_statement)= @_ ;
    $with_level||= 0 ;
    $in_new_statement||= 0 ;
    my(@out, $element, $token, $last_token, $new_last_token, $newline_since_last_token, $div_ok,
       $term_so_far, $prefix, $sub_expr, $op, $new_val, $cur_val_str,
       $in_braces, $in_func, $expr, $next_expr,
       $var_decl, $var, $eq, $value, $skip1, $skip2, $funcname, $with_obj, $code,
       $closequote1, $closequote2) ;
    $does_write= 0  if $top_level ;
    1 while ($in=~ s/^\s*(?:<!--.*?-->\s*)+//
	  or $in=~ s/^\s*(?:<!.*?>\s*)+//    ) ;
    $in=~ s/^\s*<!--[^\n]*(.*)-->\s*$/$1/s ;
  OUTER:
    while ($div_ok  ? $in=~ /\G($RE_JS_INPUT_ELEMENT_DIV)/gco
		    : $in=~ /\G($RE_JS_INPUT_ELEMENT_REG_EXP)/gco) {
	($element, $token, $closequote1, $closequote2)= ($1, $2, $3, $4) ;
	if ($token=~ /^['"]/ && !$closequote1 && !$closequote2) {
	    last unless &get_string_literal_remainder(\$in, \$token) ;
	    $element= $token ;
	}
	$newline_since_last_token= 1 if $element=~ /^$RE_JS_LINE_TERMINATOR$/o ;
	$new_last_token= '' ;
	$in_braces++ if $token eq '{' ;
	$in_braces-- if $token eq '}' ;
	$in_func= 0 if $in_braces==0 ;
	if ($token eq '') {
	    if ($term_so_far ne '') {
		if ($element=~ /$RE_JS_LINE_TERMINATOR/o) {
		    $term_so_far.= "\n" ;
		} else {
		    $term_so_far.= ' ' ;
		}
	    } else {
		push(@out, $element) ;
	    }
	} elsif ($token=~ s/^_proxy(\d*)_/'_proxy'.($1+1).'_'/e) {
	    $term_so_far.= $token ;
	    $div_ok= 1 ;
	} elsif ($token=~ /^(?:$RE_JS_NUMERIC_LITERAL|$RE_JS_REGULAR_EXPRESSION_LITERAL)$/o
		 or $token=~ /^['"]/) {
	    push(@out, $prefix, $term_so_far) ;
	    $prefix= '' ;
	    $term_so_far= $token ;
	    $div_ok= 1 ;
	} elsif ($token eq '++' or $token eq '--' or $token eq 'delete') {
	    if ($token eq '--' and $in=~ /\G\s*>/gco) {
		push(@out, $prefix, $term_so_far, '-->') ;
		$prefix= $term_so_far= '' ;
	    } elsif (($term_so_far ne '') and !$newline_since_last_token) {
		push(@out, $prefix, $term_so_far, $token) ;
		$prefix= $term_so_far= '' ;
		$div_ok= 1 ;
	    } else {
		push(@out, $prefix, $term_so_far) ;
		$prefix= $term_so_far= '' ;
		my $start_paren= $in=~ /\G$RE_JS_SKIP*\(/gco ;
		my($o, $p)= &get_next_js_term(\$in) ;
		last unless defined($p) ;
		last if $start_paren and !($in=~ /\G$RE_JS_SKIP*\)/gco) ; 
		if ($o ne '') {
		    push(@out, " _proxy_jslib_assign('$token', (" . (&proxify_js($o, 0, $with_level))[0] . "), (" . (&proxify_js($p, 0, $with_level))[0] . "), '')" ) ;
		} else {
		    $p=~ s/^'|'$//g ;
		    push(@out, "($p= _proxy_jslib_assign_rval('$token', '$p', '', '', (typeof $p=='undefined' ? void 0 : $p)))") ;
		}
		$div_ok= 1 ;
	    }
	} elsif (($token eq 'eval') && $in=~ /\G($RE_JS_SKIP*\()/gco) {
	    $needs_jslib= 1 ;
	    $term_so_far.= $token . $1 . '_proxy_jslib_proxify_js(('
			 . (&proxify_js(&get_next_js_expr(\$in,1), 0, $with_level))[0]
			 . "), 0, $with_level) )" ;
	    last unless $in=~ /\G\)/gc ;
	    $div_ok= 1 ;
	} elsif ($token=~ /^(?:open|write|writeln|close|replace|load|eval
			       |setInterval|setTimeout|toString
			       |src|href|background|lowsrc|action|location|poster
			       |URL|referrer|baseURI
			       |useMap|longDesc|cite|codeBase|profile
			       |cssText|insertRule|setStringValue|setProperty
			       |backgroundImage|content|cursor|listStyleImage
			       |host|hostname|pathname|port|protocol|search
			       |setNamedItem
			       |innerHTML|outerHTML|outerText|body
			       |getElementById|getElementsByTagName
			       |insertAdjacentHTML|setAttribute|setAttributeNode
			       |nodeValue
			       |value|cookie|domain|frames|parent|top|opener
			       |execScript|execCommand|navigate
			       |showModalDialog|showModelessDialog
			       |LoadMovie
			    )$/x) {
	    $needs_jslib= 1 ;
	    $does_write||= ($token eq 'write') || ($token eq 'writeln') || ($token eq 'eval') ;
	    if ($newline_since_last_token
		and $last_token=~ m#^(?:\)|\]|\+\+|--)$|
				    ^(?!(?:case|delete|do|else|in|instanceof|new|typeof|void|function|var)$)
				     (?:\pL|[\$_\\0-9'"]|\.\d|/..)#x )
	    {
		push(@out, $prefix, $term_so_far) ;
		$prefix= $term_so_far= '' ;
	    }
	    $term_so_far=~ s/\.((?>$RE_JS_WHITE_SPACE+)|$RE_JS_LINE_TERMINATOR)*\z// ;
	    my $old_pos= pos($in) ;
	    my $next_is_paren= $in=~ /\G$RE_JS_SKIP*\(/gco  ? 1  : 0 ;
	    pos($in)= $old_pos ;
	    if ($last_token=~ /^[{,]$/ and $in=~ /\G($RE_JS_SKIP*:)/gco) {
		push(@out, $prefix, $term_so_far, $token, $1) ;
		$prefix= $term_so_far= '' ;
		$new_last_token= ':' ;
		$div_ok= 0 ;
	    } elsif ($prefix ne '') {
		if ($term_so_far eq '') {
		    push(@out, ($with_level
				? " $token= _proxy_jslib_with_assign_rval(_proxy_jslib_with_objs, '$prefix', '$token', '', '', $token)"
				: " $token= _proxy_jslib_assign_rval('$prefix', '$token', '', '', (typeof $token=='undefined' ? void 0 : $token))") ) ;
		} else {
		    $term_so_far= " _proxy_jslib_assign('$prefix', $term_so_far, '$token', '', '')" ;
		}
		$prefix= '' ;
		$new_last_token= ')' ;
		$div_ok= 1 ;
	    } elsif ($in=~ /\G$RE_JS_SKIP_NO_LT*(\+\+|--)/gco) {
		$op= $1 ;
		if ($term_so_far eq '') {
		    push(@out, ($with_level
				? " $token= _proxy_jslib_with_assign_rval(_proxy_jslib_with_objs, '', '$token', '$op', '', $token)"
				: " $token= _proxy_jslib_assign_rval('', '$token', '$op', '', (typeof $token=='undefined' ? void 0 : $token))") ) ;
		} else {
		    $term_so_far= " _proxy_jslib_assign('', $term_so_far, '$token', '$op', '')" ;
		}
		$new_last_token= ')' ;
		$div_ok= 1 ;
	    } elsif ($in=~ /\G$RE_JS_SKIP*(>>>=|<<=|>>=|[+*\/%&|^-]?=(?!=))/gco) {
		$op= $1 ;
		$new_val= (&proxify_js(&get_next_js_expr(\$in), 0, $with_level))[0] ;
		if ($term_so_far eq '') {
		    push(@out, ($with_level
				? " $token= _proxy_jslib_with_assign_rval(_proxy_jslib_with_objs, '', '$token', '$op', ($new_val), $token)"
				: " $token= _proxy_jslib_assign_rval('', '$token', '$op', ($new_val), (typeof $token=='undefined' ? void 0 : $token))") ) ;
		} else {
		    $term_so_far= " _proxy_jslib_assign('', $term_so_far, '$token', '$op', ($new_val))" ;
		}
		$new_last_token= ')' ;
		$div_ok= 0 ;
	    } else {
		if ($term_so_far eq '') {
		    $term_so_far= ($with_level
				   ? " _proxy_jslib_with_handle(_proxy_jslib_with_objs, '$token', $token, $next_is_paren, $in_new_statement)"
				   : " _proxy_jslib_handle(null, '$token', $token, $next_is_paren, $in_new_statement)" ) ;
		} else {
		    $term_so_far= " _proxy_jslib_handle($term_so_far, '$token', '', $next_is_paren, $in_new_statement)" ;
		}
		$new_last_token= ')' ;
		$div_ok= 1 ;
	    }
	} elsif ($token eq 'applets' or $token eq 'embeds' or $token eq 'forms'
		 or $token eq 'ids' or $token eq 'layers' or $token eq 'anchors'
		 or $token eq 'images' or $token eq 'links')
	{
	    if ($doing_insert_here and $term_so_far ne '' and $in=~ /\G($RE_JS_SKIP*\[)/gco) {
		$skip1= $1 ;
		$next_expr= &get_next_js_expr(\$in,1) ;
		if ($next_expr=~ /^\s*\d+\s*$/) {
		    $term_so_far.= $token . $skip1 . "_proxy_jslib_increments['$token']+(" . (&proxify_js($next_expr, 0, $with_level))[0] . ')]' ;
		} else {
		    $term_so_far.= $token . $skip1 . '(' . (&proxify_js($next_expr, 0, $with_level))[0] . ')]' ;
		}
		last unless $in=~ /\G\]/gc ;
		$new_last_token= ']' ;
	    } else {
		$term_so_far.= $token ;
	    }
	    $div_ok= 1 ;
	} elsif ($token eq 'if' or $token eq 'while' or $token eq 'for'
		 or $token eq 'switch')
	{
	    push(@out, $prefix, $term_so_far, $token) ;
	    $prefix= $term_so_far= '' ;
	    last unless $in=~ /\G($RE_JS_SKIP*\()/gco ;
	    push(@out, $1, (&proxify_js(&get_next_js_expr(\$in,1), 0, $with_level))[0], ')') ;
	    last unless $in=~ /\G\)/gc ;
	    $div_ok= 0 ;
	} elsif ($token eq 'catch') {
	    push(@out, $prefix, $term_so_far, $token) ;
	    $prefix= $term_so_far= '' ;
	    last unless $in=~ /\G($RE_JS_SKIP*\()/gco ;
	    push(@out, $1, &get_next_js_expr(\$in,1), ')') ;
	    last unless $in=~ /\G\)/gc ;
	    $div_ok= 0 ;
	} elsif ($token eq 'function') {
	    push(@out, $prefix, $term_so_far, $token) ;
	    $prefix= $term_so_far= '' ;
	    last unless $in=~ /\G($RE_JS_SKIP*)($RE_JS_IDENTIFIER_NAME(?:\.(?:$RE_JS_IDENTIFIER_NAME))*)?($RE_JS_SKIP*\()/gco ;
	    ($skip1, $funcname, $skip2)= ($1, $2, $3) ;
	    $funcname=~ s/^_proxy(\d*)_/'_proxy'.($1+1).'_'/e ;
	    push(@out, $skip1, $funcname, $skip2, &get_next_js_expr(\$in,1), ') {') ;
	    last unless $in=~ /\G\)$RE_JS_SKIP*\{/gc ;
	    $in_braces++ ;
	    $in_func= 1 ;
	    $div_ok= 0 ;
	} elsif ($token eq 'with') {
	    push(@out, $prefix, $term_so_far) ;
	    $prefix= $term_so_far= '' ;
	    last unless $in=~ /\G($RE_JS_SKIP*)\(/gco ;
	    $skip1= $1 ;
	    $with_obj= (&proxify_js(&get_next_js_expr(\$in, 1), 0, $with_level))[0] ;
	    last unless $in=~ /\G\)($RE_JS_SKIP*)/gco ;
	    $skip2= $1 ;
	    if ($in=~ /\G\{/gc) {
		$code= '{' . (&proxify_js(&get_next_js_expr(\$in, 1), 0, $with_level+1))[0] . '}' ;
		last unless $in=~ /\G\}/gc ;
	    } else {
		$code= (&proxify_js(&get_next_js_expr(\$in), 0, $with_level+1))[0] ;
		$code.= ',' . (&proxify_js(&get_next_js_expr(\$in), 0, $with_level+1))[0]
		    while $in=~ /\G,/gc ;
	    }
	    push(@out, '{', ($with_level  ? ''  : 'var _proxy_jslib_with_objs= [] ;'),
		       "with$skip1(_proxy_jslib_with_objs[_proxy_jslib_with_objs.length]= ($with_obj))$skip2$code",
		       '; _proxy_jslib_with_objs.length-- ;}') ;
	    $div_ok= 0 ;
	} elsif ($token eq 'var') {
	    push(@out, $prefix, $term_so_far, $token) ;
	    $prefix= $term_so_far= '' ;
	    while (1) {
		$var_decl= &get_next_js_expr(\$in,0) ;
		( ($skip1, $var, $eq, $value)= $var_decl=~ /^($RE_JS_SKIP*)($RE_JS_IDENTIFIER_NAME$RE_JS_SKIP*)(=|in)?(.*)$/s )
		    || last OUTER ;
		$var=~ s/^_proxy(\d*)_/'_proxy'.($1+1).'_'/e ;
		push(@out, $skip1, $var) ;
		push(@out, $eq, (&proxify_js($value, 0, $with_level))[0]) if $eq ne '' ;
		last unless $in=~ /\G,/gc ;
		push(@out, ',') ;
	    }
	    $div_ok= 0 ;
	} elsif ($token eq 'new') {
	    push(@out, $prefix, $term_so_far) ;
	    $prefix= $term_so_far= '' ;
	    my($pos)= pos($in) ;
	    if ($in=~ /\G$RE_JS_SKIP*Function\b/gco) {
		push(@out, '_proxy_jslib_new_function') ;
	    } elsif ($in=~ /\G($RE_JS_SKIP*function\s*\()/gco) {
		$term_so_far= 'new' . $1 ;
		my($args)= &get_next_js_expr(\$in, 1) ;
		last unless $in=~ /\G(\)$RE_JS_SKIP*\{)/gco ;
		$term_so_far.= $args . $1 ;
		my($body)= &proxify_js(&get_next_js_expr(\$in, 1), 0, $with_level, 0) ;
		last unless $in=~ /\G\}/gc ;
		$term_so_far.= $body . '}' ;
		$new_last_token= '}' ;
		$div_ok= 1 ;
	    } else {
		my($new_expr, $remainder) ;
		if ($in=~ /\G$RE_JS_SKIP*\(/gco) {
		    ($new_expr, $remainder)=
			&proxify_js(&get_next_js_expr(\$in, 1), 0, $with_level, 1) ;
		    pos($in)= $pos, last  unless $in=~ /\G\)/gc ;
		} else {
		    ($new_expr, $remainder)=
			&proxify_js(&get_next_js_constructor(\$in), 0, $with_level, 1) ;
		}
		pos($in)= $pos, last  if $remainder ne '' ;
		$term_so_far.= "new ($new_expr)" ;
		$new_last_token= ')' ;
		$div_ok= 1 ;
	    }
	} elsif (($token eq 'return') and !$in_func and $top_level) {
	    push(@out, $prefix, $term_so_far) ;
	    $prefix= $term_so_far= '' ;
	    $needs_jslib= 1 ;
	    $expr= &get_next_js_expr(\$in,0) ;
	    $expr.= ', ' . &get_next_js_expr(\$in,0) while $in=~ /\G$RE_JS_SKIP*,$RE_JS_SKIP*/gco ;
	    $expr= (&proxify_js($expr, 0, $with_level))[0] ;
	    push(@out,
		 "return ((_proxy_jslib_ret= ($expr)), _proxy_jslib_flush_write_buffers(), _proxy_jslib_ret)") ;
	    $div_ok= 0 ;
	} elsif ($token=~ /^(?:abstract|boolean|break|byte|case|char|class|const|continue|debugger|default|delete|do|else|enum|export|extends|final|finally|float|goto|implements|in|instanceof|int|interface|long|native|package|private|protected|return|short|static|synchronized|throw|throws|transient|try|typeof|void|volatile)$/) {
	    push(@out, $prefix, $term_so_far, $token) ;
	    $prefix= $term_so_far= '' ;
	    $div_ok= 0 ;
	} elsif ($token=~ /^$RE_JS_IDENTIFIER_NAME$/o) {
	    if ($newline_since_last_token
		and $last_token=~ m#^(?:\)|\]|\+\+|--)$|
				    ^(?!(?:case|delete|do|else|in|instanceof|new|typeof|void|function|var)$)
				     (?:\pL|[\$_\\0-9'"]|\.\d|/..)#x )
	    {
		push(@out, $prefix, $term_so_far) ;
		$prefix= '' ;
		$term_so_far= $token ;
	    } else {
		$term_so_far.= $token ;
	    }
	    $div_ok= 1 ;
	} elsif ($token eq '.') {
	    $term_so_far.= '.' ;
	    $div_ok= 0 ;
	} elsif ($token eq '(') {
	    $does_write= 1 ;   # any function call could do a write()
	    $term_so_far.= '(' . (&proxify_js(&get_next_js_expr(\$in,1), 0, $with_level))[0] . ')' ;
	    last unless $in=~ /\G\)/gc ;
	    $new_last_token= ')' ;
	    $div_ok= 1 ;
	} elsif ($token eq '[') {
	    if ($in=~ /\G($RE_JS_SKIP*\d+$RE_JS_SKIP*\])/gco) {
		$term_so_far.= '[' . $1 ;
		$new_last_token= ']' ;
		$div_ok= 1 ;
	    } else {
		$sub_expr= (&proxify_js(&get_next_js_expr(\$in,1), 0, $with_level))[0] ;
		last unless $in=~ /\G\]/gc ;
		my $old_pos= pos($in) ;
		my $next_is_paren= $in=~ /\G$RE_JS_SKIP*\(/gco  ? 1  : 0 ;
		pos($in)= $old_pos ;
		if ($term_so_far ne '') {
		    $needs_jslib= 1 ;
		    $new_last_token= ')' ;
		    if ($prefix ne '') {
			$term_so_far= " _proxy_jslib_assign('$prefix', $term_so_far, ($sub_expr), '', '')" ;
			$prefix= '' ;
			$div_ok= 0 ;
		    } elsif ($in=~ /\G$RE_JS_SKIP_NO_LT*(\+\+|--)/gco) {
			$op= $1 ;
			$term_so_far= " _proxy_jslib_assign('', $term_so_far, ($sub_expr), '$op', '')" ;
			$div_ok= 1 ;
		    } elsif ($in=~ /\G$RE_JS_SKIP*(>>>=|<<=|>>=|[+*\/%&|^-]?=(?!=))/gco) {
			$op= $1 ;
			$new_val= (&proxify_js(&get_next_js_expr(\$in), 0, $with_level))[0] ;
			$term_so_far= " _proxy_jslib_assign('', $term_so_far, ($sub_expr), '$op', ($new_val))" ;
			$div_ok= 0 ;
		    } else {
			$term_so_far= " _proxy_jslib_handle($term_so_far, ($sub_expr), '', $next_is_paren, $in_new_statement)" ;
			$div_ok= 1 ;
		    }
		} else {
		    $term_so_far= "[$sub_expr]" ;
		    $new_last_token= ']' ;
		    $div_ok= 1 ;
		}
	    }
	} elsif ($token=~ /^(?:$RE_JS_PUNCTUATOR|$RE_JS_DIV_PUNCTUATOR)$/o) {
	    push(@out, $prefix, $term_so_far, $token) ;
	    $prefix= $term_so_far= '' ;
	    $div_ok= ($token eq ')' or $token eq ']') ;
	} else {
	    &HTMLdie("Shouldn't get here, token= [$token]") ;
	}
	if (defined($token)) {
	    $last_token= $new_last_token ne ''  ? $new_last_token  : $token ;
	    $newline_since_last_token= 0 ;
	}
    }
    push(@out, $prefix, $term_so_far) ;
    push(@out, " ;\n_proxy_jslib_flush_write_buffers() ;"), $needs_jslib= 1
	if $top_level && $does_write ;
    return ( join('', @out), substr($in, pos($in)) ) ;
}
sub get_next_js_expr {
    my($s, $allow_multiple, $is_new)= @_ ;
    my(@out, @p, $element, $token, $div_ok, $last_token, $pos,
       $closequote1, $closequote2) ;
    while (1) {
	last unless ($div_ok
		     ? $$s=~ /\G($RE_JS_INPUT_ELEMENT_DIV)/gco
		     : $$s=~ /\G($RE_JS_INPUT_ELEMENT_REG_EXP)/gco) ;
	($element, $token, $closequote1, $closequote2)= ($1, $2, $3, $4) ;
	if ($token=~ /^['"]/ && !$closequote1 && !$closequote2) {
	    last unless &get_string_literal_remainder($s, \$token) ;
	    $element= $token ;
	}
	if ($element eq ';' or $element eq ',') {
	    pos($$s)-= 1, return join('', @out)  if !$allow_multiple and !@p ;
	} elsif ($element=~ /^$RE_JS_LINE_TERMINATOR$/o) {
	    pos($$s)= $pos-length($element), return join('', @out)
		if !$allow_multiple and !@p 
		   and $last_token=~ m#^(?:\)|\]|\+\+|--)$|
				       ^(?!(?:case|delete|do|else|in|instanceof|new|typeof|void|function|var)$)
					(?:\pL|[\$_\\0-9'"]|\.\d|/..)#x
		   and ($pos= pos($$s), $$s=~ /\G$RE_JS_SKIP*$RE_JS_IDENTIFIER_NAME/gco) ;
	} elsif ($element=~ /^[(\[{?]$/) {
	    pos($$s) -= 1, return join( '', @out )
		if $is_new and !@p and $element eq '(' ;
	    push(@p, $element) ;
	} elsif ($element=~ /^[)\]}:]$/) {
	    pos($$s)-= 1, return join('', @out)  unless @p ;
	    pop(@p)  unless ($element eq ':' and $p[$#p] ne '?') ;
	    return join('', @out, '}')
		if ($element eq '}' and !@p and !$allow_multiple) ;
	}
	push(@out, $element) ;
	if (defined($token)) {
	    $div_ok= $token=~ m#^(?:\)|\]|\+\+|--)$|
				^(?!(?:case|delete|do|else|in|instanceof|new|return|throw|typeof|void)$)
				 (?:\pL|[\$_\\0-9'"]|\.\d|/..)#x ;
	    $last_token= $token ;
	}
    }
    return  @p  ? undef  : join('', @out) ;
}
sub get_next_js_term {
    my($s)= @_ ;
    my($o, $p, $ofrag) ;
    $$s=~ /\G$RE_JS_SKIP*($RE_JS_IDENTIFIER_NAME)/gco or return ;
    $p= "'$1'" ;
    $ofrag= $1 ;
    while ($$s=~ /\G$RE_JS_SKIP*([.\[\(])/gco) {
	$o.= $ofrag ;
	if ($1 eq '.') {
	    $$s=~ /\G$RE_JS_SKIP*($RE_JS_IDENTIFIER_NAME)/gco  or return ;
	    $p= "'$1'" ;
	    $ofrag= '.' . $1 ;
	} elsif ($1 eq '[') {
	    $p= &get_next_js_expr($s, 1) ;
	    $ofrag= "[$p]" ;
	    $$s=~ /\G\]/gco  or return undef ;
	} elsif ($1 eq '(') {
	    $p= '' ;
	    $ofrag= '(' . &get_next_js_expr($s, 1) . ')' ;
	    $$s=~ /\G\)/gco  or return undef ;
	}
    }
    return ($o, $p) ;
}
sub get_next_js_constructor {
    my($s)= @_ ;
    my($c) ;
    $$s=~ /\G$RE_JS_SKIP*($RE_JS_IDENTIFIER_NAME)/gco ;
    $c= $1 ;
    while ($$s=~ /\G$RE_JS_SKIP*([.\[])/gco) {
	if ($1 eq '.') {
	    $$s=~ /\G$RE_JS_SKIP*($RE_JS_IDENTIFIER_NAME)/gco or return ;
	    $c.= ".$1" ;
	} elsif ($1 eq '[') {
	    $c.= '[' . &get_next_js_expr($s, 1) . ']' ;
	    $$s=~ /\G\]/gco or return ;
	}
    }
    return $c ;
}
sub get_string_literal_remainder {
    my($inp, $startp)= @_ ;
    my($q)= substr($$startp, 0, 1) ;
    my $RE= ($q eq "'")  ? $RE_JS_STRING_REMAINDER_1  : $RE_JS_STRING_REMAINDER_2 ;
    while ($$inp=~ /\G($RE)/gc) {
	last if $1 eq '' and $2 eq '' ;
	$$startp.= $1 ;
	return 1 if $2 ;
    }
    die "end_of_input\n" ;   # throw error if regex failed.
}
sub separate_last_js_statement {
    my($s)= @_ ;
    my($e, $rest, $last) ;
    while (($e= &get_next_js_expr($s)) or (pos($$s)!=length($$s))) {
	return ($rest, $last.$e)
	    if $$s=~ /\G(?:;|$RE_JS_LINE_TERMINATOR|$RE_JS_SKIP)*\z/gco ;
	if ($$s=~ /\G(?:;|$RE_JS_LINE_TERMINATOR)/gco) {
	    $rest.= $last . $e . ';' ;
	    $last= '' ;
	} else {
	    return ($rest, $last)  if $e eq '' ;   # probably a syntax error
	    $last.= $e ;
	    $last.= ','  if $$s=~ /\G,/gco ;
	}
    }
    return ($rest, $last) ;
}
sub set_RE_JS {
    $RE_JS_WHITE_SPACE= qr/[\x09\x0b\x0c \xa0]|\p{Zs}/ ;
    $RE_JS_LINE_TERMINATOR= qr/[\012\015]/ ;
    $RE_JS_COMMENT= qr#(?>/\*.*?\*/|//[^\012\015]*|<!--[^\012\015]*)#s ;
    $RE_JS_IDENTIFIER_START= qr/\pL|[\$_]|\\u[0-9a-fA-F]{4}/ ;
    $RE_JS_IDENTIFIER_PART=  qr/$RE_JS_IDENTIFIER_START|\p{Mn}|\p{Mc}|\p{Nd}/ ;
    $RE_JS_IDENTIFIER_NAME=  qr/(?>$RE_JS_IDENTIFIER_START$RE_JS_IDENTIFIER_PART*)/ ;
    $RE_JS_PUNCTUATOR= qr/(?>>>>=?|===|!==|<<=|>>=|[<>=!+*%&|^-]=|\+\+|--|<<|>>|&&|\|\||[{}()[\].;,<>+*%&|^!~?:=-])/ ;
    $RE_JS_DIV_PUNCTUATOR= qr!(?>/=?)! ;
    $RE_JS_NUMERIC_LITERAL= qr/(?>0[xX][0-9a-fA-F]+|
				  [0-9]+(?:\.[0-9]*)?(?:[eE][+-]?[0-9]+)?|
				  \.[0-9]+(?:[eE][+-]?[0-9]+)?)
			       (?!$RE_JS_IDENTIFIER_START)
			      /x ;
    $RE_JS_ESCAPE_SEQUENCE= qr/x[0-9a-fA-F]{2}|u[0-9a-fA-F]{4}|(?:[0-3]?[0-7](?![0-9])|[4-7][0-7]|[0-3][0-7][0-7])|[^0-9xu]/ ;
    $RE_JS_STRING_LITERAL_START= qr/'(?>(?:[^'\\\012\015]|\\$RE_JS_ESCAPE_SEQUENCE){0,5000})('?)|
				    "(?>(?:[^"\\\012\015]|\\$RE_JS_ESCAPE_SEQUENCE){0,5000})("?)/x ;
    $RE_JS_STRING_REMAINDER_1= qr/(?>(?:[^'\\\012\015]|\\$RE_JS_ESCAPE_SEQUENCE){0,5000})('?)/ ;
    $RE_JS_STRING_REMAINDER_2= qr/(?>(?:[^"\\\012\015]|\\$RE_JS_ESCAPE_SEQUENCE){0,5000})("?)/ ;
    $RE_JS_REGULAR_EXPRESSION_LITERAL=
	qr!/(?>(?:[^\012\015*\\/[]|\[(?:[^\\\]\012\015]|\\[^\012\015])*\]|\\[^\012\015])
	       (?:[^\012\015\\/[] |\[(?:[^\\\]\012\015]|\\[^\012\015])*\]|\\[^\012\015])*)
	   /(?>$RE_JS_IDENTIFIER_PART*)
	  !x ;
    $RE_JS_TOKEN= qr/$RE_JS_IDENTIFIER_NAME|$RE_JS_NUMERIC_LITERAL|$RE_JS_PUNCTUATOR|$RE_JS_STRING_LITERAL_START/ ;
    $RE_JS_INPUT_ELEMENT_DIV= qr/(?>$RE_JS_WHITE_SPACE+)|$RE_JS_LINE_TERMINATOR|$RE_JS_COMMENT|
				 ($RE_JS_TOKEN|$RE_JS_DIV_PUNCTUATOR|$RE_JS_REGULAR_EXPRESSION_LITERAL)/x ;
    $RE_JS_INPUT_ELEMENT_REG_EXP= qr/(?>$RE_JS_WHITE_SPACE+)|$RE_JS_LINE_TERMINATOR|$RE_JS_COMMENT|
				     ($RE_JS_TOKEN|$RE_JS_REGULAR_EXPRESSION_LITERAL|$RE_JS_DIV_PUNCTUATOR)/x ;
    $RE_JS_SKIP= qr/(?>$RE_JS_WHITE_SPACE+)|$RE_JS_LINE_TERMINATOR|$RE_JS_COMMENT/ ;
    $RE_JS_SKIP_NO_LT= qr/(?>$RE_JS_WHITE_SPACE+)|$RE_JS_COMMENT/ ;
}
sub return_jslib {
    my($date_header)=    &rfc1123_date($now, 0) ;
    my($expires_header)= &rfc1123_date($now+86400*7, 0) ;  # expires after a week
    unless ($JSLIB_BODY) {
	my($script_name_jsq)= $ENV_SCRIPT_NAME ;
	$script_name_jsq=~ s/(["\\])/\\$1/g ;   # make safe for JS quoted string
	my($script_url_jsq)= $script_url ;
	$script_url_jsq=~ s/(["\\])/\\$1/g ;   # make safe for JS quoted string
	my($THIS_HOST_jsq)= $THIS_HOST ;
	$THIS_HOST_jsq=~ s/(["\\])/\\$1/g ;   # make safe for JS quoted string
	my($proxy_group_jsq, @pg, $all_types_js, $mime_type_id_js) ;
	@pg= @PROXY_GROUP ;
	foreach (@pg) { s/(["\\])/\\$1/g }
	$proxy_group_jsq= join(', ', map { "'$_'" } @pg) ;
	$all_types_js=    join(', ', map { "'$_'" } @ALL_TYPES) ;
	$mime_type_id_js= join(', ', map { "'$_':$MIME_TYPE_ID{$_}" } keys %MIME_TYPE_ID) ;
	$JSLIB_BODY= <<EOV . <<'EOF' ;
var _proxy_jslib_SCRIPT_NAME= "$script_name_jsq" ;
var _proxy_jslib_SCRIPT_URL= "$script_url_jsq" ;
var _proxy_jslib_THIS_HOST= "$THIS_HOST_jsq" ;
var _proxy_jslib_PROXY_GROUP= [$proxy_group_jsq] ;
var _proxy_jslib_ALL_TYPES= [$all_types_js] ;
var _proxy_jslib_MIME_TYPE_ID= {$mime_type_id_js} ;
$ENCODE_DECODE_BLOCK_IN_JS
EOV
var _proxy_jslib_browser_family ;
var _proxy_jslib_RE_FULL_PATH ;
var _proxy_jslib_url_start, _proxy_jslib_url_start_inframe, _proxy_jslib_url_start_noframe,
    _proxy_jslib_is_in_frame, _proxy_jslib_packed_flags, _proxy_jslib_URL ;
var _proxy_jslib_cookies_are_banned_here, _proxy_jslib_doing_insert_here, _proxy_jslib_SESSION_COOKIES_ONLY,
    _proxy_jslib_COOKIE_PATH_FOLLOWS_SPEC, _proxy_jslib_RESPECT_THREE_DOT_RULE,
    _proxy_jslib_ALLOW_UNPROXIFIED_SCRIPTS,
    _proxy_jslib_default_script_type, _proxy_jslib_default_style_type ;
var _proxy_jslib_RE, _proxy_jslib_needs_jslib, _proxy_jslib_does_write ;
var _proxy_jslib_write_buffers= [ {doc:document, has_js:true} ] ;
var _proxy_jslib_ret ;
var _proxy_jslib_current_object_classid ;
var _proxy_jslib_increments= {applets: 0, embeds: 0, forms: 0, ids: 0, layers: 0, anchors: 0, images: 0, links: 0} ;
var _proxy_jslib_handle_properties= 'eval insertAdjacentHTML setAttribute setAttributeNode insertRule innerHTML outerHTML outerText src href background lowsrc action useMap longDesc cite codeBase location poster open write writeln URL referrer baseURI body replace toString setInterval setTimeout cookie domain frames parent top opener protocol host hostname port pathname search setStringValue setProperty setNamedItem load execScript navigate showModalDialog showModelessDialog execCommand LoadMovie getElementById getElementsByTagName close'.split(/\s+/) ;
var _proxy_jslib_assign_properties= 'background src href lowsrc action useMap longDesc cite codeBase location poster profile cssText innerHTML outerHTML outerText nodeValue protocol host hostname port pathname search cookie domain value backgroundImage content cursor listStyle listStyleImage'.split(/\s+/) ;
var _proxy_jslib_handle_props_hash, _proxy_jslib_assign_props_hash ;
var _proxy_jslib_ORIGINAL_ARRAY_push= Array.prototype.push ;
_proxy_jslib_init() ;
function _proxy_jslib_init() {
    _proxy_jslib_browser_family=
	    navigator.appName.match(/Netscape/i)   ? 'netscape'
	  : navigator.appName.match(/Microsoft/i)  ? 'msie'
	  : '' ;
    _proxy_jslib_set_RE() ;
    _proxy_jslib_handle_props_hash= {} ;
    for (var i= 0 ; i<_proxy_jslib_handle_properties.length ; i++)
	_proxy_jslib_handle_props_hash[_proxy_jslib_handle_properties[i]]= true ;
    _proxy_jslib_assign_props_hash= {} ;
    for (var i= 0 ; i<_proxy_jslib_assign_properties.length ; i++)
	_proxy_jslib_assign_props_hash[_proxy_jslib_assign_properties[i]]= true ;
    var RE_SCRIPT_NAME= _proxy_jslib_SCRIPT_NAME
		.replace(/(\W)/g, function (p) { return '\\'+p } ) ;
    _proxy_jslib_RE_FULL_PATH= new RegExp('^('+RE_SCRIPT_NAME+')\\/?([^\\/]*)\\/?(.*)') ;
    var URL= document.URL.replace(/^wyciwyg:\/\/\d+\//i, '') ;
    var u= _proxy_jslib_parse_full_url(URL) ;
    if (_proxy_jslib_PROXY_GROUP.length) {
	_proxy_jslib_url_start= _proxy_jslib_PROXY_GROUP[Math.floor(Math.random()*_proxy_jslib_PROXY_GROUP.length)]
				+'/'+u[1]+'/' ;
    } else {
	_proxy_jslib_url_start= u[0]+'/'+u[1]+'/' ;
    }
    var flags= _proxy_jslib_unpack_flags(u[1]) ;
    _proxy_jslib_is_in_frame= flags[5] ;
    flags[5]= 1 ;    // that's the frame flag
    _proxy_jslib_url_start_inframe= u[0]+'/'+_proxy_jslib_pack_flags(flags)+'/' ;
    flags[5]= 0 ;
    _proxy_jslib_url_start_noframe= u[0]+'/'+_proxy_jslib_pack_flags(flags)+'/' ;
    _proxy_jslib_packed_flags= u[1] ;
    _proxy_jslib_URL=          u[2] ;
    window._proxy_jslib_document_domain= _proxy_jslib_parse_url(_proxy_jslib_URL)[4] ;
    var old_onload= window.onload ;
    window.onload= function() {
		       try { if (old_onload) old_onload() } catch(e) {} ;
		       _proxy_jslib_onload() ;
		   }
}
function _proxy_jslib_pass_vars(base_url, cookies_are_banned_here, doing_insert_here, SESSION_COOKIES_ONLY, COOKIE_PATH_FOLLOWS_SPEC, RESPECT_THREE_DOT_RULE, ALLOW_UNPROXIFIED_SCRIPTS, default_script_type, default_style_type) {
    _proxy_jslib_set_base_vars(window.document, base_url) ;
    _proxy_jslib_cookies_are_banned_here=   cookies_are_banned_here ;
    _proxy_jslib_doing_insert_here=         doing_insert_here ;
    _proxy_jslib_SESSION_COOKIES_ONLY=      SESSION_COOKIES_ONLY ;
    _proxy_jslib_COOKIE_PATH_FOLLOWS_SPEC=  COOKIE_PATH_FOLLOWS_SPEC ;
    _proxy_jslib_RESPECT_THREE_DOT_RULE=    RESPECT_THREE_DOT_RULE ;
    _proxy_jslib_ALLOW_UNPROXIFIED_SCRIPTS= ALLOW_UNPROXIFIED_SCRIPTS ;
    _proxy_jslib_default_script_type=      default_script_type.toLowerCase() ;
    _proxy_jslib_default_style_type=       default_style_type.toLowerCase() ;
}
function _proxy_jslib_onload() {
    if (_proxy_jslib_is_in_frame && (window.parent===window.top) && top._proxy_jslib_insertion_frame)
	top._proxy_jslib_insertion_frame.document.URLform.URL.value= _proxy_jslib_URL ;
}
function _proxy_jslib_handle (o, property, cur_val, calls_now, in_new_statement) {
    if (typeof(property)=='number') return _handle_default() ;
    if ((o===null)  && (typeof(property)=='string') && property.match(/^(location|open|setInterval|setTimeout|frames|parent|top|opener|execScript|navigate|showModalDialog|showModelessDialog|parentWindow)$/) && (window[property]===cur_val)) o= window ;
    if (property=='eval') {
	if ((o!=null) && (o.eval)) {
	    var oldeval= o.eval ;
	    return function (code) {
		       var ret ;
		       o._proxy_jslib_oldeval= oldeval ;
		       ret= o._proxy_jslib_oldeval(_proxy_jslib_proxify_js(code, 0)) ;
		       delete o._proxy_jslib_oldeval ;
		       return ret ;
		   } ;
	} else {
	    if (o!=null) return undefined ;
	    var oldeval= eval ;
	    return function (code) {
		       return oldeval(_proxy_jslib_proxify_js(code, 0)) ;
		   } ;
	}
    }
    if (o==null) return cur_val ;
    try {
	if ((_proxy_jslib_browser_family!='msie') && (o instanceof StorageList)) {
	    return o[property+'.cgiproxy.'+_proxy_jslib_THIS_HOST] ;
	}
    } catch(e) {} ;
    if (!_proxy_jslib_handle_props_hash[property]) return _handle_default() ;
    var otype= _proxy_jslib_object_type(o) ;
    if (!otype) return _handle_default() ;
    if (   ('getAttributeNode' in o) && ('getElementsByTagName' in o)
	&& ('removeAttribute' in o) && !('innerHTML' in o) )
    {
	return _handle_default() ;
    }
    switch (property) {
	case 'insertAdjacentHTML':
	    return function (where, text) {
		       if (this!==window) o= this ;
		       return o.insertAdjacentHTML(where, _proxy_jslib_proxify_html(text, o.ownerDocument, false)[0]) ;
		   } ;
	case 'setAttribute':
	    if (!(('getAttributeNode' in o) && ('getElementsByTagName' in o) && ('removeAttribute' in o)))
		break ;
	    return function (name, value) {
		       if (this!==window) o= this ;
		       return o.setAttribute(name.toLowerCase(), _proxy_jslib_proxify_attribute(name, value)) ;
		   }
	case 'setAttributeNode':
	    return function (newAttr) {
		       if (this!==window) o= this ;
		       newAttr.nodeValue= _proxy_jslib_proxify_attribute(newAttr.nodeName, newAttr.nodeValue) ;
		       return o.setAttributeNode(newAttr) ;
		   }
	case 'insertRule':
	    return function (rule, index) {
		       if (this!==window) o= this ;
		       return o.insertRule(_proxy_jslib_proxify_css(rule), index) ;
		   }
	case 'innerHTML':
	case 'outerHTML':
	case 'outerText':
	    if (!(   ('getAttributeNode' in o) && ('getElementsByTagName' in o)
		  && ('removeAttribute' in o) ))
		return _handle_default() ;
	    return _proxy_jslib_proxify_html(o[property], (o.ownerDocument || o), false, true)[0] ;  // unproxifies
	case 'src':
	case 'href':
	case 'background':
	case 'lowsrc':
	case 'action':
	case 'useMap':
	case 'longDesc':
	case 'cite':
	case 'codeBase':
	case 'location':
	case 'baseURI':
	case 'poster':
	    var u= (o!=void 0) ? o[property] : cur_val ;
	    if (u==void 0) return void 0 ;
	    if (typeof u=='number') return u ;
	    if (u && (typeof u=='object') && !('toLowerCase' in u))
		return u ;
	    var pu= _proxy_jslib_parse_full_url(u) ;
	    if (pu==void 0) return u ;   // if it's not a URL
	    return pu[2] ;
    }
    switch (otype+'.'+property) {
	case 'Window.open':
	    return function (url, name, features, replace) {
		       if (this!==window) o= this ;
		       var full_url= _proxy_jslib_full_url(url) ;
		       var win= o.open(full_url, name, features, replace) ;
		       if (url) win._proxy_jslib_document_domain=
			   _proxy_jslib_parse_url(_proxy_jslib_parse_full_url(full_url)[2])[4] ;
		       return win ;
		   } ;
	case 'Document.open':
	    return function(arg1, name, features, replace) {
		       if (arg1==void 0) arg1= 'text/html' ;
		       if (this!==window) o= this ;
		       if (arguments.length<=2) {
			   return o.open(arg1, name) ;
		       } else {
			   return o.open(_proxy_jslib_full_url(arg1, o), name, features, replace) ;
		       }
		   }
	case 'Document.write':
	    return function () {
		       if (this!==window) o= this ;
		       for (var i= 0 ; i<arguments.length ; i++)
			   _proxy_jslib_write_via_buffer(o, arguments[i]) ;
		   } ;
	case 'Document.writeln':
	    return function () {
		       if (this!==window) o= this ;
		       for (var i= 0 ; i<arguments.length ; i++)
			   _proxy_jslib_write_via_buffer(o, arguments[i]) ;
		       _proxy_jslib_write_via_buffer(o, '\n') ;
		   } ;
	case 'Document.close':
	    return function() {
		       if (this!==window) o= this ;
		       var buf, i, p ;
		       for (i in _proxy_jslib_write_buffers) {
			   if (_proxy_jslib_write_buffers[i].doc===o) {
			       buf= _proxy_jslib_write_buffers[i] ;
			       if (buf.buf==void 0) break ;
			       p= _proxy_jslib_proxify_html(buf.buf, o, !buf.has_js) ;
			       if (p[3]) return ;   // found frame document
			       buf.has_js= false ;
			       buf.buf= void 0 ;
			       o.write(p[0]) ;
			       break ;
			   }
		       }
		       o.close() ;
		   }
	case 'Document.getElementById':
	    return function (elementId) {
		       if (this!==window) o= this ;
		       var e, i, buf, p ;
		       e= o.getElementById(elementId) ;
		       if (e!=null) return e ;
		       for (i= 0 ; i<_proxy_jslib_write_buffers.length ; i++)
			   if (_proxy_jslib_write_buffers[i]  &&
			       _proxy_jslib_write_buffers[i].doc===o) break ;
		       if (i>=_proxy_jslib_write_buffers.length) return null ;
		       buf= _proxy_jslib_write_buffers[i] ;
		       if (buf.buf==void 0) return null ;
		       if (buf.buf.match(new RegExp('id\\s*=\\s*[\'"]?\\s*'+elementId+'\\s*[\'"]?', 'i'))) {
			   p= _proxy_jslib_proxify_html(buf.buf, o, false) ;
			   if (p[3]) return ;   // found frame document
			   buf.has_js= buf.has_js || p[2] ;
			   buf.buf= p[1] ;
			   o.write(p[0]) ;
		       }
		       return o.getElementById(elementId) ;
		   }
	case 'Node.getElementsByTagName':     // actually Element
	case 'Document.getElementsByTagName':
	    return function (tagname) {
if (document.URL.match(/\/mail\.google\.com\/mail\//) && tagname=='INPUT') alert('Hit OK to continue...') ;
		       if (this!==window) o= this ;
		       var i, buf, pi, doc ;
		       doc= (o.ownerDocument || o) ;
		       for (i= 0 ; i<_proxy_jslib_write_buffers.length ; i++)
			   if (_proxy_jslib_write_buffers[i]  &&
			       _proxy_jslib_write_buffers[i].doc===doc) break ;
		       if (i>=_proxy_jslib_write_buffers.length) return o.getElementsByTagName(tagname) ;
		       buf= _proxy_jslib_write_buffers[i] ;
		       if (buf.buf==void 0) return o.getElementsByTagName(tagname) ;
		       if (tagname=='*' || buf.buf.match(new RegExp('<\\s*'+tagname+'\\b', 'i'))) {
			   p= _proxy_jslib_proxify_html(buf.buf, doc, false) ;
			   if (p[3]) return ;   // found frame document
			   buf.has_js= buf.has_js || p[2] ;
			   buf.buf= p[1] ;
			   doc.write(p[0]) ;
		       }
		       return o.getElementsByTagName(tagname) ;
		   }
	case 'Document.URL':
	case 'Document.referrer':
	    var pu= _proxy_jslib_parse_full_url(o[property]) ;
	    return (pu==void 0)  ? void 0  : pu[2] ;
	case 'Document.body':
	    var ret= o.getElementById('_proxy_css_main_div') ;
	    return ret  ? ret  : o.body ;
	case 'Location.replace':
	    return function (url) {
		       if (this!==window) o= this ;
		       var u= _proxy_jslib_parse_full_url(o.toString()) ;
		       if (u!=void 0 && u[1]!='') {
			   return o.replace(_proxy_jslib_full_url_by_frame(url, null, _proxy_jslib_unpack_flags(u[1])[5])) ;
		       } else {
			   return o.replace(_proxy_jslib_full_url(url)) ;
		       }
		   } ;
	case 'Link.toString':
	case 'Location.toString':
	    return function () {
		       if (this!==window) o= this ;
		       return _proxy_jslib_parse_full_url(o.toString())[2] ;
		   }
	case 'Window.setInterval':
	    if (_proxy_jslib_browser_family=='msie') {
		var oldsetInterval= o.setInterval ;
		return function (codefunc, interval) {
			   var ret ;
			   if (this!==window) o= this ;
			   o._proxy_jslib_oldsetInterval= oldsetInterval ;
			   if (typeof(codefunc)=='function') {
			       ret= o._proxy_jslib_oldsetInterval(codefunc, interval) ;
			   } else {
			       ret= o._proxy_jslib_oldsetInterval(_proxy_jslib_proxify_js(codefunc), interval) ;
			   }
			   try {
			       delete o._proxy_jslib_oldsetInterval ;
			   } catch(e) {
			   }
			   return ret ;
		       } ;
	    } else {
		var oldsetInterval= o.setInterval ;
		return function (codefunc, interval) {
			   if (this!==window) o= this ;
			   if (typeof(codefunc)=='function') {
			       return oldsetInterval.apply(o, arguments) ;
			   } else {
			       return oldsetInterval.call(o, _proxy_jslib_proxify_js(codefunc), interval) ;
			   }
		       } ;
	    }
	case 'Window.setTimeout':
	    if (_proxy_jslib_browser_family=='msie') {
		var oldsetTimeout= o.setTimeout ;
		return function (codefunc, delay) {
			   var ret ;
			   if (this!==window) o= this ;
			   o._proxy_jslib_oldsetTimeout= oldsetTimeout ;
			   if (typeof(codefunc)=='function') {
			       ret= o._proxy_jslib_oldsetTimeout(codefunc, delay) ;
			   } else {
			       ret= o._proxy_jslib_oldsetTimeout(_proxy_jslib_proxify_js(codefunc), delay) ;
			   }
			   try {
			       delete o._proxy_jslib_oldsetTimeout ;
			   } catch(e) {
			   }
			   return ret ;
		       } ;
	    } else {
		var oldsetTimeout= o.setTimeout ;
		return function (codefunc, delay) {
			   if (this!==window) o= this ;
			   if (typeof(codefunc)=='function') {
			       return oldsetTimeout.apply(o, arguments) ;
			   } else {
			       return oldsetTimeout.call(o, _proxy_jslib_proxify_js(codefunc), delay) ;
			   }
		       } ;
	    }
	case 'Document.cookie':
	    return _proxy_jslib_cookie_from_client(o) ;
	case 'Document.domain':
	    return _proxy_jslib_doc2win(o)._proxy_jslib_document_domain ;
	case 'Window.frames':
	    var f, ret= [], useret ;
	    if (_proxy_jslib_document_domain==void 0) _proxy_jslib_init_domain(window) ;
	    for (f=0 ; f<o.frames.length ; f++) {
		try {
		    if (o.frames[f]._proxy_jslib_document_domain==void 0) _proxy_jslib_init_domain(o.frames[f]) ;
		    if ((o.frames[f]._proxy_jslib_document_domain!=_proxy_jslib_document_domain)
			&& (o.frames[f]._proxy_jslib_document_domain!=void 0))
		    {
			ret[f]= _proxy_jslib_dup_window_safe(o.frames[f]) ;
			if (o.frames[f].name) ret[o.frames[f].name]= ret[f] ;
			useret= true ;
		    } else {
			ret[f]= o.frames[f] ;
			if (o.frames[f].name) ret[o.frames[f].name]= ret[f] ;
		    }
		} catch (e) {
		}
	    }
	    return useret  ? ret  : o.frames ;
	case 'Window.parent':
	    var w= (o.top._proxy_jslib_main_frame===o)  ? o  : o.parent ;
	    if (_proxy_jslib_document_domain==void 0) _proxy_jslib_init_domain(window) ;
	    if (w._proxy_jslib_document_domain==void 0) _proxy_jslib_init_domain(w) ;
	    return ((w._proxy_jslib_document_domain==_proxy_jslib_document_domain)
		    || (w._proxy_jslib_document_domain==void 0))
		? w  : _proxy_jslib_dup_window_safe(w) ;
	case 'Window.top':
	    var w= (o.top._proxy_jslib_main_frame!==void 0)  ? o.top._proxy_jslib_main_frame  : o.top ;
	    if (_proxy_jslib_document_domain==void 0) _proxy_jslib_init_domain(window) ;
	    if (w._proxy_jslib_document_domain==void 0) _proxy_jslib_init_domain(w) ;
	    return ((w._proxy_jslib_document_domain==_proxy_jslib_document_domain)
		    || (w._proxy_jslib_document_domain==void 0))
		? w  : _proxy_jslib_dup_window_safe(w) ;
	case 'Window.opener':
	    if (!o.opener) return null ;
	    if (_proxy_jslib_document_domain==void 0) _proxy_jslib_init_domain(window) ;
	    if (o.opener._proxy_jslib_document_domain==void 0) _proxy_jslib_init_domain(o.opener) ;
	    return ((o.opener._proxy_jslib_document_domain==_proxy_jslib_document_domain)
		    || (o.opener._proxy_jslib_document_domain==void 0))
		? o.opener  : _proxy_jslib_dup_window_safe(o.opener) ;
	case 'Link.protocol':
	case 'Location.protocol':
	    return _proxy_jslib_parse_url(_proxy_jslib_parse_full_url(o.href)[2])[1] ;
	case 'Link.host':
	case 'Location.host':
	    return _proxy_jslib_parse_url(_proxy_jslib_parse_full_url(o.href)[2])[3] ;
	case 'Link.hostname':
	case 'Location.hostname':
	    return _proxy_jslib_parse_url(_proxy_jslib_parse_full_url(o.href)[2])[4] ;
	case 'Link.port':
	case 'Location.port':
	    return _proxy_jslib_parse_url(_proxy_jslib_parse_full_url(o.href)[2])[5] ;
	case 'Link.pathname':
	case 'Location.pathname':
	    return _proxy_jslib_parse_url(_proxy_jslib_parse_full_url(o.href)[2])[6] ;
	case 'Link.search':
	case 'Location.search':
	    return _proxy_jslib_parse_url(_proxy_jslib_parse_full_url(o.href)[2])[7] ;
	case 'FlashPlayer.LoadMovie':
	    return function (layer, url) {
		       if (this!==window) o= this ;
		       return o.LoadMovie(layer, _proxy_jslib_full_url(url)) ;
		   }
	case 'CSSPrimitiveValue.setStringValue':
	    return function (type, value) {
		       if (this!==window) o= this ;
		       if (type==CSSPrimitiveValue.CSS_URI)
			   return o.setStringValue(type, _proxy_jslib_full_url(value)) ;
		       return o.setStringValue(type, value) ;
		   }
	case 'CSSStyleDeclaration.setProperty':
	    return function (name, value, priority) {
		       if (this!==window) o= this ;
		       return o.setProperty(name, _proxy_jslib_proxify_css(value), priority) ;
		   }
	case 'NamedNodeMap.setNamedItem':
	    return function (node) {
		       if (this!==window) o= this ;
		       node.nodeValue= _proxy_jslib_proxify_attribute(node.nodeName, node.nodeValue) ;
		       return o.setNamedItem(node) ;
		   }
	case 'Layer.load':
	    if (!o.load) return undefined ;
	    return function (url, width) {
		       if (this!==window) o= this ;
		       return o.load(_proxy_jslib_full_url(url), width) ;
		   } ;
	case 'Window.execScript':
	    if (!o.execScript) return undefined ;
	    return function(code, language) {
		       if (this!==window) o= this ;
		       if (language.match(/^\s*(javascript|jscript|ecmascript|livescript|$)/i))
			   return o.execScript(_proxy_jslib_proxify_js(code), language) ;
		       if (_proxy_jslib_ALLOW_UNPROXIFIED_SCRIPTS)
			   return o.execScript(code, language) ;
		       return ;
		   }
	case 'Window.navigate':
	    if (!o.navigate) return undefined ;
	    return function (url) {
		       if (this!==window) o= this ;
		       return o.navigate(_proxy_jslib_full_url(url, o.document)) ;
		   } ;
	case 'Window.showModalDialog':
	    if (!o.showModalDialog) return undefined ;
	    return function(url, args, features) {
		       if (this!==window) o= this ;
		       return o.showModalDialog(_proxy_jslib_full_url(url, o.document), args, features) ;
		   } ;
	case 'Window.showModelessDialog':
	    if (!o.showModelessDialog) return undefined ;
	    return function(url, args, features) {
		       if (this!==window) o= this ;
		       return o.showModelessDialog(_proxy_jslib_full_url(url, o.document), args, features) ;
		   } ;
	case 'XMLHttpRequest.open':
	    return function(method, url, asyncflag, username, password) {
		       if (this!==window) o= this ;
		       if (url.match(/^[\w\+\.\-]*\:/)) {
			   var h1= (_proxy_jslib_parse_url(_proxy_jslib_URL))[4] ;
			   var h2= (_proxy_jslib_parse_url(url))[4] ;
			   var d1= (h1.match(/(^|\.)(\w+\.\w+)$/))[2].toLowerCase() ;
			   var d2= (h2.match(/(^|\.)(\w+\.\w+)$/))[2].toLowerCase() ;
			   if (d1!=d2) return ;   // unallowed domain
		       }
		       var flags= _proxy_jslib_unpack_flags(_proxy_jslib_packed_flags) ;
		       flags[5]= 1 ;  // because of how this is used, don't insert the top form
		       flags[6]= 'text/xml' ;
		       var old_url_start= _proxy_jslib_url_start ;
		       _proxy_jslib_url_start= _proxy_jslib_SCRIPT_URL + '/' + _proxy_jslib_pack_flags(flags) + '/' ;
		       url= _proxy_jslib_full_url(url) ;
		       _proxy_jslib_url_start= old_url_start ;
		       return o.open(method, url, asyncflag, username, password) ;
		   }
	case 'Document.execCommand':
	    return function (cmd, do_UI, value) {
		       var ret ;
		       cmd= cmd.toLowerCase() ;
		       if (_proxy_jslib_browser_family=='netscape') {
			   if ((cmd=='createlink') || (cmd=='insertimage')) {
			       ret= o.execCommand(cmd, do_UI, _proxy_jslib_full_url(value, o)) ;
			   } else if (cmd=='inserthtml') {
			       ret= o.execCommand(cmd, do_UI, _proxy_jslib_proxify_html(value, o)[0]) ;
			   } else {
			       ret= o.execCommand(cmd, do_UI, value) ;
			   }
		       } else if (_proxy_jslib_browser_family=='msie') {
			   if ((cmd=='createlink') || (cmd=='insertimage')) {
			       ret= o.execCommand(cmd, do_UI, _proxy_jslib_full_url(value, o)) ;
			   } else if (cmd.match(/^insert/)) {
			       alert('tried to execCommand('+cmd+')') ;
			       ret= undefined ;
			   } else {
			       ret= o.execCommand(cmd, do_UI, value) ;
			   }
		       }
		       return ret ;
		   }
	default:
	    return _handle_default() ;
    }
    function _handle_default() {
	if (calls_now && !in_new_statement && (typeof(o[property])=='function')) {
	    if (o==Function && property=='prototype') return o[property] ;
	    var fn= o[property] ;
	    var ret= function () {
			 if (fn.apply==void 0) {
			     return o[property](arguments[0], arguments[1],
						arguments[2], arguments[3],
						arguments[4], arguments[5]) ;
			 }
			 if (this!==window) {
			     return fn.apply(this, arguments) ;
			 } else {
			     return fn.apply(o, arguments) ;
			 }
		     } ;
	    for (var p in o[property]) ret[p]= o[property][p] ;
	    return ret ;
	} else {
	    try {
		if (_proxy_jslib_browser_family=='msie' && property=='getElementsByTagName')
		    return function(tagname) {
			       if (this!==window) o= this ;
			       return o.getElementsByTagName(tagname) ;
			   } ;
		return o[property] ;
	    } catch(e) {
		return undefined ;
	    }
	}
    }
}
function _proxy_jslib_assign (prefix, o, property, op, val) {
    var new_val, otype ;
    if (prefix=='delete') return delete o[property] ;
    if (prefix=='++') {
	val= o[property]+1 ;
	op= '=' ;
    } else if (prefix=='--') {
	val= o[property]-1 ;
	op= '=' ;
    }
if (o==null) alert('in assign, o is null, property, caller=\n['+property+']\n['+arguments.callee.caller+']') ;   // jsm-- remove in production release?
    if (!_proxy_jslib_assign_props_hash[property]) return _assign_default() ;
    otype= _proxy_jslib_object_type(o) ;
    var opmod= op.match(/=/)  ? op.replace(/=$/, '')  : '' ;
    switch (property) {
	case 'background':
	    if (otype=="CSS2Properties") {
		o[property]= _proxy_jslib_proxify_css(val) ;
		return val ;
	    }   // else drop through to next block
	case 'src':
	case 'href':
	case 'lowsrc':
	case 'action':
	case 'useMap':
	case 'longDesc':
	case 'cite':
	case 'codeBase':
	case 'location':
	case 'poster':
	    if (!(val instanceof String) && !(typeof val=='string'))
		return eval('o[property]'+op+'val') ;
	    if (opmod!='') {
		new_val= _proxy_jslib_parse_full_url(o[property])[2] ;
		eval('new_val' + op + 'val') ;
	    } else {
		new_val= val ;
	    }
	    if ((property=='location') && (o.top===o)) {
		o[property]= _proxy_jslib_full_url_by_frame(new_val, null, false) ;
	    } else {
		o[property]= _proxy_jslib_full_url(new_val, o.ownerDocument) ;
	    }
	    if (otype=='Window') _proxy_jslib_init_domain(o) ;
	    return new_val ;
	case 'profile':
	    if (!o.tagName || o.tagName.toLowerCase()!='head')
		return o[property]= val ;
	    var u= val.split(/\s+/) ;
	    for (var i= 0 ; i<u.length ; i++)
		u[i]= _proxy_jslib_full_url(u[i], o.ownerDocument) ;
	    o[property]= u.join(' ') ;
	    return val ;
	case 'cssText':
	    o[property]= _proxy_jslib_proxify_css(val) ;
	    return val ;
	case 'innerHTML':
	case 'outerHTML':
	case 'outerText':
	    if (!(   ('getAttributeNode' in o) && ('getElementsByTagName' in o)
		  && ('removeAttribute' in o) ))
		return _assign_default() ;
	    if (op!='=') new_val= _proxy_jslib_proxify_html(o[property], (o.ownerDocument || o), false, true)[0] ;
	    eval('new_val' + op + 'val') ;
	    o[property]= _proxy_jslib_proxify_html(new_val, (o.ownerDocument || o), false)[0] ;
	    return new_val ;
	case 'nodeValue':
	    if (opmod!='') { eval('new_val= o[property]' + opmod + 'val') }
	    else           { new_val= val }
	    o[property]= _proxy_jslib_proxify_attribute(property, new_val) ;
	    return new_val ;
	default:
	    var fu, u ;
	    if (otype=='Link' || otype=='Location') {
		fu= _proxy_jslib_parse_full_url(o.href) ;
		u=  _proxy_jslib_parse_url(fu[2]) ;
	    }
	    switch (otype+'.'+property) {
		case 'Link.protocol':
		case 'Location.protocol':
		    val.toLowerCase() ;
		    o.href= _proxy_jslib_full_url(val+'//'+(u[2]!='' ? u[2]+'@' : '', o.ownerDocument)+u[3]+u[6]+u[7]+u[8]) ;
		    return val ;
		case 'Link.host':
		case 'Location.host':
		    val.toLowerCase() ;
		    o.href= _proxy_jslib_full_url(u[1]+'//'+(u[2]!='' ? u[2]+'@' : '', o.ownerDocument)+val+u[6]+u[7]+u[8]) ;
		    return val ;
		case 'Link.hostname':
		case 'Location.hostname':
		    o.href= _proxy_jslib_full_url(u[1]+'//'+(u[2]!='' ? u[2]+'@' : '', o.ownerDocument)+val+(u[5]!='' ? ':'+u[5] : '')+u[6]+u[7]+u[8]) ;
		    return val ;
		case 'Link.port':
		case 'Location.port':
		    o.href= _proxy_jslib_full_url(u[1]+'//'+(u[2]!='' ? u[2]+'@' : '', o.ownerDocument)+u[4]+(val!='' ? ':'+val : '')+u[6]+u[7]+u[8]) ;
		    return val ;
		case 'Link.pathname':
		case 'Location.pathname':
		    o.href= _proxy_jslib_full_url(u[1]+'//'+(u[2]!='' ? u[2]+'@' : '', o.ownerDocument)+u[3]+val+u[7]+u[8]) ;
		    return val ;
		case 'Link.search':
		case 'Location.search':
		    o.href= _proxy_jslib_full_url(u[1]+'//'+(u[2]!='' ? u[2]+'@' : '', o.ownerDocument)+u[3]+u[6]+val+u[8]) ;
		    return val ;
		case 'Document.cookie':
		    return (_proxy_jslib_cookies_are_banned_here
			    ? ''
			    : o.cookie= _proxy_jslib_cookie_to_client(val) ) ;
		case 'Document.domain':
		    var w= _proxy_jslib_doc2win(o) ;
		    if ( ( (('.'+val)==w._proxy_jslib_document_domain.slice(-val.length-1))
			   || (val==w._proxy_jslib_document_domain) )
			 && val.match(/\./) )
			return (w._proxy_jslib_document_domain= val) ;
		    break ;
		case 'Attr.value':
		    o.value= _proxy_jslib_proxify_attribute(o.name, val) ;
		    return val ;
		case 'CSS2Properties.backgroundImage':
		case 'CSS2Properties.content':
		case 'CSS2Properties.cursor':
		case 'CSS2Properties.listStyle':
		case 'CSS2Properties.listStyleImage':
		    o[property]= _proxy_jslib_proxify_css(val) ;
		    return val ;
		default:
		    return _assign_default() ;
	    }
    }
    function _assign_default() {
	if (op=='++') return o[property]++ ;
	else if (op=='--') return o[property]-- ;
	else return eval('o[property]'+op+'val') ;
    }
}
function _proxy_jslib_assign_rval (prefix, property, op, val, cur_val) {
    if (prefix=='delete') return undefined ;  // not quite the same as delete, but close enough
    if (prefix=='++') {
	val= 1 ;
	op= '+=' ;
    } else if (prefix=='--') {
	val=  1 ;
	op= '-=' ;
    }
    if (val && (typeof val=='object') && (!('toLowerCase' in val)))
	return val ;
    var new_val= cur_val ;
    eval('new_val' + op + 'val') ;
    switch (property) {
	case 'location':
	    return _proxy_jslib_full_url(new_val) ;
	default:
	    return new_val ;
    }
}
function _proxy_jslib_with_handle (with_objs, property, cur_val, calls_now, in_new_statement) {
    for (var i= with_objs.length-1 ; i>=0 ; i--)
    if (property in with_objs[i])
	    return _proxy_jslib_handle(with_objs[i], property, with_objs[i][property], calls_now, in_new_statement) ;
    return _proxy_jslib_handle(null, property, cur_val, calls_now, in_new_statement) ;
}
function _proxy_jslib_with_assign_rval (with_objs, prefix, property, op, val, cur_val) {
    for (var i= with_objs.length-1 ; i>=0 ; i--)
    if (property in with_objs[i])
	return _proxy_jslib_assign(prefix, with_objs[i], property, op, val) ;
    return _proxy_jslib_assign_rval(prefix, property, op, val, cur_val) ;
}
function _proxy_jslib_write_via_buffer(doc, html) {
    var i, buf ;
    for (i= 0 ; i<_proxy_jslib_write_buffers.length ; i++) {
	if (_proxy_jslib_write_buffers[i].doc===doc) {
	    buf= _proxy_jslib_write_buffers[i] ;
	    break ;
	}
    }
    if (!buf) {
	buf= _proxy_jslib_write_buffers[_proxy_jslib_write_buffers.length]=
	    { doc: doc, buf: html } ;
    } else {
	if (buf.buf==void 0) buf.buf= '' ;
	buf.buf+= html ;
    }
}
function _proxy_jslib_flush_write_buffers() {
    var buf, i, p ;
    for (i= 0 ; (_proxy_jslib_write_buffers!=void 0) && (i<_proxy_jslib_write_buffers.length) ; i++) {
	buf= _proxy_jslib_write_buffers[i] ;
	if (buf.buf==void 0) continue ;
	p= _proxy_jslib_proxify_html(buf.buf, buf.doc, !buf.has_js) ;
	if (p[3]) return ;   // found frame document
	buf.has_js= buf.has_js || p[2] ;
	buf.buf= p[1] ;
	buf.doc.write(p[0]) ;
    }
}
function _proxy_jslib_flush_write_buffer(buf) {
    var p= _proxy_jslib_proxify_html(buf.buf, buf.doc, !buf.has_js) ;
    if (p[3]) return ;   // found frame document
    buf.has_js= buf.has_js || p[2] ;
    buf.buf= p[1] ;
    buf.doc.write(p[0]) ;
}
function _proxy_jslib_new_function() {
    arguments[arguments.length-1]= _proxy_jslib_proxify_js(arguments[arguments.length-1]) ;
    return Function.apply(null, arguments) ;  // Function() same w/ or w/o "new"
}
function _proxy_jslib_doc2win(d) {
    return (_proxy_jslib_browser_family!='msie')
	? d.defaultView
	: d.parentWindow ;
}
function _proxy_jslib_dup_window_safe(w) {
    return { navigator:     w.navigator,
	     clearInterval: w.clearInterval,
	     moveBy:        w.moveBy,
	     self:          w,
	     location:      w.location } ;
}
function _proxy_jslib_init_domain(w) {
    if (w.document.URL=='about:blank') {
	w._proxy_jslib_document_domain= void 0 ;
	return ;
    }
    var URL= w.document.URL.replace(/^wyciwyg:\/\/\d+\//i, '') ;
    URL= _proxy_jslib_parse_full_url(URL)[2] ;
    URL= decodeURIComponent(URL) ;
    if (URL=='about:blank') {
	w._proxy_jslib_document_domain= void 0 ;
	return ;
    }
    w._proxy_jslib_document_domain= _proxy_jslib_parse_url(URL)[4] ;
}
function _proxy_jslib_full_url(uri_ref, doc, reverse, retain_query) {
    var script, r_l, m1, r_q, query ;
    retain_query= false ;
    if (uri_ref=='//:') return uri_ref ;
    if (!doc) doc= window.document ;
    if (uri_ref==null) return '' ;
    if (reverse) return _proxy_jslib_parse_full_url(uri_ref)[2] ;
    if (!doc._proxy_jslib_base_url) _proxy_jslib_set_base_vars(doc, _proxy_jslib_parse_full_url(doc.URL)[2]) ;
    uri_ref= uri_ref.replace(/^\s+|\s+$/g, '') ;
    if (/^x\-proxy\:\/\//i.test(uri_ref))  return '' ;
    if (uri_ref.match(/^about\:\s*blank$/i))  return uri_ref ;
    if (/^(javascript|livescript)\:/i.test(uri_ref)) {
	script= uri_ref.replace(/^(javascript|livescript)\:/i, '') ;
	r_l= _proxy_jslib_separate_last_js_statement(script) ;
	r_l[1]= r_l[1].replace(/\s*;\s*$/, '') ;
	return 'javascript:' + _proxy_jslib_proxify_js(r_l[0], 1)
			     + '; _proxy_jslib_proxify_html(' + _proxy_jslib_proxify_js(r_l[1], 0) + ')[0]' ;
    } else if (m1= uri_ref.match(/^(fscommand:)(.*)/i)) {
	return m1[1] + _proxy_jslib_proxify_js(m1[2]) ;
    }
    var uf= uri_ref.match(/^([^\#]*)(\#.*)?/) ;
    var uri= uf[1] ;
    var frag=  uf[2]  ? uf[2]  : '' ;
    if (uri=='')  return uri_ref ;
    uri= uri.replace(/[\r\n]/g, '') ;
    if (retain_query) {
	r_q= uri.split(/\?/) ;
	uri= r_q[0] ;
	query= r_q[1] ;
	if (query) query= '?'+query ;
	else query= '' ;
    }
    var absurl ;
    if      (/^[\w\+\.\-]*\:/.test(uri))  { absurl= uri               }
    else if (/^\/\//.test(uri))           { absurl= doc._proxy_jslib_base_scheme + uri }
    else if (/^\//.test(uri))             { absurl= doc._proxy_jslib_base_host   + uri }
    else if (/^\?/.test(uri))             { absurl= doc._proxy_jslib_base_file   + uri }
    else                                  { absurl= doc._proxy_jslib_base_path   + uri }
    return _proxy_jslib_url_start + _proxy_jslib_wrap_proxy_encode(absurl) + (retain_query ? query : '') + frag ;
}
function _proxy_jslib_full_url_by_frame(uri_ref, doc, is_frame, reverse) {
    var old_url_start= _proxy_jslib_url_start ;
    _proxy_jslib_url_start= is_frame  ? _proxy_jslib_url_start_inframe  : _proxy_jslib_url_start_noframe ;
    var ret= _proxy_jslib_full_url(uri_ref, doc, reverse) ;
    _proxy_jslib_url_start= old_url_start ;
    return ret ;
}
function _proxy_jslib_set_base_vars(doc, base_url) {
    if (!base_url) base_url= doc.URL ;
    doc._proxy_jslib_base_url= base_url.replace(/^\s+|\s+$/g, '')
				       .replace(/^([\w\+\.\-]+\:\/\/[^\/\?]+)\/?/, "$1/") ;
    if (!base_url.match(/^\s*https?\:\/\//i)) return ; // handles "about:blank", etc.
    doc._proxy_jslib_base_scheme= doc._proxy_jslib_base_url.match(/^([\w\+\.\-]+\:)\/\//)[1] ;
    doc._proxy_jslib_base_host=   doc._proxy_jslib_base_url.match(/^([\w\+\.\-]+\:\/\/[^\/\?]+)/)[1] ;
    doc._proxy_jslib_base_path=   doc._proxy_jslib_base_url.match(/^([^\?]*\/)/)[1] ;
    doc._proxy_jslib_base_file=   doc._proxy_jslib_base_url.match(/^([^\?]*)/)[1] ;
}
function _proxy_jslib_wrap_proxy_encode(URL) {
    var uf= URL.match(/^([^\#]*)(\#.*)?/) ;
    var uri= uf[1] ;
    var frag=  uf[2]  ? uf[2]  : '' ;
    uri= _proxy_jslib_proxy_encode(uri) ;
    uri= uri.replace(/\=/g, '=3d').replace(/\?/g, '=3f').replace(/\#/g, '=23')
	    .replace(/\%/g, '=25').replace(/\&/g, '=26').replace(/\;/g, '=3b') ;
    while (uri.match(/\/\//)) uri= uri.replace(/\/\//g, '/=2f') ;
    return uri + frag ;
}
function _proxy_jslib_wrap_proxy_decode(enc_URL) {
    var uf= enc_URL.match(/^([^\?\#]*)([^\#]*)(.*)/) ;
    var uri= uf[1] ;
    var query= uf[2] ;
    var frag=  uf[3]  ? uf[3]  : '' ;
    uri= uri.replace(/\=2f/g, '/').replace(/\=25/g, '%').replace(/\=23/g, '#')
	    .replace(/\=3f/g, '?').replace(/\=26/g, '&').replace(/\=3b/g, ';')
	    .replace(/\=3d/g, '=') ;
    uri= _proxy_jslib_proxy_decode(uri) ;
    return uri + query + frag ;
}
function _proxy_jslib_cookie_to_client(cookie) {
    if (_proxy_jslib_cookies_are_banned_here) return '' ;
    var u= _proxy_jslib_parse_url(_proxy_jslib_URL) ;
    if (u==null) {
	alert("CGIProxy Error: Can't parse URL <"+_proxy_jslib_URL+">; not setting cookie.") ;
	return '' ;
    }
    var source_server= u[4] ;
    var source_path= u[6] ;
    if (source_path.substr(0,1)!='/') source_path= '/' + source_path ;
    var name, value, expires_clause, path, domain, secure_clause ;
    var new_name, new_value, new_cookie ;
    name= value= expires_clause= path= domain= secure_clause=
	new_name= new_value= new_cookie= '' ;
    if (/^\s*([^\=\;\,\s]*)\s*\=?\s*([^\;]*)/.test(cookie)) {
	name= RegExp.$1 ; value= RegExp.$2 ;
    }
    if (/\;\s*(expires\s*\=[^\;]*)/i.test(cookie))        expires_clause= RegExp.$1 ;
    if (/\;\s*path\s*\=\s*([^\;\,\s]*)/i.test(cookie))    path= RegExp.$1 ;
    if (/\;\s*domain\s*\=\s*([^\;\,\s]*)/i.test(cookie))  domain= RegExp.$1 ;
    if (/\;\s*(secure\b)/i.test(cookie))                  secure_clause= RegExp.$1 ;
    if (path=='') path= _proxy_jslib_COOKIE_PATH_FOLLOWS_SPEC  ? source_path  : '/' ;
    if (domain=='') {
	domain= source_server ;
    } else {
	domain= domain.replace(/\.+$/, '') ;
	domain= domain.replace(/\.{2,}/g, '.') ;
	if ( (source_server.substr(source_server.length-domain.length)!=domain.toLowerCase()) && ('.'+source_server!=domain) )
	    return '' ;
	var dots= domain.match(/\./g) ;
	if (_proxy_jslib_RESPECT_THREE_DOT_RULE) {
	    if (dots.length<3 && !( dots.length>=2 && /\.(com|edu|net|org|gov|mil|int)$/i.test(domain) ) )
		return '' ;
	} else {
	    if (dots.length<2) {
		if (domain.match(/^\./)) return '' ;
		domain= '.'+domain ;
		if (dots.length<1) return '' ;
	    }
	}
    }
    new_name=  _proxy_jslib_cookie_encode('COOKIE;'+name+';'+path+';'+domain) ;
    new_value= _proxy_jslib_cookie_encode(value+';'+secure_clause) ;
    if (_proxy_jslib_SESSION_COOKIES_ONLY && (expires_clause!='')) {
	/^expires\s*\=\s*(.*)$/i.test(expires_clause) ;
	var expires_date= RegExp.$1.replace(/\-/g, ' ') ;  // Date.parse() can't handle "-"
	if ( Date.parse(expires_date) > (new Date()).getTime() ) expires_clause= '' ;
    }
    new_cookie= new_name+'='+new_value ;
    if (expires_clause!='') new_cookie= new_cookie+'; '+expires_clause ;
    new_cookie= new_cookie+'; path='+_proxy_jslib_SCRIPT_NAME+'/' ;
    return new_cookie ;
}
function _proxy_jslib_cookie_from_client(doc) {
    if (_proxy_jslib_cookies_are_banned_here) return '' ;
    if (!doc.cookie) return '' ;
    var target_path, target_server, target_scheme ;
    var u= _proxy_jslib_parse_url(_proxy_jslib_URL) ;
    if (u==null) {
	alert("CGIProxy Error: Can't parse URL <"+_proxy_jslib_URL+">; not using cookie.") ;
	return ;
    }
    target_scheme= u[1] ;
    target_server= u[4] ;
    target_path= u[6] ;
    if (target_path.substr(0,1)!='/') target_path= '/' + target_path ;
    var matches= new Array() ;
    var pathlen= new Object() ;
    var cookies= doc.cookie.split(/\s*;\s*/) ;
    for (var c= 0 ; c < cookies.length ; c++) {
	var nv= cookies[c].split('=', 2) ;
	var name=  _proxy_jslib_cookie_decode(nv[0]) ;
	var value= _proxy_jslib_cookie_decode(nv[1]) ;
	var n= name.split(/;/) ;
	if (n[0]=='COOKIE') {
	    var cname, path, domain, cvalue, secure ;
	    cname= n[1] ; path= n[2] ; domain= n[3].toLowerCase() ;
	    var v= value.split(/;/) ;
	    cvalue= v[0] ; secure= v[1] ;
	    if (secure!='' && secure!=null && target_scheme!='https:') continue ;
	    if ( ((target_server.substr(target_server.length-domain.length)==domain)
		  || (domain=='.'+target_server))
		&& target_path.substr(0, path.length)==path )
	    {
		matches[matches.length]= cname  ? cname+'='+cvalue  : cvalue ;
		pathlen[cname+'='+cvalue]= path.length ;
	    }
	}
    }
    matches.sort(function (v1,v2) { return (pathlen[v2]-pathlen[v1]) } ) ;
    return matches.join('; ') ;
}
function _proxy_jslib_proxify_html(html, doc, is_full_page, reverse) {
    var out= [] ;
    var match, m2, last_lastIndex= 0, remainder ;
    var tag_name, html_pos, head_pos ;
    var base_url, base_url_jsq, jslib_block, insert_string, insert_pos ;
    var jslib_added= false ;
    if (html==void 0) return [void 0, void 0, false, false] ;
    if (typeof html=='number') return [html, void 0, false, false] ;
    if (is_full_page) _proxy_jslib_needs_jslib= false ;
    var RE= new RegExp(/([^\<]*)(?:(\<\!\-\-(?=[\s\S]*?\-\-\>)[\s\S]*?\-\-\s*\>|\<\!\-\-(?![\s\S]*?\-\-\>)[\s\S]*?\>)|(\<\s*script\b[\s\S]*?\<\s*\/script\b[\s\S]*?\>)|(\<\s*style\b[\s\S]*?\<\s*\/style\b[\s\S]*?\>)|(\<\![^\>]*\>)|(\<\?[^\>]*\>)|(\<[^\>]*\>))?/gi) ;
    var RE2= new RegExp(/[^\>]*(?:\>|$)/g) ;
    while ((last_lastIndex!=html.length) && (match= RE.exec(html))) {
	if (match.index!=last_lastIndex) {
	    remainder= html.slice(last_lastIndex) ;
	    break ;
	}
	last_lastIndex= RE2.lastIndex= RE.lastIndex ;
	out.push(match[1]) ;
	if (match[2]) {
	    out.push(_proxy_jslib_proxify_comment(match[2], doc, reverse)) ;
	} else if (match[3]) {
	    out.push(_proxy_jslib_proxify_script_block(match[3], doc, reverse)) ;
	} else if (match[4]) {
	    out.push(_proxy_jslib_proxify_style_block(match[4], doc, reverse)) ;
	} else if (match[5]) {
	    out.push(_proxy_jslib_proxify_decl_bang(match[5], doc, reverse)) ;
	} else if (match[6]) {
	    out.push(_proxy_jslib_proxify_decl_question(match[6], doc, reverse)) ;
	} else if (match[7]) {
	    m2= match[7].match(/^\<\s*(\/?[A-Za-z][\w\.\:\-]*)/) ;
if (!m2) alert('no m2; match[7]=['+match[7]+']') ;
	    tag_name= m2[1].toLowerCase() ;
	    if ((tag_name=='script') || (tag_name=='style')) {
		remainder= match[7]+html.slice(last_lastIndex) ;
		break ;
	    }
	    if ((tag_name=='frameset') && _proxy_jslib_doing_insert_here && !_proxy_jslib_is_in_frame && !reverse) {
		_proxy_jslib_return_frame_doc(_proxy_jslib_wrap_proxy_encode(_proxy_jslib_URL), doc) ;
		return ['', void 0, false, true] ;
	    }
	    if (tag_name=='/object') _proxy_jslib_current_object_classid= '' ;
	    var new_element= _proxy_jslib_proxify_element(match[7], doc, reverse) ;
	    while (new_element==void 0 && last_lastIndex!=html.length) {
		m2= RE2.exec(html) ;
		last_lastIndex= RE.lastIndex= RE2.lastIndex ;
		match[7]+= m2[0] ;
		new_element= _proxy_jslib_proxify_element(match[7], doc, reverse) ;
	    }
	    out.push(new_element) ;
	    if      (tag_name=='html') { html_pos= out.length }
	    else if (tag_name=='head') { head_pos= out.length }
	}
    }
    if ((last_lastIndex!=html.length) && !remainder)
	 remainder= html.slice(last_lastIndex) ;
    if (reverse) _proxy_jslib_needs_jslib= false ;
    if (is_full_page && _proxy_jslib_needs_jslib && !reverse) {
	jslib_block= '<script type="text/javascript" src="'
		       + _proxy_jslib_html_escape(_proxy_jslib_url_start+_proxy_jslib_wrap_proxy_encode('x-proxy://scripts/jslib'))
		       + '"><\/script>\n' ;
	if (!doc._proxy_jslib_base_url) {
	    base_url= _proxy_jslib_parse_full_url(doc.URL)[2] ;
	    _proxy_jslib_set_base_vars(doc, base_url) ;
	}
	base_url_jsq= doc._proxy_jslib_base_url
		.replace(/(["\\])/g, function (p) { return '\\'+p } ) ;
	if (base_url_jsq!=void 0) base_url_jsq= '"' + base_url_jsq + '"' ;
	insert_string= '<script type="text/javascript">_proxy_jslib_pass_vars('
		     + base_url_jsq + ','
		     + _proxy_jslib_cookies_are_banned_here + ','
		     + _proxy_jslib_doing_insert_here + ','
		     + _proxy_jslib_SESSION_COOKIES_ONLY + ','
		     + _proxy_jslib_COOKIE_PATH_FOLLOWS_SPEC + ','
		     + _proxy_jslib_RESPECT_THREE_DOT_RULE + ','
		     + _proxy_jslib_ALLOW_UNPROXIFIED_SCRIPTS + ',"'
		     + _proxy_jslib_default_script_type + '","'
		     + _proxy_jslib_default_style_type + '");<\/script>\n' ;
	insert_pos= head_pos || html_pos || 0 ;
	out.splice(insert_pos, 0, jslib_block, insert_string) ;
	jslib_added= true ;
    }
    return [out.join(''), remainder, jslib_added] ;
}
function _proxy_jslib_proxify_comment(comment, doc, reverse) {
    var m= comment.match(/^\<\!\-\-(.*?)(\-\-\s*)?>$/) ;
    var contents= m[1] ;
    var end= m[2] ;
    contents= _proxy_jslib_proxify_html(contents, doc, false, reverse)[0] ;
    comment= '<!--' + contents + end + '>' ;
    return comment ;
}
function _proxy_jslib_proxify_decl_bang(decl_bang, doc, reverse) {
    var q ;
    var inside= decl_bang.match(/^\<\!([^>]*)/)[1] ;
    var words= inside.match(/\"[^\"\>]*\"?|\'[^\'\>]*\'?|[^\'\"][^\s\>]*/g) ;
    for (var i=0 ; i<words.length ; i++) {
	words[i]= words[i].replace(/^\s*/, '') ;
	if (words[i].match(/^[\'\"]?http\:\/\/www\.w3\.org\//)) continue ;
	if (words[i].match(/^[\"\']?[\w\+\.\-]+\:\/\//)) {
	    if      (words[i].match(/^'/))  { q= "'" ; words[i]= words[i].replace(/^\'|\'$/g, '') }
	    else if (words[i].match(/^"/))  { q= '"' ; words[i]= words[i].replace(/^\"|\"$/g, '') }
	    else                            { q= '' }
	    words[i]= q + _proxy_jslib_full_url(words[i], doc, reverse) + q ;
	}
    }
    decl_bang= '<!' + words.join(' ') + '>' ;
    return decl_bang ;
}
function _proxy_jslib_proxify_decl_question(decl_question, doc, reverse) {
    return decl_question ;
}
function _proxy_jslib_proxify_script_block(script_block, doc, reverse) {
    var m1, m2, tag, script, attrs, attr, name ;
    attr= new Object() ;
    m1= script_block.match(/^(\<\s*script\b[^\>]*\>)([\s\S]*)\<\s*\/script\b[^\>]*\>$/i) ;
    var o_n_j= _proxy_jslib_needs_jslib ;   // hack hack
    tag= _proxy_jslib_proxify_element(m1[1], doc, reverse) ;
    _proxy_jslib_needs_jslib= o_n_j ;
    script= m1[2] ;
    attrs= tag.match(/^\<\s*script\b([^\>]*)\>/i)[1] ;
    while (m2= attrs.match(/([A-Za-z][\w\.\:\-]*)\s*(\=\s*(\"([^\"\>]*)\"?|\'([^\'\>]*)\'?|([^\'\"][^\s\>]*)))?/)) {
	attrs= attrs.substr(m2[0].length) ;
	name= m2[1].toLowerCase() ;
	if (attr[name]!=null) continue ;
	attr[name]= m2[4]  ? m2[4]  : m2[5]  ? m2[5]  : m2[6]  ? m2[6]  : '' ;
	attr[name]= _proxy_jslib_html_unescape(attr[name]) ;
    }
    if (attr.type!=null) attr.type= attr.type.toLowerCase() ;
    if (!attr.type && attr.language) {
	attr.type= attr.language.match(/javascript|ecmascript|livescript|jscript/i)
						     ? 'application/x-javascript'
		 : attr.language.match(/css/i)       ? 'text/css'
		 : attr.language.match(/vbscript/i)  ? 'application/x-vbscript'
		 : attr.language.match(/perl/i)      ? 'application/x-perlscript'
		 : attr.language.match(/tcl/i)       ? 'text/tcl'
		 : '' ;
    }
    if (!attr.type) attr.type= _proxy_jslib_default_script_type ;
    script= _proxy_jslib_proxify_block(script, attr.type,
		_proxy_jslib_ALLOW_UNPROXIFIED_SCRIPTS, reverse) ;
    return tag+script+'<\/script>' ;
}
function _proxy_jslib_proxify_style_block(style_block, doc, reverse) {
    var m1, m2, tag, stylesheet, attrs, type ;
    m1= style_block.match(/^(\<\s*style\b[^\>]*\>)([\s\S]*)\<\s*\/style\b[^\>]*\>$/i) ;
    var o_n_j= _proxy_jslib_needs_jslib ;   // hack hack
    tag= _proxy_jslib_proxify_element(m1[1], doc, reverse) ;
    _proxy_jslib_needs_jslib= o_n_j ;
    stylesheet= m1[2] ;
    attrs= tag.match(/^\<\s*style\b([^\>]*)\>/i)[1] ;
    while (m2= attrs.match(/([A-Za-z][\w\.\:\-]*)\s*(\=\s*(\"([^\"\>]*)\"?|\'([^\'\>]*)\'?|([^\'\"][^\s\>]*)))?/)) {
	attrs= attrs.substr(m2[0].length) ;
	if (m2[1].toLowerCase()=='type') {
	    type= m2[4]!=null  ? m2[4]  : m2[5]!=null  ? m2[5]  : m2[6]!=null  ? m2[6]  : '' ;
	    type= _proxy_jslib_html_unescape(type).toLowerCase() ;
	    break ;
	}
    }
    if (!type) type= _proxy_jslib_default_style_type ;
    stylesheet= _proxy_jslib_proxify_block(stylesheet, type,
			_proxy_jslib_ALLOW_UNPROXIFIED_SCRIPTS, reverse) ;
    return tag+stylesheet+'<\/style>' ;
}
function _ostring(o, depth, filter) {
    var ret= '' ;
    if (depth>0)
	for (var p in o) {
try {
	    if (filter && !p.match(filter)) continue ;
		ret+= p + ':'
		      + ( (o[p]&&(typeof(o[p])=='object'))
			  ? _ostring(o[p],depth-1)
			  : ('"'+o[p]+'"') )
		      + ', ' ;
} catch(e) { ret+= p+':<<error: '+e+'>>, ' }
	}
    return '{' + ret + '}' ;
}
function _nodestring(n) {
    if (!n) return '' ;
    var ret= '' ;
    ret+= '<' + n.nodeName ;
    if (n.attributes)
	for (var i= 0 ; i<n.attributes.length ; i++)
	    ret+= ' ' + n.attributes[i].nodeName + '="' + n.attributes[i].nodeValue+'"' ;
    ret+= '>' ;
    ret+= n.innerHTML + '<\/' + n.nodeName + '>' ;
    return ret ;
}
function _node_is_in_document(node) {
    for ( ; (node!=null) && (node.nodeType!=9) ; node= node.parentNode) ;   // Node.DOCUMENT_NODE==9
    return node!=null ;
}
function _ancestorsof(node) {
    var ret= '' ;
    for ( ; (node!=null) && (node.nodeType!=9) ; node= node.parentNode)
	ret+= '['+node.nodeType+']['+node+']['+_node_is_in_document(node)+']\n' ;
   return ret ;
}
function _object_type(o) { return _proxy_jslib_object_type(o) }
function _proxy_jslib_proxify_element(element, doc, reverse) {
    var m1, m2, tag_name, attrs, attr= {}, names= [], name, i, rebuild, end_slash,
	old_url_start, flags ;
    if (!doc) doc= window.document ;
    if (!(m1= element.match(/^\<\s*([A-Za-z][\w\.\:\-]*)\s*([\s\S]*)$/))) return element ;
    tag_name= m1[1].toLowerCase() ;
    attrs= m1[2] ;
    if (attrs=='') return element ;
    while (m2= attrs.match(/([A-Za-z][\w\.\:\-]*)\s*(\=\s*(\"([^\"]*)\"|\'([^\']*)\'|([^\'\"][^\s\>]*)|(\'[^\']*$|\"[^\"]*$)))?/)) {
	if (m2[7]) return void 0 ;
	attrs= attrs.substr(m2.index+m2[0].length) ;
	name= m2[1].toLowerCase() ;
	if (name in attr) { rebuild= 1 ; continue }
	attr[name]= (m2[4]!=void 0 && m2[4]!='') ? m2[4]
		  : (m2[5]!=void 0 && m2[5]!='') ? m2[5]
		  : (m2[6]!=void 0 && m2[6]!='') ? m2[6]
		  : '' ;
	attr[name]= _proxy_jslib_html_unescape(attr[name]) ;
	names.push(name) ;
    }
    for (i= 0 ; i<names.length ; i++) {
	name= names[i] ;
	if (attr[name].match(/\&\{.*\}\;/)) { delete attr[name] ; rebuild= 1 ; continue }
	if (name.match(/^on/)) {
	    attr[name]= _proxy_jslib_proxify_block(attr[name], _proxy_jslib_default_script_type, _proxy_jslib_ALLOW_UNPROXIFIED_SCRIPTS, reverse) ;
	    rebuild= 1 ;
	}
    }
    if (tag_name=='object') {
	_proxy_jslib_current_object_classid= attr.classid ;
    } else if (tag_name=='param') {
	if (_proxy_jslib_current_object_classid &&
	    _proxy_jslib_current_object_classid.match(/^\s*clsid\:\{?D27CDB6E-AE6D-11CF-96B8-444553540000\}?\s*$/i))
	{
	    if (attr.name && attr.name.match(/^movie$/i)) {
		attr.value= _proxy_jslib_full_url(attr.value, doc, reverse, 1) ;
		rebuild= 1 ;
	    }
	}
    }
    if ('style' in attr) {
	if (attr.style.match(/(expression|function)\s*\(/i ))
	    attr.style= _proxy_jslib_global_replace(attr.style, /\b((expression|function)\s*\()([^\)]*)/i,
						    function (p) { return p[1]+_proxy_jslib_proxify_js(p[3], void 0, void 0, void 0, reverse) } ) ;
	attr.style= _proxy_jslib_proxify_block(attr.style, _proxy_jslib_default_style_type, _proxy_jslib_ALLOW_UNPROXIFIED_SCRIPTS, reverse) ;
	rebuild= 1 ;
    }
    if ('href' in attr)        { attr.href=        _proxy_jslib_full_url(attr.href, doc, reverse) ;        rebuild= 1 }
    if ('src' in attr)         {
	if (tag_name=='frame' || tag_name=='iframe') {
				 attr.src=         _proxy_jslib_full_url_by_frame(attr.src, doc, 1, reverse) ; rebuild= 1 ;
	} else if (tag_name=='script') {   // messy  :P
	    var old_url_start= _proxy_jslib_url_start ;
	    var flags= _proxy_jslib_unpack_flags(_proxy_jslib_packed_flags) ;
	    flags[6]= (attr.type!==void 0)  ? attr.type  : _proxy_jslib_default_script_type ;
	    _proxy_jslib_url_start= _proxy_jslib_SCRIPT_URL + '/' + _proxy_jslib_pack_flags(flags) + '/' ;
				 attr.src=         _proxy_jslib_full_url(attr.src, doc, reverse) ;         rebuild= 1 ;
	    _proxy_jslib_needs_jslib= true ;
	    _proxy_jslib_url_start= old_url_start ;
	} else if (tag_name=='embed') {
			       { attr.src=         _proxy_jslib_full_url(attr.src, doc, reverse, (attr.type && attr.type.toLowerCase()=='application/x-shockwave-flash')) ;      rebuild= 1 }
	} else                 { attr.src=         _proxy_jslib_full_url(attr.src, doc, reverse) ;         rebuild= 1 }
    }
    if ('lowsrc' in attr)      { attr.lowsrc=      _proxy_jslib_full_url(attr.lowsrc, doc, reverse) ;      rebuild= 1 }
    if ('dynsrc' in attr)      { attr.dynsrc=      _proxy_jslib_full_url(attr.dynsrc, doc, reverse) ;      rebuild= 1 }
    if ('action' in attr)      { attr.action=      _proxy_jslib_full_url(attr.action, doc, reverse) ;      rebuild= 1 }
    if ('background' in attr)  { attr.background=  _proxy_jslib_full_url(attr.background, doc, reverse) ;  rebuild= 1 }
    if ('usemap' in attr)      { attr.usemap=      _proxy_jslib_full_url(attr.usemap, doc, reverse) ;      rebuild= 1 }
    if ('cite' in attr)        { attr.cite=        _proxy_jslib_full_url(attr.cite, doc, reverse) ;        rebuild= 1 }
    if ('longdesc' in attr)    { attr.longdesc=    _proxy_jslib_full_url(attr.longdesc, doc, reverse) ;    rebuild= 1 }
    if ('codebase' in attr)    { attr.codebase=    _proxy_jslib_full_url(attr.codebase, doc, reverse) ;    rebuild= 1 }
    if ('poster' in attr)      { attr.poster=      _proxy_jslib_full_url(attr.poster, doc, reverse) ;      rebuild= 1 }
    if ('pluginspage' in attr) { attr.pluginspage= _proxy_jslib_full_url(attr.pluginspage, doc, reverse) ; rebuild= 1 }
    if ((tag_name=='meta') && attr['http-equiv'] && attr['http-equiv'].match(/^\s*refresh\b/i)) {
	attr.content= _proxy_jslib_global_replace(
			  attr.content,
			  /(\;\s*URL\=)\s*(\S*)/i,
			  function (a) { return a[1] + _proxy_jslib_full_url(a[2], doc, reverse) } ) ;
	rebuild= 1 ;
    }
    if (!rebuild) return element ;
    attrs= '' ;
    for (i= 0 ; i<names.length ; i++) {
	name= names[i] ;
	if (attr[name]==null) continue ;
	if (attr[name]=='')  { attrs+= ' '+name ; continue }
	if (!attr[name].match(/\"/) || attr[name].match(/\'/)) {
	    attrs+= ' '+name+'="'+_proxy_jslib_html_escape(attr[name])+'"' ;
	} else {
	    attrs+= ' '+name+"='"+_proxy_jslib_html_escape(attr[name])+"'" ;
	}
    }
    end_slash= element.match(/\/\s*>?$/)  ? ' /'  : '' ;
    return '<'+tag_name+attrs+end_slash+'>' ;
}
function _proxy_jslib_element2tag (e) {
    var ret= '', i ;
if (e.nodeType!=1) alert('in element2tag; nodeType=['+e.nodeType+']') ;
    for (i= 0 ; i<e.attributes.length ; i++)
	ret+= ' '+e.attributes[i].nodeName+'="'+e.attributes[i].nodeValue+'"' ;
    ret= '<'+e.tagName+ret+'>' ;
    for (i=0 ; i<e.childNodes.length ; i++)
	if      (e.childNodes[i].nodeType==1) ret+= '\n'+_proxy_jslib_element2tag(e.childNodes[i]) ;
	else if (e.childNodes[i].nodeType==3) ret+= '\n'+e.childNodes[i].nodeValue ;
    return ret ;
}
function _proxy_jslib_proxify_attribute(name, value, reverse) {
    if (/\&\{.*\}\;/.test(value)) return ;
    name= name.toLowerCase() ;
    if (/^(href|src|lowsrc|dynsrc|action|background|usemap|cite|longdesc|codebase|poster)$/i.test(name)) {
	return _proxy_jslib_full_url_by_frame(value, null, true, reverse) ;
    } else if (/^on/i.test(name)) {
	return _proxy_jslib_proxify_block(value, _proxy_jslib_default_script_type,
			_proxy_jslib_ALLOW_UNPROXIFIED_SCRIPTS, reverse) ;
    } else if (/^style$/i.test(name)) {
	if (/(expression|function)\s*\(/i.test(value)) return ;
	else return value ;
    } else {
	return value ;
    }
}
function _proxy_jslib_proxify_block(s, type, unknown_type_ok, reverse) {
    type= type.toLowerCase() ;
    if (type=='text/css') {
	return _proxy_jslib_proxify_css(s, reverse) ;
    } else if (type.match(/^(application\/x\-javascript|application\/x\-ecmascript|application\/javascript|application\/ecmascript|text\/javascript|text\/ecmascript|text\/livescript|text\/jscript)$/)) {
	return _proxy_jslib_proxify_js(s, 1, void 0, void 0, reverse) ;
    } else {
	return unknown_type_ok ? s : '' ;
    }
}
function _proxy_jslib_proxify_css(css, reverse) {
   if (css==null) return css ;
    var out= '', m1 ;
    while (m1= css.match(/url\s*\(\s*(([^\)]*\\\))*[^\)]*)(\)|$)/i)) {
	out+= css.substr(0,m1.index) + 'url(' + _proxy_jslib_css_full_url(m1[1], null, reverse) + ')' ;
	css= css.substr(m1.index+m1[0].length) ;
    }
    out+= css ;
    css= out ;
    out= '' ;
    while (m1=css.match(/\@import\s*(\"[^"]*\"|\'[^']*\'|[^\;\s\<]*)/i)) {
	if (!m1[1].match(/^url\s*\(/i)) {   // to avoid use of "(?!...)"
	    out+= css.substr(0,m1.index) + '@import ' + _proxy_jslib_css_full_url(m1[1], null, reverse) ;
	} else {
	    out+= css.substr(0,m1.index) + m1[0] ;
	}
	css= css.substr(m1.index+m1[0].length) ;
    }
    out+= css ;
    css= out ;
    out= '' ;
    while (m1= css.match(/((expression|function)\s*\()([^)]*)/i)) {
	out+= css.substr(0,m1.index) + m1[1] + _proxy_jslib_proxify_js(m1[3], void 0, void 0, void 0, reverse) ;
	css= css.substr(m1.index+m1[0].length) ;
    }
    out+= css ;
    return out ;
}
function _proxy_jslib_css_full_url(url, doc, reverse) {
    var q= '' ;
    url= url.replace(/\s+$/, '') ;
    if      (url.match(/^\"/)) { q= '"' ; url= url.replace(/^\"|\"$/g, '') }
    else if (url.match(/^\'/)) { q= "'" ; url= url.replace(/^\'|\'$/g, '') }
    url= url.replace(/\\(.)/g, "$1").replace(/^\s+|\s+$/g, '') ;
    url= _proxy_jslib_full_url(url, doc, reverse) ;
    url= url.replace(/([\(\)\,\s\'\"\\])/g, function (p) { return '\\'+p } ) ;
    return q+url+q ;
}
function _proxy_jslib_return_frame_doc(enc_URL, doc) {
    var top_URL= _proxy_jslib_html_escape(_proxy_jslib_url_start_inframe
					  + _proxy_jslib_wrap_proxy_encode('x-proxy://frames/topframe?URL='
								      + encodeURIComponent(enc_URL) ) ) ;
    var page_URL= _proxy_jslib_html_escape(_proxy_jslib_url_start_inframe + enc_URL) ;
    doc.open();
    doc.write('<html>\n<frameset rows="80,*">\n'
	    + '<frame src="'+top_URL+'">\n<frame src="'+page_URL+'" name="_proxy_jslib_main_frame">\n'
	    + '<\/frameset>\n</html>') ;
    doc.close() ;
}
function _proxy_jslib_proxify_js(s, top_level, with_level, in_new_statement, reverse) {
    if ((s==void 0) || (s=='')) return s ;
    if (with_level==void 0) with_level= 0 ;
    if (in_new_statement==void 0) in_new_statement= 0 ;
    if (reverse) return s ;
    if (!((typeof s=='string') || (s instanceof String) || (s instanceof Array)))
	return s ;
    var jsin= _proxy_jslib_tokenize_js(s) ;
    return _proxy_jslib_proxify_js_tokens(jsin, 0, jsin.length, top_level, with_level, in_new_statement, reverse) ;
}
function _proxy_jslib_proxify_js_tokens(jsin, start, end, top_level, with_level, in_new_statement, reverse)
{
    var RE= _proxy_jslib_RE ;
    var i_jsin, out, element, token, last_token, new_last_token, newline_since_last_token,
	term_so_far= '', sub_expr, op, new_val, cur_val_str, inc_by,
	in_braces= 0, in_func= false, expr, new_expr,
	var_decl, varname, eq, value, skip1, skip2, funcname, with_obj, code,
	match, m2, o_p, ostart, oend, pstart, pend, p, estart, eend,
	skipped, i, i_next_token, i_lt, next_token, next_expr, next_expr_st, skipped, args, fn_body, t ;
    out= [] ;
    out.push= _proxy_jslib_ORIGINAL_ARRAY_push ;  // hack to use original ARRAY.push()
    if (top_level) _proxy_jslib_does_write= false ;
    i_jsin= start ;
  OUTER:
    while (i_jsin<end) {
	element= jsin[i_jsin++] ;
	token= element.skip  ? void 0  : element ;
	if (RE.LINETERMINATOR.test(element)) newline_since_last_token= true ;
	new_last_token= '' ;
	if (token=='{') {
	    in_braces++ ;
	} else if (token=='}') {
	    if (--in_braces==0) in_func= false ;
	}
	i_next_token= i_lt= i_jsin ;
	while (i_next_token<end && jsin[i_next_token].skip) i_next_token++ ;
	next_token= (i_next_token<end)  ? jsin[i_next_token]  : void 0 ;
	while (i_lt<i_next_token && !RE.LINETERMINATOR.test(jsin[i_lt])) i_lt++ ;
	if (i_lt==i_next_token) i_lt= void 0 ;
	if (!token) {
	    if (term_so_far) term_so_far+= element ;
	    else out.push(element) ;
	} else if (match= token.match(/^\_proxy(\d*)(\_.*)/))   {
	    term_so_far+= '_proxy'+(match[1]-0+1)+match[2] ;
	} else if (RE.N_S_RE.test(token)) {
	    out.push(term_so_far) ;
	    term_so_far= token ;
	} else if (/^(\+\+|\-\-|delete)$/.test(token)) {
	    if (token=='--' && (next_token=='>')) {
		i_jsin= i_next_token+1 ;
		out.push(term_so_far, '-->') ;
		term_so_far= '' ;
	    } else if (term_so_far!='' && !newline_since_last_token) {
		out.push(term_so_far, token) ;
		term_so_far= '' ;
	    } else {
		out.push(term_so_far) ;
		term_so_far= '' ;
		o_p= _proxy_jslib_get_next_js_term(jsin, i_jsin, end) ;
		if (o_p==void 0) break ;
		ostart= o_p[0] ;
		oend=   o_p[1] ;
		pstart= o_p[2] ;
		pend=   o_p[3] ;
		if (oend>ostart) {
		    if (pstart>=pend) {
			p= '' ;
		    } else if (jsin[pstart]=='[') {
			p= _proxy_jslib_proxify_js_tokens(jsin, pstart+1, pend-1, 0, with_level) ;
		    } else {
			p= "'" + jsin[pstart] + "'" ;  // should be single identifier
		    }
		    out.push(" _proxy_jslib_assign('" + token + "', ("
			    + _proxy_jslib_proxify_js_tokens(jsin, ostart, oend, 0, with_level) + "), ("
			    + p + "), '')" ) ;
		} else {
		    p= jsin[pstart] ;   // should be single identifier
		    out.push("(" + p + "= _proxy_jslib_assign_rval('"
			     + token + "', '" + p + "', '', '', "
			     + "(typeof " + p + "=='undefined' ? void 0 : " + p + ")))") ;
		}
		i_jsin= pend ;
	    }
	} else if (token=='eval' && (next_token=='(')) {
	    estart= i_jsin= i_next_token+1 ;
	    eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 1) ;
	    if (i_jsin==void 0 || i_jsin>=end || jsin[i_jsin++]!=')') break ;
	    term_so_far+= 'eval(_proxy_jslib_proxify_js(('
			+ _proxy_jslib_proxify_js_tokens(jsin, estart, eend, 0, with_level)
			+ '), 0, ' + with_level + ') )' ;
	    _proxy_jslib_needs_jslib= true ;
	} else if (/^(open|write|writeln|replace|load|eval|setInterval|setTimeout|toString|src|href|background|lowsrc|action|location|poster|URL|referrer|baseURI|useMap|longDesc|cite|codeBase|profile|cssText|insertRule|setStringValue|setProperty|backgroundImage|content|cursor|listStyleImage|host|hostname|pathname|port|protocol|search|setNamedItem|innerHTML|outerHTML|outerText|body|insertAdjacentHTML|setAttribute|setAttributeNode|nodeValue|value|cookie|domain|frames|parent|top|opener|execScript|execCommand|navigate|showModalDialog|showModelessDialog|LoadMovie|close|getElementById|getElementsByTagName)$/.test(token)) {
	    _proxy_jslib_needs_jslib= true ;
	    _proxy_jslib_does_write= _proxy_jslib_does_write || (token=='write') || (token=='writeln') || (token=='eval') ;
	    if ( newline_since_last_token
		 &&   /^(\)|\]|\+\+|\-\-)$|^([a-zA-Z\$\_\\\d'"]|\.\d|\/..)/.test(last_token)
		 && ! /^(case|delete|do|else|in|instanceof|new|typeof|void|function|var)$/.test(last_token) )
	    {
		out.push(term_so_far) ;
		term_so_far= '' ;
	    }
	    term_so_far= term_so_far.replace(RE.DOTSKIPEND, '') ;
	    var next_is_paren= (next_token=='(')  ? 1  : 0 ;
	    if (/^[\{\,]/.test(last_token) && (next_token==':')) {
		out.push(term_so_far, token) ;
		for (i= i_jsin ; i<=i_next_token ; i++) out.push(jsin[i]) ;
		i_jsin= i_next_token+1 ;
		term_so_far= '' ;
		new_last_token= ':' ;
	    } else if ((i_lt==void 0) && (next_token=='++' || next_token=='--')) {
		op= next_token ;
		i_jsin= i_next_token+1 ;
		if (term_so_far=='') {
		    out.push(' ', (with_level
				      ? (token+"= _proxy_jslib_with_assign_rval(_proxy_jslib_with_objs, '', '"+token+"', '"+op+"', '', "+token+")")
				      : (token+"= _proxy_jslib_assign_rval('', '"+token+"', '"+op+"', '', (typeof "+token+"=='undefined' ? void 0 : " + token+"))") )
			     ) ;
		} else {
		    term_so_far= " _proxy_jslib_assign('', "+term_so_far+", '"+token+"', '"+op+"', '')" ;
		}
		new_last_token= ')' ;
	    } else if (next_token && next_token.match(RE.ASSIGNOP)) {
		op= next_token ;
		estart= i_jsin= i_next_token+1 ;
		eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 0) ;
		if (i_jsin==void 0) break ;
		new_val= _proxy_jslib_proxify_js_tokens(jsin, estart, eend, 0, with_level) ;
		if (term_so_far=='') {
		    out.push(' ', (with_level
				? (token+"= _proxy_jslib_with_assign_rval(_proxy_jslib_with_objs, '', '"+token+"', '"+op+"', ("+new_val+"), "+token+")")
				: (token+"= _proxy_jslib_assign_rval('', '"+token+"', '"+op+"', ("+new_val+"), (typeof "+token+"=='undefined' ? void 0 : " + token+"))") )
			    )
		} else {
		    term_so_far= " _proxy_jslib_assign('', "+term_so_far+", '"+token+"', '"+op+"', ("+new_val+"))" ;
		}
		new_last_token= ')' ;
	    } else {
		if (term_so_far=='') {
		    term_so_far= (with_level
				  ? (" _proxy_jslib_with_handle(_proxy_jslib_with_objs, '"+token+"', "+token+", "+next_is_paren+", "+in_new_statement+")")
				  : (" _proxy_jslib_handle(null, '"+token+"', "+token+", "+next_is_paren+", "+in_new_statement+")") ) ;
		} else {
		    term_so_far= " _proxy_jslib_handle("+term_so_far+", '"+token+"', '', "+next_is_paren+", "+in_new_statement+")" ;
		}
		new_last_token= ')' ;
	    }
	} else if (/^(if|while|for|switch)$/.test(token)) {
	    out.push(term_so_far, token) ;
	    term_so_far= '' ;
	    if (next_token!='(') break ;
	    estart= i_jsin= i_next_token+1 ;
	    eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 1) ;
	    if (i_jsin==void 0 || i_jsin>=end || jsin[i_jsin++]!=')') break ;
	    out.push('(', _proxy_jslib_proxify_js_tokens(jsin, estart, eend, 0, with_level), ')') ;
	} else if (token=='catch') {
	    out.push(term_so_far, token) ;
	    term_so_far= '' ;
	    if (next_token!='(') break ;
	    estart= i_jsin= i_next_token+1 ;
	    eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 1) ;
	    if (i_jsin==void 0 || i_jsin>=end || jsin[i_jsin++]!=')') break ;
	    out.push('(') ;
	    for (i= estart ; i<eend ; i++) out.push(jsin[i]) ;
	    out.push(')') ;
	} else if (token=='function') {
	    out.push(term_so_far, token) ;
	    term_so_far= '' ;
	    if (next_token && next_token.match(RE.IdentifierName)) {
		for (i= i_jsin ; i<i_next_token ; i++) out.push(jsin[i]) ;
		funcname= next_token ;
		i_jsin= i_next_token+1 ;
		while (i_jsin<end-1
		       && jsin[i_jsin]=='.' && jsin[i_jsin+1].match(RE.IdentifierName)) {
		    funcname+= jsin[i_jsin] + jsin[i_jsin+1] ;
		    i_jsin+= 2 ;
		}
	    } else {
		funcname= '' ;
	    }
	    if (m2= funcname.match(/^_proxy(\d*)_/))
		funcname= '_proxy' + (m2[1]-0+1) + funcname.replace(/^_proxy(\d*)/, '') ;
	    out.push(funcname) ;
	    i_next_token= i_jsin ;
	    while (i_next_token<end && jsin[i_next_token].skip) i_next_token++ ;
	    for (i= i_jsin+1 ; i<i_next_token ; i++) out.push(jsin[i]) ;
	    if (jsin[i_next_token]!='(') break ;
	    estart= i_jsin= i_next_token+1 ;
	    eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 1) ;
	    if (i_jsin==void 0 || i_jsin>=end || jsin[i_jsin++]!=')') break ;
	    out.push('(') ;
	    for (i= estart ; i<eend ; i++) out.push(jsin[i]) ;
	    out.push(') {') ;
	    while (i_jsin<end && jsin[i_jsin].skip) i_jsin++ ;
	    if (i_jsin>=end || jsin[i_jsin++]!='{') break ;
	    in_braces++ ;
	    in_func= true ;
	} else if (token=='with') {
	    out.push(term_so_far) ;
	    term_so_far= '' ;
	    skip1= '' ;
	    for (i= i_jsin ; i<i_next_token ; i++) skip1+= jsin[i] ;
	    if (next_token!='(') break ;
	    estart= i_jsin= i_next_token+1 ;
	    eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 1) ;
	    with_obj= _proxy_jslib_proxify_js_tokens(jsin, estart, eend, 0, with_level) ;
	    if (i_jsin>=end || jsin[i_jsin++]!=')') break ;
	    skip2= '' ;
	    while (i_jsin<end && jsin[i_jsin].skip) skip2+= jsin[i_jsin++] ;
	    if (jsin[i_jsin]=='{') {
		estart= ++i_jsin ;
		eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 1) ;
		code= '{' + _proxy_jslib_proxify_js_tokens(jsin, estart, eend, 0, with_level+1) + '}' ;
		if (i_jsin>=end || jsin[i_jsin++]!='}') break ;
	    } else {
		estart= i_jsin ;
		eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 0) ;
		code= _proxy_jslib_proxify_js_tokens(jsin, estart, eend, 0, with_level+1) ;
		while (jsin[i_jsin]==',') {
		    estart= ++i_jsin ;
		    eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 0) ;
		    code+= ',' + _proxy_jslib_proxify_js_tokens(jsin, estart, eend, 0, with_level+1) ;
		}
	    }
	    out.push('{', with_level  ? ''  : 'var _proxy_jslib_with_objs= [] ;') ;
	    out.push('with', skip1, '(_proxy_jslib_with_objs[_proxy_jslib_with_objs.length]= (', with_obj, '))', skip2, code) ;
	    out.push('; _proxy_jslib_with_objs.length-- ;}') ;
	    new_last_token= ';' ;
	} else if (token=='var') {
	    out.push(term_so_far, token) ;
	    term_so_far= '' ;
	    while (1) {
		estart= i_jsin ;
		eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 0) ;
		i= estart ;
		while (i<eend && jsin[i].skip) out.push(jsin[i++]) ;
		varname= (i<eend)  ? jsin[i]  : void 0 ;
		if (!varname || !varname.match(RE.IdentifierName)) break OUTER ;
		if (varname && (match= varname.match(/^_proxy(\d*)_/)))
		    varname= '_proxy' + (match[1]-0+1) + varname.replace(/^_proxy(\d*)/, '') ;
		out.push(varname) ;
		i++ ;
		while (i<eend && jsin[i].skip) out.push(jsin[i++]) ;
		eq= (i<eend)  ? jsin[i]  : void 0 ;
		if (eq && !(eq=='=' || eq=='in')) break OUTER ;
		if (eq) out.push(eq, _proxy_jslib_proxify_js_tokens(jsin, i+1, eend, 0, with_level)) ;
		if (i_jsin>=end || jsin[i_jsin]!=',') break ;
		i_jsin++ ;
		out.push(',') ;
	    }
	} else if (token=='new') {
	    out.push(term_so_far) ;
	    term_so_far= '' ;
	    if (next_token=='Function') {
		i_jsin= i_next_token+1 ;
		out.push('_proxy_jslib_new_function') ;
	    } else if (next_token=='function') {
		term_so_far= 'new function' ;
		i_jsin= i_next_token+1 ;
		while (i_jsin<end && jsin[i_jsin].skip) i_jsin++ ;
		if (i_jsin>=end || jsin[i_jsin++]!='(') break ;
		estart= i_jsin ;
		eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 1) ;
		if (i_jsin>=end || jsin[i_jsin++]!=')') break ;
		term_so_far+= '(' ;
		for (i= estart ; i<eend ; i++) term_so_far+= jsin[i] ;
		term_so_far+= ')' ;
		while (i_jsin<end && jsin[i_jsin].skip) i_jsin++ ;
		if (i_jsin>=end || jsin[i_jsin++]!='{') break ;
		estart= i_jsin ;
		eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 1) ;
		if (i_jsin>=end || jsin[i_jsin++]!='}') break ;
		fn_body= _proxy_jslib_proxify_js_tokens(jsin, estart, eend, 0, with_level, 0) ;
		term_so_far+= '{'+fn_body+'}' ;
		new_last_token= '}' ;
	    } else {
		if (next_token=='(') {
		    estart= i_jsin= i_next_token+1 ;
		    eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 1) ;
		    if (i_jsin>=end || jsin[i_jsin++]!=')') break ;
		} else {
		    estart= i_jsin ;
		    eend= i_jsin= _proxy_jslib_get_next_js_constructor(jsin, i_jsin, end) ;
		}
		new_expr= _proxy_jslib_proxify_js_tokens(jsin, estart, eend, 0, with_level, 1) ;
		term_so_far+= 'new ('+new_expr+')' ;
		new_last_token= ')' ;
	    }
	} else if ((token=='return') && !in_func && top_level) {
	    out.push(term_so_far) ;
	    term_so_far= '' ;
	    _proxy_jslib_needs_jslib= true ;
	    estart= i_jsin= i_next_token ;
	    eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 0) ;
	    while (jsin[i_jsin]==',') {
		estart= ++i_jsin ;
		eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 0) ;
	    }
	    out.push('return ((_proxy_jslib_ret= (',
		     _proxy_jslib_proxify_js_tokens(jsin, estart, eend, 0, with_level),
		     ')), _proxy_jslib_flush_write_buffers(), _proxy_jslib_ret)') ;
	} else if (/^(abstract|boolean|break|byte|case|char|class|const|continue|debugger|default|delete|do|else|enum|export|extends|final|finally|float|goto|implements|in|instanceof|int|interface|long|native|package|private|protected|return|short|static|synchronized|throw|throws|transient|try|typeof|void|volatile)$/.test(token)) {
	    out.push(term_so_far, token) ;
	    term_so_far= '' ;
	} else if (token.match(RE.IDENTIFIER)) {
	    if ( newline_since_last_token
		 &&   /^(\)|\]|\+\+|\-\-)$|^([a-zA-Z\$\_\\\d'"]|\.\d|\/..)/.test(last_token)
		 && ! /^(case|delete|do|else|in|instanceof|new|typeof|void|function|var)$/.test(last_token) )
	    {
		out.push(term_so_far) ;
		term_so_far= token ;
	    } else {
		term_so_far+= token ;
	    }
	} else if (token=='.') {
	    term_so_far+= '.' ;
	} else if (token=='(') {
	    _proxy_jslib_does_write= true ;
	    estart= i_jsin ;
	    eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 1) ;
	    if (i_jsin>=end || jsin[i_jsin++]!=')') break ;
	    term_so_far+= '(' + _proxy_jslib_proxify_js_tokens(jsin, estart, eend, 0, with_level) + ')' ;
	    new_last_token= ')' ;
	} else if (token=='[') {
	    estart= i_jsin ;
	    eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 1) ;
	    if (i_jsin>=end || jsin[i_jsin++]!=']') break ;
	    if (eend-estart<=1 && ! /\D/.test(jsin[estart])) {
		term_so_far+= '['+(eend!=estart ?jsin[estart] :'')+']' ;
		new_last_token= ']' ;
	    } else {
		sub_expr= _proxy_jslib_proxify_js_tokens(jsin, estart, eend, 0, with_level) ;
		if (term_so_far) {
		    _proxy_jslib_needs_jslib= true ;
		    new_last_token= ')' ;
		    i_next_token= i_lt= i_jsin ;
		    while (i_next_token<end && jsin[i_next_token].skip) i_next_token++ ;
		    next_token= (i_next_token<end)  ? jsin[i_next_token]  : void 0 ;
		    while (i_lt<i_next_token && !RE.LINETERMINATOR.test(jsin[i_lt])) i_lt++ ;
		    if (i_lt==i_next_token) i_lt= void 0 ;
		    var next_is_paren= (jsin[i_next_token]=='(')  ? 1  : 0 ;
		    if ((i_lt==void 0) && (next_token=='++' || next_token=='--')) {
			op= next_token ;
			i_jsin= i_next_token+1 ;
			term_so_far= " _proxy_jslib_assign('', "+term_so_far+", ("+sub_expr+"), '"+op+"', '')" ;
		    } else if (next_token && next_token.match(RE.ASSIGNOP)) {
			op= next_token ;
			estart= i_jsin= i_next_token+1 ;
			eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 0) ;
			new_val= _proxy_jslib_proxify_js_tokens(jsin, estart, eend, 0, with_level) ;
			term_so_far= " _proxy_jslib_assign('', "+term_so_far+", ("+sub_expr+"), '"+op+"', ("+new_val+"))" ;
		    } else {
			term_so_far= " _proxy_jslib_handle("+term_so_far+", ("+sub_expr+"), '', "+next_is_paren+", "+in_new_statement+")" ;
		    }
		} else {
		    term_so_far= '['+sub_expr+']' ;
		    new_last_token= ']' ;
		}
	    }
	} else if (RE.PUNCDIVPUNC.test(token)) {
	    out.push(term_so_far, token) ;
	    term_so_far= '' ;
	} else {
	}
	if (token) {
	    last_token= new_last_token  ? new_last_token  : token ;
	    newline_since_last_token= false ;
	}
    }
    out.push(term_so_far) ;
    if (top_level && _proxy_jslib_does_write) {
	out.push(' ;\n_proxy_jslib_flush_write_buffers() ;') ;
	_proxy_jslib_needs_jslib= true ;
    }
    return out.join('') ;
    function _proxy_jslib_get_next_js_term(jsin, start, end) {
	var oend, pstart, pend ;
	var i_jsin= start ;
	while (i_jsin<end && jsin[i_jsin].skip) i_jsin++ ;
	if (i_jsin>=end || !jsin[i_jsin].match(RE.IDENTIFIER)) return void 0 ;
	oend= i_jsin ;
	pstart= i_jsin ;
	pend= i_jsin+1 ;
	i_jsin++ ;
	while (i_jsin<end && jsin[i_jsin].skip) i_jsin++ ;
	while (i_jsin<end && (jsin[i_jsin]=='.' || jsin[i_jsin]=='(' || jsin[i_jsin]=='[')) {
	    if (jsin[i_jsin]=='.') {
		oend= i_jsin++ ;
		while (i_jsin<end && jsin[i_jsin].skip) i_jsin++ ;
		if (i_jsin>=end || !jsin[i_jsin].match(RE.IDENTIFIER)) return void 0 ;
		pstart= i_jsin++ ;
		pend= i_jsin ;
	    } else if (jsin[i_jsin]=='[') {
		oend= i_jsin ;
		pstart= i_jsin ;
		i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin+1, end, 1) ;
		if (i_jsin==void 0 || i_jsin>=end || jsin[i_jsin++]!=']') return void 0 ;
		pend= i_jsin ;
	    } else if (jsin[i_jsin]=='(') {
		i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin+1, end, 1) ;
		if (i_jsin==void 0 || i_jsin>=end || jsin[i_jsin++]!=')') return void 0 ;
		oend= pstart= pend= i_jsin ;
	    }
	    while (i_jsin<end && jsin[i_jsin].skip) i_jsin++ ;
	}
	return [start, oend, pstart, pend] ;
    }
    function _proxy_jslib_get_next_js_constructor(jsin, start, end) {
	var c= [], t, skip= [], op, estart, eend ;
	var i_jsin= start ;
	while (i_jsin<end && jsin[i_jsin].skip) i_jsin++ ;
	if (i_jsin>=end || !jsin[i_jsin].match(RE.IDENTIFIER)) return void 0 ;
	i_jsin++ ;
	while (i_jsin<end && jsin[i_jsin].skip) i_jsin++ ;
	while (i_jsin<end && (jsin[i_jsin]=='.' || jsin[i_jsin]=='[')) {
	    if (jsin[i_jsin]=='.') {
		i_jsin++ ;
		while (i_jsin<end && jsin[i_jsin].skip) i_jsin++ ;
		if (i_jsin>=end || !jsin[i_jsin].match(RE.IDENTIFIER)) return void 0 ;
		i_jsin++ ;
	    } else if (jsin[i_jsin]=='[') {
		estart= ++i_jsin ;
		eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, end, 1) ;
		if (i_jsin==void 0 || i_jsin>=end || jsin[i_jsin++]!=']') return void 0 ;
	    }
	    while (i_jsin<end && jsin[i_jsin].skip) i_jsin++ ;
	}
	return i_jsin ;
    }
}
function _proxy_jslib_get_next_js_expr(jsin, start, end, allow_multiple, is_new) {
    var p= [], element, last_token, i ;
    var i_jsin= start ;
    while (i_jsin<end) {
	element= jsin[i_jsin] ;
	switch(element) {
	    case ';':
	    case ',':
		if (!allow_multiple && p.length==0) return i_jsin ;
		break ;
	    case '\x0a':
	    case '\x0d':
		i= i_jsin+1 ;
		while (i<end && jsin[i].skip) i++ ;
		if ( !allow_multiple && p.length==0
		     &&   /^(\)|\]|\+\+|\-\-)$|^([a-zA-Z\$\_\\\d'"]|\.\d|\/..)/.test(last_token)
		     && ! /^(case|delete|do|else|in|instanceof|new|typeof|void|function|var)$/.test(last_token)
		     &&   _proxy_jslib_RE.IDENTIFIER.test(jsin[i]) )
		{
		    return i_jsin ;
		}
		break ;
	    case '(':
		if (is_new && (p.length==0)) return i_jsin ;
	    case '[':
	    case '{':
	    case '?':
		p.push(element) ;
		break ;
	    case ')':
	    case ']':
	    case '}':
	    case ':':
		if (p.length==0) return i_jsin ;
		if (p.length>0 && !(element==':' && p[p.length-1]!='?')) p.length-- ;
		if (element=='}' && p.length==0 && !allow_multiple) return i_jsin+1 ;
		break ;
	}
	if (!element.skip) {
	    last_token= element ;
	}
	i_jsin++ ;
    }
    return p.length==0  ? i_jsin  : void 0 ;
}
function _proxy_jslib_separate_last_js_statement(s) {
    var rest, last, jsin, i, i_jsin, estart, eend, rest_end= 0 ;
    var RE= _proxy_jslib_RE ;
    jsin= _proxy_jslib_tokenize_js(s) ;
    estart= i_jsin= 0 ;
    eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, jsin.length, 0) ;
    while (eend>estart || eend<jsin.length) {
	while (i_jsin<jsin.length && jsin[i_jsin].skip) i_jsin++ ;
	i= i_jsin ;
	while (i<jsin.length && (jsin[i]==';' || jsin[i].skip)) i++ ;
	if (i==jsin.length) break ;
	if ((jsin[i_jsin]).match(RE.STATEMENTTERMINATOR)) {
	    rest_end= ++i_jsin ;
	} else {
	    if (jsin[i_jsin]==',') i_jsin++ ;
	}
	estart= i_jsin ;
	eend= i_jsin= _proxy_jslib_get_next_js_expr(jsin, i_jsin, jsin.length, 0) ;
    }
    rest= jsin.slice(0, rest_end).join('') ;
    last= jsin.slice(rest_end, jsin.length).join('') ;
    return [rest, last] ;
}
function _proxy_jslib_tokenize_js(s) {
    var out= [], match, element, token, div_ok, last_lastIndex= 0 ;
    var RE_InputElementDivG= _proxy_jslib_RE.InputElementDivG ;
    var RE_InputElementRegExpG= _proxy_jslib_RE.InputElementRegExpG ;
    while (1) {
	if (div_ok) {
	    if (!(match= RE_InputElementDivG.exec(s))) break ;
	    if (match.index!= last_lastIndex) break ;
	    last_lastIndex= RE_InputElementRegExpG.lastIndex= RE_InputElementDivG.lastIndex ;
	} else {
	    if (!(match= RE_InputElementRegExpG.exec(s))) break ;
	    if (match.index!= last_lastIndex) break ;
	    last_lastIndex= RE_InputElementDivG.lastIndex= RE_InputElementRegExpG.lastIndex ;
	}
	element= match[0] ;
	token= match[1] ;
	if (!token) {
	    element= new String(element) ;
	    element.skip= true ;
	}
	out.push(element) ;
	if (token) {
	    div_ok= /^(\)|\]|\+\+|\-\-)$|^([a-zA-Z\$\_\\\d'"]|\.\d|\/..)/.test(token)
		 && !/^(case|delete|do|else|in|instanceof|new|return|throw|typeof|void)$/.test(token) ;
	}
    }
    RE_InputElementDivG.lastIndex= RE_InputElementRegExpG.lastIndex= 0 ;
    return out ;
}
function _proxy_jslib_set_RE() {
    if (!_proxy_jslib_RE) {  // saves time for multiple calls
	var RE= {} ;
	RE.WhiteSpace= '[\\x09\\x0b\\x0c \\xa0]' ;
	RE.LineTerminator= '[\\x0a\\x0d]' ;
	RE.Comment= '\\/\\*[\\s\\S]*?\\*\\/|\\/\\/[^\\x0a\\x0d]*|\\<\\!\\-\\-[^\\x0a\\x0d]*' ;
	RE.IdentifierStart= '[a-zA-Z\\$\\_]|\\\\u[\\da-fA-F]{4}' ;
	RE.IdentifierPart= RE.IdentifierStart+'|\\d' ;
	RE.IdentifierName= '(?:'+RE.IdentifierStart+')(?:'+RE.IdentifierPart+')*' ;
	RE.Punctuator= '\\>\\>\\>\\=?|\\=\\=\\=|\\!\\=\\=|\\<\\<\\=|\\>\\>\\=|[\\<\\>\\=\\!\\+\\*\\%\\&\\|\\^\\-]\\=|\\+\\+|\\-\\-|\\<\\<|\\>\\>|\\&\\&|\\|\\||[\\{\\}\\(\\)\\[\\]\\.\\;\\,\\<\\>\\+\\*\\%\\&\\|\\^\\!\\~\\?\\:\\=\\-]' ;
	RE.DivPunctuator= '\\/\\=?' ;
	RE.NumericLiteral= '0[xX][\\da-fA-F]+|(?:0|[1-9]\\d*)(?:\\.\\d*)?(?:[eE][\\+\\-]?\\d+)?|\\.\\d+(?:[eE][\\+\\-]?\\d+)?' ;
	RE.EscapeSequence= 'x[\\da-fA-F]{2}|u[\\da-fA-F]{4}|0|[0-3]?[0-7]\\D|[4-7][0-7]|[0-3][0-7][0-7]|[^\\dxu]' ;
	RE.StringLiteral= '"(?:[^\\"\\\\\\x0a\\x0d]|\\\\(?:'+RE.EscapeSequence+'))*"|'
			+ "'(?:[^\\'\\\\\\x0a\\x0d]|\\\\(?:"+RE.EscapeSequence+"))*'" ;
	RE.RegularExpressionLiteral= '\\/(?:[^\\x0a\\x0d\\*\\\\\\/]|\\\\[^\\x0a\\x0d])(?:[^\\x0a\\x0d\\\\\\/]|\\\\[^\\x0a\\x0d])*\\/(?:'+RE.IdentifierPart+')*' ;
	RE.Token= RE.IdentifierName+'|'+RE.NumericLiteral+'|'+RE.Punctuator+'|'+RE.StringLiteral ;
	RE.InputElementDivG= RE.WhiteSpace+'+|'+RE.LineTerminator+'|'+RE.Comment+
			    '|('+RE.Token+'|'+RE.DivPunctuator+'|'+RE.RegularExpressionLiteral+')' ;
	RE.InputElementRegExpG= RE.WhiteSpace+'+|'+RE.LineTerminator+'|'+RE.Comment+
			       '|('+RE.Token+'|'+RE.RegularExpressionLiteral+'|'+RE.DivPunctuator+')' ;
	RE.SKIP= RE.WhiteSpace+'+|'+RE.LineTerminator+'|'+RE.Comment ;
	RE.SKIP_NO_LT= RE.WhiteSpace+'+|'+RE.Comment ;
	RE.InputElementDivG= new RegExp(RE.InputElementDivG, 'g') ;
	RE.InputElementRegExpG= new RegExp(RE.InputElementRegExpG, 'g') ;
	RE.LINETERMINATOR= new RegExp('^'+RE.LineTerminator+'$') ;
	RE.N_S_RE= new RegExp('^(?:'+RE.NumericLiteral+'|'+RE.StringLiteral+'|'+RE.RegularExpressionLiteral+')$') ;
	RE.DOTSKIPEND= new RegExp('\\.('+RE.WhiteSpace+'+|'+RE.LineTerminator+')*$') ;
	RE.ASSIGNOP= new RegExp('^(\\>\\>\\>\\=|\\<\\<\\=|\\>\\>\\=|[\\+\\*\\/\\%\\&\\|\\^\\-]?\\=)$') ;
	RE.NEXTISINCDEC= new RegExp('^('+RE.SKIP_NO_LT+')*(\\+\\+|\\-\\-)') ;
	RE.SKIPTOPAREN= new RegExp('^(('+RE.SKIP+')*\\()') ;
	RE.SKIPTOCOLON= new RegExp('^(('+RE.SKIP+')*\\:)') ;
	RE.SKIPTOCOMMASKIP= new RegExp('^(('+RE.SKIP+')*\\,('+RE.SKIP+')*)') ;
	RE.PUNCDIVPUNC= new RegExp('^('+RE.Punctuator+'|'+RE.DivPunctuator+')$') ;
	RE.IDENTIFIER= new RegExp('^'+RE.IdentifierName+'$') ;
	RE.SKIPTOOFRAG= new RegExp('^('+RE.SKIP+')*([\\.\\[\\(])') ;
	RE.STATEMENTTERMINATOR= new RegExp('^(;|'+RE.LineTerminator+')') ;
	_proxy_jslib_RE= RE ;
    }
}
function _proxy_jslib_object_type(o) {
    var ret= '' ;
    if ((o==null) || ((typeof(o)!='object') && (typeof(o)!='function')))
	return null ;   // can't use instanceof yet
    if (o instanceof Array) return '' ;  // save time on huge Arrays
    try {
	if (o._proxy_jslib_class) return o._proxy_jslib_class ;
    } catch(e) {
    }
    try {
	if (("navigator" in o) && ("clearInterval" in o) && ("moveBy" in o) && (o.self===o.window)) {
	    ret= 'Window' ;
	} else if (("alinkColor" in o) && ("cookie" in o) && ("writeln" in o)) {
	    ret= 'Document' ;
	} else if (("pathname" in o) && ("protocol" in o) && ("target" in o)) {
	    ret= 'Link' ;   // must do before 'Location'
	} else if (("pathname" in o) && ("protocol" in o)  && ("search" in o)) {
	    ret= 'Location' ;
	} else if (("background" in o) && ("parentLayer" in o) && ("moveAbove" in o)) {
	    ret= 'Layer' ;
	} else if (("hspace" in o) && ("src" in o) && ("border" in o)) {
	    ret= 'Image' ;
	} else if (("action" in o) && ("encoding" in o) && ("submit" in o)) {
	    ret= 'Form' ;
	} else if (("ownerElement" in o) && ("specified" in o)) {
	    ret= 'Attr' ;
	} else if (("nodeName" in o) && ("nodeType" in o) && ("nodeValue" in o)) {
	    ret= 'Node' ;  // must be here after descendents of Node
	} else if (("getNamedItem" in o) && ("removeNamedItem" in o) && ("setNamedItem" in o)) {
	    ret= 'NamedNodeMap' ;
	} else if (("cloneRange" in o) && ("compareBoundaryPoints" in o) && ("surroundContents" in o)) {
	    ret= 'Range' ;
	} else if (("azimuth" in o) && ("backgroundAttachment" in o) && ("pageBreakInside" in o)) {
		ret= 'CSS2Properties' ;
	} else if (("primitiveType" in o) && ("getRectValue" in o) && ("getCounterValue" in o)) {
	    ret= 'CSSPrimitiveValue' ;
	} else if (("getPropertyCSSValue" in o) && ("getPropertyPriority" in o) && ("removeProperty" in o)) {
	    ret= 'CSSStyleDeclaration' ;
	} else if (("GotoFrame" in o) && ("LoadMovie" in o) && ("SetZoomRect" in o)) {
	    ret= 'FlashPlayer' ;
	} else if ( (_proxy_jslib_browser_family!='msie') &&
		    (("getAllResponseHeaders" in o) &&
		     ("getResponseHeader" in o) &&
		     ("setRequestHeader" in o)) ) {
	    ret= 'XMLHttpRequest' ;
	} else if ( (_proxy_jslib_browser_family=='msie') &&
		    (o instanceof ActiveXObject) ) {
	    ret= 'XMLHttpRequest' ;
	}
    } catch(e) {
alert('error calculating object type: '+e.toString()) ;
    }
    try {
	if (ret) o._proxy_jslib_class= ret ;
    } catch(e) {
    }
    return ret ;
}
function _proxy_jslib_parse_url(URL) {
    var u ;
    if (u= URL.match(/^(javascript\:|livescript\:)([\s\S]*)$/i))
	return [ URL, u[1].toLowerCase(), u[2] ] ;
    if (URL.match(/^\s*\#/))
	return [ URL, '', '', '' ,'', '', '', '', URL ] ;
    u= URL.match(/^([\w\+\.\-]+\:)\/\/([^\/\?\#\@]*\@)?(([^\:\/\?\#]*)(\:[^\/\?\#]*)?)([^\?\#]*)([^#]*)(.*)$/) ;
    if (u==null) return ;   // if pattern doesn't match
    for (var i= 0 ; i<u.length ; i++)  if (u[i]==void 0) u[i]= '' ;
    u[1]= u[1].toLowerCase() ;
    u[2]= u[2].replace(/\@$/, '') ;
    u[3]= u[3].toLowerCase() ;
    u[3]= u[3].replace(/\.+(:|$)/, '$1') ;  // close potential exploit
    u[4]= u[4].toLowerCase() ;
    u[4]= u[4].replace(/\.+$/, '') ;      // close potential exploit
    u[5]= u[5].replace(/^\:/, '') ;
    return u ;
}
function _proxy_jslib_parse_full_url(URL) {
    if (typeof(URL)=='number') URL= URL.toString() ;
    if (URL=='about:blank') return ['', '', 'about:blank'] ;
    if (URL.match(/^(javascript|livescript)\:/i)) return ['', '', URL] ;
    if (URL.match(/^\s*\#/)) return ['', '', URL] ;
    if (URL=='') return ['', '', ''] ;
    var cmp, path_cmp ;
    if (_proxy_jslib_PROXY_GROUP.length) {
	for (var i in _proxy_jslib_PROXY_GROUP) {
	    if (URL.substring(0,_proxy_jslib_PROXY_GROUP[i].length)==_proxy_jslib_PROXY_GROUP[i]) {
		path_cmp= URL.substring(_proxy_jslib_PROXY_GROUP[i].length).match(/\/([^\/\?]*)\/?([^\?]*)(\??.*)/) ;
		if (path_cmp==null) return void 0 ;
		return [_proxy_jslib_PROXY_GROUP[i],
			path_cmp[1],
			_proxy_jslib_wrap_proxy_decode(path_cmp[2])+path_cmp[3]] ;
	    }
	}
	return void 0 ;
    }
    cmp= URL.match(/^([\w\+\.\-]+)\:\/\/([^\/\?]*)([^\?]*)(\??.*)$/) ;
    if (cmp==null) return void 0 ;
    cmp[3]=cmp[3].replace(/\%7e/gi, '~') ;
    path_cmp= cmp[3].match(_proxy_jslib_RE_FULL_PATH) ;
    if (cmp==null || path_cmp==null) return void 0 ;
    return [cmp[1]+"://"+cmp[2]+path_cmp[1],
	    path_cmp[2],
	    _proxy_jslib_wrap_proxy_decode(path_cmp[3])+cmp[4]] ;
}
function _proxy_jslib_pack_flags(flags) {
    var pflags= new Array() ;
    for (var i= 0 ; i<6 ; i++) { pflags[i]= (flags[i]==1) ? '1' : '0' }
    pflags[6]= String.fromCharCode(_proxy_jslib_MIME_TYPE_ID[flags[6]]+65) ;  // only works through #26
    return pflags.join('') ;
}
function _proxy_jslib_unpack_flags(flagst) {
    var flags= flagst.split('') ;
    for (var i= 0 ; i<6 ; i++) { flags[i]= (flags[i]=='1') ? 1 : 0 }
    flags[6]= flags[6].charCodeAt(0)-65 ;     // only works through #26
    flags[6]= _proxy_jslib_ALL_TYPES[flags[6]] ;
    return flags ;
}
function _proxy_jslib_html_escape(s) {
    if (s==void 0) return '' ;
    s= s.replace(/\&/g, '&amp;') ;
    s= s.replace(/([^\x00-\x7f])/g,
		 function (a) {
		     return '&#' + a.charCodeAt(0) + ';' ;
		 } ) ;
    return s.replace(/\"/g, '&quot;')
	    .replace(/\</g, '&lt;')
	    .replace(/\>/g, '&gt;') ;
}
function _proxy_jslib_html_unescape(s) {
    if (s==void 0) return '' ;
    s= s.replace(/\&\#(x)?(\w+);?/g,
		 function (a, p1, p2) { return p1
		     ? String.fromCharCode(eval('0x'+p2))
		     : String.fromCharCode(p2)
		 } ) ;
    return s.replace(/\&quot\b\;?/g, '"')
	    .replace(/\&lt\b\;?/g,   '<')
	    .replace(/\&gt\b\;?/g,   '>')
	    .replace(/\&amp\b\;?/g,  '&') ;
}
function _proxy_jslib_global_replace(s, pattern, replace_function) {
    if (s==null) return s ;
    var out= '' ;
    var m1 ;
    while ((m1=s.match(pattern))!=null) {
	out+= s.substr(0,m1.index) + replace_function(m1) ;
	s= s.substr(m1.index+m1[0].length) ;
    }
    return out+s ;
}
EOF
    } # end setting of $JSLIB_BODY
    print "$HTTP_1_X 200 OK\015\012",
	  "Expires: $expires_header\015\012",
	  "Date: $date_header\015\012",
	  "Content-Type: application/x-javascript\015\012",
	  "Content-Length: ", length($JSLIB_BODY), "\015\012",
	  "\015\012",
	  $JSLIB_BODY ;
    goto EXIT ;
}
sub proxify_swf {
    my($in)= @_ ;
    my(@out, $tag, $tags) ;
    my($DONT_COMPRESS)= 0 ;   # set to 1 for testing
    substr($in, 3, 1)= "\x08"  if substr($in, 3, 1) eq "\x07" ;
    my($swf_version, $swf_header_start, $swf_header_end, $rest)=
	&get_swf_header_and_tags($in) ;
    $tags= &proxify_swf_taglist(\$rest, $swf_version) ;
    substr($swf_header_start, 4)=
	pack('V', length($swf_header_start)+length($swf_header_end)+length($tags)) ;
    substr($swf_header_start, 0, 1)= 'F'  if $DONT_COMPRESS ;
    if (substr($swf_header_start, 0, 1) eq 'C') {
	$rest= $swf_header_end . $tags ;
	$rest= Compress::Zlib::compress($rest) ;
	return $swf_header_start . $rest ;
    }
    return $swf_header_start . $swf_header_end . $tags ;
}
sub proxify_swf_taglist {
    my($in, $swf_version)= @_ ;
    my (@out, $tag) ;
    while ($$in=~ /\G(..)/gcs) {
	my($tag_code_and_length_code)= $1 ;
	my($tag_code_and_length_int)= unpack('v', $tag_code_and_length_code) ;
	my($tag_code)= $tag_code_and_length_int >> 6 ;
	my($tag_length)= $tag_code_and_length_int & 0x3f ;
	if ($tag_length==0x3f) {
	    $$in=~ /\G(....)/gcs ;
	    $tag_length= $1 ;
	    $tag_code_and_length_code.= $tag_length ;
	    $tag_length= unpack('V', $tag_length) ;
	}
	if ($tag_code==57 or $tag_code==71) {
	    $$in=~ /\G(.*?)\0/gcs ;
	    my($swf_URL)= $1 ;
	    my($rest_len)= $tag_length - length($swf_URL) - 1 ;
	    my($tag_rest)= substr($$in, pos($$in), $rest_len) ;
	    pos($$in)+= $rest_len ;
	    $tag= &pack_swf_tag($tag_code, &full_url($swf_URL)."\0".$tag_rest) ;
	} elsif ($tag_code==12) {
	    $tag= &pack_swf_tag(12, &proxify_swf_action_list($in, $tag_length)) ;
	} elsif ($tag_code==59) {
	    my($sprite_id)= substr($$in, pos($$in), 2) ;
	    pos($$in)+= 2 ;
	    $tag= &pack_swf_tag(59, $sprite_id.&proxify_swf_action_list($in, $tag_length-2)) ;
	} elsif ($tag_code==39) {
	    $$in=~ /\G(....)/gcs ;
	    my($tag_start)= $1 ;
	    my($rest_len)= $tag_length-4 ;
	    my($taglist)= substr($$in, pos($$in), $rest_len) ;
	    pos($$in)+= $rest_len ;
	    my($tag_content)= &proxify_swf_taglist(\$taglist, $swf_version) ;
	    $tag= &pack_swf_tag(39, $tag_start . $tag_content) ;
	} elsif ($tag_code==26) {
	    $$in=~ /\G(.)../gcs ;
	    my($flags)= ord($1) ;
	    if (!($flags & 0x80)) {
		my($tag_content)= substr($$in, pos($$in)-3, $tag_length) ;
		pos($$in)+= $tag_length-3 ;
		$tag= &pack_swf_tag(26, $tag_content) ;
	    } else {
		my(@out) ;   # local copy
		push(@out, substr($$in, pos($$in)-3, 3)) ;
		$$in=~ /\G(..)/gcs, push(@out, $1)    if ($flags & 2) ;
		push(@out, &get_matrix($in))          if ($flags & 4) ;
		push(@out, &get_cxformwithalpha($in)) if ($flags & 8) ;
		$$in=~ /\G(..)/gcs, push(@out, $1)    if ($flags & 16) ;
		$$in=~ /\G(.*?\0)/gcs, push(@out, $1) if ($flags & 32) ;
		$$in=~ /\G(..)/gcs, push(@out, $1)    if ($flags & 64) ;
		push(@out, &get_clip_actions($in, $swf_version)) ;
		$tag= &pack_swf_tag(26, join('', @out)) ;
	    }
	} elsif ($tag_code==70) {
	    $$in=~ /\G(..)(..)/gcs ;
	    my($flags, $depth)= (unpack('S', $1), $2) ;
	    if (!($flags & 0x8000)) {
		my($tag_content)= substr($$in, pos($$in)-4, $tag_length) ;
		pos($$in)+= $tag_length-4 ;
		$tag= &pack_swf_tag(70, $tag_content) ;
	    } else {
		my(@out) ;   # local copy
		push(@out, substr($$in, pos($$in)-4, 4)) ;
		$$in=~ /\G(.*?\0)/gcs, push(@out, $1)
		    if ($flags & 8) or (($flags & 16) and ($flags & 0x200)) ;
		$$in=~ /\G(..)/gcs, push(@out, $1)    if ($flags & 0x200) ;
		push(@out, &get_matrix($in))          if ($flags & 0x400) ;
		push(@out, &get_cxformwithalpha($in)) if ($flags & 0x800) ;
		$$in=~ /\G(..)/gcs, push(@out, $1)    if ($flags & 0x1000) ;
		$$in=~ /\G(.*?\0)/gcs, push(@out, $1) if ($flags & 0x2000) ;
		$$in=~ /\G(..)/gcs, push(@out, $1)    if ($flags & 0x4000) ;
		push(@out, &get_filterlist($in))      if ($flags & 1) ;
		$$in=~ /\G(.)/gcs, push(@out, $1)     if ($flags & 2) ;
		push(@out, &get_clip_actions($in,  $swf_version)) ;
		$tag= &pack_swf_tag(70, join('', @out)) ;
	    }
	} elsif ($tag_code==7) {
	    $$in=~ /\G(..)/gcs ;
	    my($tag_start)= $1 ;
	    my($buttonrecords)= &get_button_records($in) ;
	    my($actions)= &proxify_swf_action_list($in, $tag_length-length($buttonrecords)-3) ;
	    $tag= &pack_swf_tag(7, $tag_start.$buttonrecords.$actions) ;
	} elsif ($tag_code==34) {
	    my(@out) ;
	    $$in=~ /\G(...)(..)/gcs ;
	    my($tag_start, $action_offset)= ($1, unpack('v', $2)) ;
	    push(@out, $1, $2) ;
	    if ($action_offset) {
		push(@out, substr($$in, pos($$in), $action_offset-2)) ;
		pos($$in)+= $action_offset-2 ;
		push(@out, &get_buttoncondactions($in)) ;
	    } else {
		push(@out, substr($$in, pos($$in), $tag_length-5)) ;
		pos($$in)+= $tag_length-5 ;
	    }
	    $tag= &pack_swf_tag(34, join('', @out)) ;
	} elsif ($tag_code==82) {
	    die "DoABC tag not supported yet" ;
	} else {
	    $tag= $tag_code_and_length_code . substr($$in, pos($$in), $tag_length) ;
	    pos($$in)+= $tag_length ;
	}
	push(@out, $tag) ;
	last if $tag_code==0 ;
    }
    return join('', @out) ;
}
sub pack_swf_tag {
    my($tag_code, $tag_content)= @_ ;
    my($len)= length($tag_content) ;
    if ($len<=62) {
	return pack('v', ($tag_code<<6) + $len) . $tag_content ;
    } else {
	return pack('vV', ($tag_code<<6) + 0x3f, $len) . $tag_content ;
    }
}
sub get_button_records {
    my($in, $expected_len, $in_define_button2)= @_ ;
    my($end_pos)= pos($$in)+$expected_len-1 ;
    my(@out) ;
    while (defined($expected_len) ? (pos($$in)<$end_pos)  : 1) {
	$$in=~ /\G(.)/gcs ;
	my($flags, $tag_start)= (ord($1), $1) ;
	pos($$in)--, last  if !defined($expected_len) and $flags==0 ;
	$$in=~ /\G(....)/gcs ;
	$tag_start.= $1 ;
	push(@out, $tag_start) ;
	push(@out, &get_matrix($in)) ;
	push(@out, &get_cxformwithalpha($in)) if $in_define_button2 ;
	push(@out, &get_filterlist($in))      if $in_define_button2 && ($flags & 16) ;
	$$in=~ /\G(.)/gcs, push(@out, $1)     if $in_define_button2 && ($flags & 32) ;
    }
    $$in=~ /\G\0/gcs or die "ERROR: missing end of button records" ;
    return join('', @out)."\0" ;
}
sub get_buttoncondactions {
    my($in)= @_ ;
    my(@out) ;
    while ($$in=~ /\G(..)/gcs) {
	my($action_size)= unpack('v', $1) ;
	$$in=~ /\G(..)/gcs ;
	my($flags)= $1 ;
	my($actions)= &proxify_swf_action_list($in, ($action_size>0)  ? $action_size-4  : undef) ;
	$action_size= 4+length($actions) if $action_size>0 ;
	push(@out, pack('v', $action_size), $flags, $actions) ;
	last if $action_size==0 ;
    }
    return join('', @out) ;
}
sub get_filterlist {
    my($in)= @_ ;
    my(@out) ;
    $$in=~ /\G(.)/gcs ;
    my($num_filters)= ord($1) ;
    for (1..$num_filters) {
	push(@out, &get_filter($in)) ;
    }
    return chr($num_filters).join('', @out) ;
}
sub get_filter {
    my($in)= @_ ;
    my($ret, $size) ;
    $$in=~ /\G(.)/gcs ;
    $ret= $1 ;
    my($filter_id)= $1 ;
    if ($filter_id==0) {            # DropShadowFilter
	$size= 23 ;
    } elsif ($filter_id==1) {     # BlurFilter
	$size= 9 ;
    } elsif ($filter_id==2) {     # GlowFilter
	$size= 15 ;
    } elsif ($filter_id==3) {     # BevelFilter
	$size= 27 ;
    } elsif ($filter_id==4) {     # GradientGlowFilter
	$$in=~ /\G(.)/gcs ;
	$ret.= $1 ;
	my($num_colors)= ord($1) ;
	$size= $num_colors*5 + 19 ;
    } elsif ($filter_id==5) {     # ConvolutionFilter
	$$in=~ /\G(.)(.)/gcs ;
	$ret.= $1.$2 ;
	my($matrixx, $matrixy)= (ord($1), ord($2)) ;
	$size= $matrixx*$matrixy*4 + 15 ;
    } elsif ($filter_id==6) {     # ColorMatrixFilter
	$size= 80 ;
    } elsif ($filter_id==7) {     # GradientBevelFilter
	$$in=~ /\G(.)/gcs ;
	$ret.= $1 ;
	my($num_colors)= ord($1) ;
	$size= $num_colors*5 + 19 ;
    } else {
	die "ERROR: unsupported filter type $filter_id\n" ;
    }
    $ret.= substr($$in, pos($$in), $size) ;
    pos($$in)+= $size ;
    return $ret ;
}
sub get_cxformwithalpha {
    my($in)= @_ ;
    my($byte1)= ord(substr($$in, pos($$in), 1)) ;
    my($has_adds)= !!($byte1 & 128) ;
    my($has_mults)= !!($byte1 & 64) ;
    my($nbits)= ($byte1>>2) & 0x0f ;
    my($record_size)= (6 + $has_adds*4*$nbits + $has_mults*4*$nbits +7)>>3 ;
    my($ret)= substr($$in, pos($$in), $record_size) ;
    pos($$in)+= $record_size ;
    return $ret ;
}
sub get_matrix {
    my($in)= @_ ;
    $$in=~ /\G(.)/gcs ;
    my($in_bitbuf)= $1 ;
    my($bitpos) ;     # first byte is 0-7
    if (vec($in_bitbuf, v(0), 1)) {    # HasScale field
	$bitpos= 1 ;
	my($nbits)= ord($in_bitbuf & "\x7f")>>2 ;
	my($nbytes)= (($nbits*2)-2+7)>>3 ;
	$in_bitbuf.= substr($$in, pos($$in), $nbytes) ;
	pos($$in)+= $nbytes ;
	$bitpos+= 5+$nbits*2 ;
    } else {
	$bitpos= 1 ;
    }
    if (vec($in_bitbuf, v($bitpos), 1)) {   # HasRotate field
	$bitpos++ ;
	if ($bitpos+5>8*length($in_bitbuf)) {
	    $in_bitbuf.= substr($$in, pos($$in), 1) ;
	    pos($$in)++ ;
	}
	my($nbits)= vec($in_bitbuf, v($bitpos), 1)  *16
		  + vec($in_bitbuf, v($bitpos+1), 1)*8
		  + vec($in_bitbuf, v($bitpos+2), 1)*4
		  + vec($in_bitbuf, v($bitpos+3), 1)*2
		  + vec($in_bitbuf, v($bitpos+4), 1)*1 ;
	$bitpos+= 5 ;
	my($nbytes)= ($nbits*2-(length($in_bitbuf)*8-$bitpos)+7)>>3 ;
	$in_bitbuf.= substr($$in, pos($$in), $nbytes) ;
	pos($$in)+= $nbytes ;
	$bitpos+= $nbits*2 ;
    } else {
	$bitpos++ ;
    }
    if ($bitpos+5>8*length($in_bitbuf)) {
	$in_bitbuf.= substr($$in, pos($$in), 1) ;
	pos($$in)++ ;
    }
    my($nbits)= vec($in_bitbuf, v($bitpos), 1)  *16
	      + vec($in_bitbuf, v($bitpos+1), 1)*8
	      + vec($in_bitbuf, v($bitpos+2), 1)*4
	      + vec($in_bitbuf, v($bitpos+3), 1)*2
	      + vec($in_bitbuf, v($bitpos+4), 1)*1 ;
    $bitpos+= 5 ;
    my($nbytes)= ($nbits*2-(length($in_bitbuf)*8-$bitpos)+7)>>3 ;
    $in_bitbuf.= substr($$in, pos($$in), $nbytes) ;
    pos($$in)+= $nbytes ;
    return $in_bitbuf ;
    sub v {
	my($vec)= @_ ;
	return (($vec>>3)<<3) + 7-($vec & 7) ;
    }
}
sub get_swf_header_and_tags {
    my($in)= @_ ;
    my($header_start)= substr($in, 0, 8) ;
    my($rest)= substr($in, 8) ;
    my($sig_byte, $swf_version, $swf_length)=
	$header_start=~ /^([CF])WS(.)(....)$/s ;
    return undef unless $sig_byte ;
    $swf_version= ord($swf_version) ;
    $swf_length= unpack('V', $swf_length) ;
    if ($sig_byte eq 'C') {
	eval { require Compress::Zlib } ;
	die "gzip not supported: $@" if $@ ;
	$rest= Compress::Zlib::uncompress($rest) ;
    }
    return undef unless $swf_length==(length($rest)+8) ;
    my($nbits)= ord($rest)>>3 ;
    my($totalbits)= (5+$nbits*4) ;
    my($nbytes)= ($totalbits + 7)>>3 ;
    my($header_end)= substr($rest, 0, $nbytes+4) ;
    substr($rest, 0, $nbytes+4)= '' ;
    return ($swf_version, $header_start, $header_end, $rest) ;
}
sub get_clip_actions {
    my($in, $swf_version)= @_ ;
    my($eventflags_re)= ($swf_version<=5)  ? (qr/\G(..)/s)  : (qr/\G(....)/s) ;
    my(@out) ;
    $$in=~ /\G\0\0/gc  or die "ERROR: didn't get clipaction header\n" ;
    $$in=~ /$eventflags_re/gc ;         # AllEventFlags field
    push(@out, "\0\0", $1) ;
    while ($$in=~ /$eventflags_re/gc) {
	my($event_flags)= $1 ;      # EventFlags field
	push(@out, $event_flags) ;
	last if $event_flags eq "\0\0" or $event_flags eq "\0\0\0\0" ;
	$$in=~ /\G(....)/gcs ;
	my($action_record_size)= unpack('V', $1) ;
	my($key_code) ;
	if ($swf_version>=6 and ord(substr($event_flags, 2, 1)) & 2) {
	    $$in=~ /\G(.)/gcs ;
	    $key_code= $1 ;
	}
	my($actions)= &proxify_swf_action_list($in, $action_record_size) ;
	$action_record_size= pack('V', length($key_code)+length($actions)) ;
	push(@out, $action_record_size, $key_code, $actions) ;
    }
    return join('', @out) ;
}
sub proxify_swf_action_list {
    my($in, $action_record_size)= @_ ;
    my(@out, $out_bytes, $out, $action, $needs_swflib, @jumps, @insertions, @code_blocks) ;
    my($insert_proxify_top_url)= "\x96\$\0\cG\0\0\0\0\0_proxy_swflib_proxify_top_url\0=" ;
    my($insert_proxify_top_url_len)= length($insert_proxify_top_url) ;
    my($insert_proxify_2nd_url)= "\x96\$\0\cG\0\0\0\0\0_proxy_swflib_proxify_2nd_url\0=" ;
    my($insert_proxify_2nd_url_len)= length($insert_proxify_2nd_url) ;
    my($insert_pre_method)= "\x96\x1f\0\cG\0\0\0\0\0_proxy_swflib_pre_method\0=" ;
    my($insert_pre_method_len)= length($insert_pre_method) ;
    my($insert_pre_function)= "\x96!\0\cG\0\0\0\0\0_proxy_swflib_pre_function\0=" ;
    my($insert_pre_function_len)= length($insert_pre_function) ;
    my($start_pos)= pos($$in) ;
    while ($$in=~ /\G(.)/gcs) {
	my($action_code)= ord($1) ;
	last if $action_code==0 ;
	my($action_length, $action_content) ;
	if ($action_code>=0x80) {
	    $$in=~ /\G(..)/gcs ;
	    $action_length= unpack('v', $1) ;
	    $action_content=
		substr($$in, pos($$in), $action_length) ;
	    pos($$in)+= $action_length ;
	}
	if ($action_code==0x83) {
	    $action_content=~ /\G(.*?)(\0.*)$/gcs ;
	    my($action_URL, $action_rest)= ($1, $2) ;
	    if ($action_URL!~ /^\s*(?:javascript|livescript)\b/i) {
		my($old_len)= length($action_content) ;
		$action_content= &full_url($action_URL) . $action_rest ;
		my($size_diff)= length($action_content) - $old_len ;
		&update_previous_jumps(\@jumps, $out_bytes, $size_diff) ;
		&update_previous_code_blocks(\@code_blocks, $out_bytes, $size_diff) ;
		push(@insertions, { 'location' => $out_bytes , 'size' => $size_diff } ) ;
	    }
	    $action= "\x83" . pack('v', length($action_content)) . $action_content ;
	} elsif ($action_code==0x9a) {
	    $needs_swflib= 1 ;
	    &update_previous_jumps(\@jumps, $out_bytes, $insert_proxify_2nd_url_len) ;
	    &update_previous_code_blocks(\@code_blocks, $out_bytes, $insert_proxify_2nd_url_len) ;
	    push(@insertions, { 'location' => $out_bytes,
				'size' => $insert_proxify_2nd_url_len } ) ;
	    push(@out, $insert_proxify_2nd_url) ;
	    $out_bytes+= $insert_proxify_2nd_url_len ;
	    $action= "\x9a" . pack('v', length($action_content)) . $action_content ;
	} elsif ($action_code==0x52) {
	    $needs_swflib= 1 ;
	    &update_previous_jumps(\@jumps, $out_bytes, $insert_pre_method_len) ;
	    &update_previous_code_blocks(\@code_blocks, $out_bytes, $insert_pre_method_len) ;
	    push(@insertions, { 'location' => $out_bytes,
				'size' => $insert_pre_method_len } ) ;
	    push(@out, $insert_pre_method) ;
	    $out_bytes+= $insert_pre_method_len ;
	    $action= "\x52" ;
	} elsif ($action_code==0x3d) {
	    $needs_swflib= 1 ;
	    &update_previous_jumps(\@jumps, $out_bytes, $insert_pre_function_len) ;
	    &update_previous_code_blocks(\@code_blocks, $out_bytes, $insert_pre_function_len) ;
	    push(@insertions, { 'location' => $out_bytes,
				'size' => $insert_pre_function_len } ) ;
	    push(@out, $insert_pre_function) ;
	    $out_bytes+= $insert_pre_function_len ;
	    $action= "\x3d" ;
	} elsif ($action_code==0x99 or $action_code==0x9d) {
	    $action= chr($action_code) . "\x02\0\0\0" ;
	    my($offset)= unpack('s', pack('S', unpack('v', $action_content))) ;
	    my($jump)= { 'location' => $out_bytes,
			 'target' => $out_bytes+$offset+5 } ;
	    &handle_previous_insertions(\@insertions, $jump) ;
	    push(@jumps, $jump) ;
	} elsif ($action_code==0x9b or $action_code==0x8e) {
	    my($codesize_loc)= $out_bytes+3+$action_length-2 ;
	    my($codesize)= unpack('v', substr($action_content, -2)) ;
	    push(@code_blocks, { 'code_start' => $out_bytes+3+$action_length,
				 'codesize_loc' => $codesize_loc,
				 'codesize' => $codesize } ) ;
	    $action= chr($action_code) . pack('v', length($action_content)) . $action_content ;
	} elsif ($action_code==0x8f) {
	    $action_content=~ /\G(.)(......)/gcs ;
	    my($flags)= ord($1) ;
	    my($try_size, $catch_size, $finally_size)= unpack('vvv', $2) ;
	    my($catch_nr) ;
	    if ($flags & 4) {
		$action_content=~ /\G(.)/gcs ;
		$catch_nr= $1 ;
	    } else {
		$action_content=~ /\G(.*?\0)/gcs ;
		$catch_nr= $1 ;
	    }
	    push(@code_blocks, { 'code_start' => $out_bytes + $action_length,
				 'codesize_loc' => $out_bytes+4,
				 'codesize' => $try_size } ) ;
	    push(@code_blocks, { 'code_start' => $out_bytes + $action_length
						 + $try_size,
				 'codesize_loc' => $out_bytes+6,
				 'codesize' => $catch_size } ) ;
	    push(@code_blocks, { 'code_start' => $out_bytes + $action_length
						 + $try_size + $catch_size,
				 'codesize_loc' => $out_bytes+8,
				 'codesize' => $finally_size } ) ;
	    $action= "\x8f" . pack('v', length($action_content)) . $action_content ;
	} elsif ($action_code==0x94) {
	    my($codesize_loc)= $out_bytes+3 ;
	    my($codesize)= unpack('v', $action_content) ;
	    push(@code_blocks, { 'code_start' => $out_bytes+5,
				 'codesize_loc' => $codesize_loc,
				 'codesize' => $codesize } ) ;
	    $action= "\x94\x02\0" . $action_content ;
	} else {
	    $action= chr($action_code)
		   . (($action_code>=0x80)
		      ? (pack('v', length($action_content)) . $action_content)
		      : '') ;
	}
	push(@out, $action) ;
	$out_bytes+= length($action) ;
    }
    $out= join('', @out) ;
    die "ERROR: out_bytes not set correctly\n" if $out_bytes!=length($out) ; 
    die "ERROR: read wrong number of bytes (expected $action_record_size, got ".(pos($$in)-$start_pos).")\n"
	if defined($action_record_size) and pos($$in)-$start_pos!=$action_record_size ;
    &rewrite_jumps(\$out, \@jumps) ;
    &rewrite_codesizes(\$out, \@code_blocks) ;
    if ($needs_swflib) {
	$swflib||= &return_swflib() ;
	$out= $swflib . $out ;
    }
    return $out."\0" ;
    sub update_previous_jumps {
	my($jumps, $insert_pos, $offset)= @_ ;
	foreach (@$jumps) {
	    $_->{target}+= $offset  if $_->{target} > $insert_pos ;
	}
    }
    sub update_previous_code_blocks {
	my($code_blocks, $insert_pos, $offset)= @_ ;
	foreach (@$code_blocks) {
	    $_->{codesize}+= $offset
		if     ($_->{code_start} <= $insert_pos)
		    && ($_->{code_start}+$_->{codesize} > $insert_pos) ;
	}
    }
    sub handle_previous_insertions {
	my($insertions, $jump)= @_ ;
	foreach (reverse @$insertions) {
	    $jump->{target}-= $_->{size}
		if $_->{location}+$_->{size} >= $jump->{target} ;
	}
    }
    sub rewrite_jumps {
	my($out, $jumps)= @_ ;
	foreach (@$jumps) {
	    die "ERROR: jump is not a jump\n"
		if substr($$out, $_->{location}, 1)!~ /^[\x99\x9d]/ ;
	    substr($$out, $_->{location}+3, 2)=
		pack('v', unpack('S', pack('s', $_->{target} - $_->{location} - 5))) ;  # jump actions are 5 bytes
	}
    }
    sub rewrite_codesizes {
	my($out, $code_blocks)= @_ ;
	foreach (@$code_blocks) {
	    substr($$out, $_->{codesize_loc}, 2)= pack('v', $_->{codesize}) ;
	}
    }
}
sub return_swflib {
    my($safe_URL)= &wrap_proxy_encode($URL) ;
    my($full_url_start)= $url_start . &wrap_proxy_encode('x-proxy://flash/fullurl') . "?base=$safe_URL&URI=" ;
    my($url_len_code)= pack('v', length($full_url_start)+2) ;
    return "\x8e\x1f\0_proxy_swflib_alert_top\0\0\0\0*\x002\0L\x96\cY\0\0javascript:alert(\"top=[\0MG\x96\cE\0\0]\")\0G\x96\cB\0\0\0\x9a\cA\0\0>\x8e\$\0_proxy_swflib_set_classlists\0\0\0\0*\0\x7f\cB\x96\cF\0\0load\0\x96 \0\0XML\0\0LoadVars\0\0StyleSheet\0\cG\cC\0\0\0B\x96\cJ\0\0download\0\x96\cT\0\0FileReference\0\cG\cA\0\0\0B\x96\cH\0\0upload\0\x96\cT\0\0FileReference\0\cG\cA\0\0\0B\x96\cF\0\0send\0\x96\cT\0\0XML\0\0LoadVars\0\cG\cB\0\0\0B\x96\cM\0\0sendAndLoad\0\x96\cT\0\0XML\0\0LoadVars\0\cG\cB\0\0\0B\x96\cH\0\0getURL\0\x96\cP\0\0MovieClip\0\cG\cA\0\0\0B\x96\cK\0\0loadMovie\0\x96\cP\0\0MovieClip\0\cG\cA\0\0\0B\x96\cO\0\0loadVariables\0\x96\cP\0\0MovieClip\0\cG\cA\0\0\0B\x96\cJ\0\0loadClip\0\x96\cV\0\0MovieClipLoader\0\cG\cA\0\0\0B\x96\cI\0\0connect\0\x96\cT\0\0NetConnection\0\cG\cA\0\0\0B\x96\cF\0\0play\0\x96\cP\0\0NetStream\0\cG\cA\0\0\0B\x96\cP\0\0loadPolicyFile\0\x96\cO\0\0Security\0\cG\cA\0\0\0B\x96\cK\0\0loadSound\0\x96\cL\0\0Sound\0\cG\cA\0\0\0B\x96\cE\0\cG\cM\0\0\0C\x96\cZ\0\0_proxy_swflib_classlists\0M\x1d\x96\cJ\0\0getURL\0\cE\cA\x96\cM\0\0loadMovie\0\cE\cA\x96\cP\0\0loadMovieNum\0\cE\cA\x96\cQ\0\0loadVariables\0\cE\cA\x96\cT\0\0loadVariablesNum\0\cE\cA\x96\cE\0\cG\cE\0\0\0C\x96\x1c\0\0_proxy_swflib_functionlist\0M\x1d>\x96#\0\cG\0\0\0\0\0_proxy_swflib_set_classlists\0=\x8e \0_proxy_swflib_pre_method\0\0\0\0*\0\cO\cB\x96\x1b\0\0_proxy_swflib_method_name\0M\x1d\x96\cV\0\0_proxy_swflib_object\0M\x1d\x96\cZ\0\0_proxy_swflib_num_params\0M\x1d\x96\cZ\0\0_proxy_swflib_classlists\0\x1c\x96\x1b\0\0_proxy_swflib_method_name\0\x1cNL\x96\cA\0\cCI\x9d\cB\0\cS\cALD\x96\cJ\0\0function\0I\x9d\cB\0\xfe\0L\x96\cY\0\0_proxy_swflib_classlist\0M\x1d\x96\cH\0\0length\0NQ\x96\cQ\0\0_proxy_swflib_j\0M\x1d\x96\cV\0\0_proxy_swflib_object\0\x1c\x96\cY\0\0_proxy_swflib_classlist\0\x1c\x96\cQ\0\0_proxy_swflib_j\0\x1cN\x1cT\cR\x9d\cB\x000\0\x96\cE\0\cG\0\0\0\0\x96\x1f\0\0_proxy_swflib_proxify_top_url\0=\x99\cB\x008\0\x96\cQ\0\0_proxy_swflib_j\0L\x1cQ\x1d\x96\cQ\0\0_proxy_swflib_j\0\x1c\x9d\cB\0I\xff\x99\cB\0\cA\0\cW\x96\cZ\0\0_proxy_swflib_num_params\0\x1c\x96\cV\0\0_proxy_swflib_object\0\x1c\x96\x1b\0\0_proxy_swflib_method_name\0\x1c>\x8e\"\0_proxy_swflib_pre_function\0\0\0\0*\0\xf4\0\x96\x1d\0\0_proxy_swflib_function_name\0M\x1d\x96\cZ\0\0_proxy_swflib_num_params\0M\x1d\x96\x1c\0\0_proxy_swflib_functionlist\0\x1c\x96\x1d\0\0_proxy_swflib_function_name\0\x1cN\cR\x9d\cB\0+\0\x96\cE\0\cG\0\0\0\0\x96\x1f\0\0_proxy_swflib_proxify_top_url\0=\x96\cZ\0\0_proxy_swflib_num_params\0\x1c\x96\x1d\0\0_proxy_swflib_function_name\0\x1c>\x8e%\0_proxy_swflib_proxify_top_url\0\0\0\0*\0|\0L\x96\cA\0\cBI\x9d\cB\0p\0\x96\x1c\0\0_proxy_jslib_full_url\0\cG\cB\0\0\0\x96\cG\0\0flash\0\x1c\x96\cJ\0\0external\0N\x96\cS\0\0ExternalInterface\0N\x96\cF\0\0call\0RL\x96\cF\0\0null\0I\cR\x9d\cB\0\cF\0\cW\x96\cB\0\0\0>\x8e%\0_proxy_swflib_proxify_2nd_url\0\0\0\0*\0^\0\x96\cV\0\0_proxy_swflib_target\0M\x1d\x96\$\0\cG\0\0\0\0\0_proxy_swflib_proxify_top_url\0=\x96\cV\0\0_proxy_swflib_target\0\x1c>" ;
}
sub swf2perl {
    my($in)= @_ ;
    my($out) ;
    while ($in=~ /\G(.)/gcs) {
	my($chr)= $1 ;
	my($ord)= ord($chr) ;
	my($digit_follows) ;
	if ($in=~ /\G\d/gcs) {
	    $digit_follows= 1 ;
	    pos($in)-- ;
	}
	if ($ord==36 or $ord==64 or $ord==92 or $ord==34) {
	    $out.= "\\".chr($ord) ;
	} elsif ($ord>=32 and $ord<=126) {
	    $out.= chr($ord) ;
	} elsif ($ord>=1 and $ord<=26) {
	    $out.= "\\c".chr($ord+64) ;
	} elsif ($ord==0 and !$digit_follows) {
	    $out.= "\\0" ;
	} else {
	    $out.= "\\x".sprintf("%x", $ord) ;
	}
    }
    return $out ;
}
