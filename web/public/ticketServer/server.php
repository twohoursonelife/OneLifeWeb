<?php



global $ts_version;
$ts_version = "2";



// edit settings.php to change server' settings
include( "settings.php" );


if( $useBulkEmailerForNotes ) {    
    require( "$bulkEmailerPath" );
    }




// no end-user settings below this point


// for use in readable base-32 encoding
// elimates 0/O and 1/I
global $readableBase32DigitArray;
$readableBase32DigitArray =
    array( "2", "3", "4", "5", "6", "7", "8", "9",
           "A", "B", "C", "D", "E", "F", "G", "H", "J", "K", "L", "M",
           "N", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z" );



// no caching
//header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache'); 



// enable verbose error reporting to detect uninitialized variables
error_reporting( E_ALL );



// page layout for web-based setup
$setup_header = "
<HTML>
<HEAD><TITLE>Ticket Permissions Server Web-based setup</TITLE></HEAD>
<BODY BGCOLOR=#FFFFFF TEXT=#000000 LINK=#0000FF VLINK=#FF0000>

<CENTER>
<TABLE WIDTH=75% BORDER=0 CELLSPACING=0 CELLPADDING=1>
<TR><TD BGCOLOR=#000000>
<TABLE WIDTH=100% BORDER=0 CELLSPACING=0 CELLPADDING=10>
<TR><TD BGCOLOR=#EEEEEE>";

$setup_footer = "
</TD></TR></TABLE>
</TD></TR></TABLE>
</CENTER>
</BODY></HTML>";






// ensure that magic quotes are OFF
// we hand-filter all _REQUEST data with regexs before submitting it to the DB
//if( get_magic_quotes_gpc() ) {
//    // force magic quotes to be removed
//    $_GET     = array_map( 'ts_stripslashes_deep', $_GET );
//    $_POST    = array_map( 'ts_stripslashes_deep', $_POST );
//    $_REQUEST = array_map( 'ts_stripslashes_deep', $_REQUEST );
//    $_COOKIE  = array_map( 'ts_stripslashes_deep', $_COOKIE );
//    }
    


// Check that the referrer header is this page, or kill the connection.
// Used to block XSRF attacks on state-changing functions.
// (To prevent it from being dangerous to surf other sites while you are
// logged in as admin.)
// Thanks Chris Cowan.
function ts_checkReferrer() {
    global $fullServerURL;
    
    if( !isset($_SERVER['HTTP_REFERER']) ||
        strpos($_SERVER['HTTP_REFERER'], $fullServerURL) !== 0 ) {
        
        die( "Bad referrer header" );
        }
    }




// all calls need to connect to DB, so do it once here
ts_connectToDatabase();

// close connection down below (before function declarations)


// testing:
//sleep( 5 );


// general processing whenver server.php is accessed directly




// grab POST/GET variables
$action = ts_requestFilter( "action", "/[A-Z_]+/i" );

$debug = ts_requestFilter( "debug", "/[01]/" );

$remoteIP = "";
if( isset( $_SERVER[ "REMOTE_ADDR" ] ) ) {
    $remoteIP = $_SERVER[ "REMOTE_ADDR" ];
    }




if( $action == "version" ) {
    global $ts_version;
    echo "$ts_version";
    }
else if( $action == "show_log" ) {
    ts_showLog();
    }
else if( $action == "clear_log" ) {
    ts_clearLog();
    }
else if( $action == "sell_ticket" ) {
    ts_sellTicket();
    }
else if( $action == "block_login_key" ) {
    ts_blockTicketID();
    }
else if( $action == "delete_login_key" ) {
    ts_deleteTicketID();
    }
else if( $action == "check_ticket" ) {
    ts_checkTicket();
    }
else if( $action == "get_ticket_email" ) {
    ts_getTicketEmail();
    }
else if( $action == "get_login_key" ) {
    ts_getTicketID();
    }
else if( $action == "check_ticket_hash" ) {
    ts_checkTicketHash();
    }
else if( $action == "show_data" ) {
    ts_showData();
    }
else if( $action == "show_detail" ) {
    ts_showDetail();
    }
else if( $action == "edit_ticket" ) {
    ts_editTicket();
    }
else if( $action == "edit_email" ) {
    ts_editEmail();
    }
else if( $action == "logout" ) {
    ts_logout();
    }
else if( $action == "ts_setup" ) {
    global $setup_header, $setup_footer;
    echo $setup_header; 

    echo "<H2>Ticket Server Web-based Setup</H2>";

    echo "Creating tables:<BR>";

    echo "<CENTER><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=1>
          <TR><TD BGCOLOR=#000000>
          <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5>
          <TR><TD BGCOLOR=#FFFFFF>";

    ts_setupDatabase();

    echo "</TD></TR></TABLE></TD></TR></TABLE></CENTER><BR><BR>";
    
    echo $setup_footer;
    }
else if( preg_match( "/server\.php/", $_SERVER[ "SCRIPT_NAME" ] ) ) {
    // server.php has been called without an action parameter

    // the preg_match ensures that server.php was called directly and
    // not just included by another script
    
    // quick (and incomplete) test to see if we should show instructions
    global $tableNamePrefix;
    
    // check if our tables exist
    $exists = ts_doesTableExist( $tableNamePrefix . "tickets" ) &&
        ts_doesTableExist( $tableNamePrefix . "log" );
    
        
    if( $exists  ) {
        echo "Ticket Server database setup and ready <a href=\"index.php\">Login Here</a>";
        }
    else {
        // start the setup procedure

        global $setup_header, $setup_footer;
        echo $setup_header; 

        echo "<H2>Ticket Server Web-based Setup</H2>";
    
        echo "Ticket Server will walk you through a " .
            "brief setup process.<BR><BR>";
        
        echo "Step 1: ".
            "<A HREF=\"server.php?action=ts_setup\">".
            "create the database tables</A>";

        echo $setup_footer;
        }
    }



// done processing
// only function declarations below

ts_closeDatabase();







/**
 * Creates the database tables needed by seedBlogs.
 */
function ts_setupDatabase() {
    global $tableNamePrefix;

    $tableName = $tableNamePrefix . "log";
    if( ! ts_doesTableExist( $tableName ) ) {

        // this table contains general info about the server
        // use INNODB engine so table can be locked
        $query =
            "CREATE TABLE $tableName(" .
			"log_id INT(10) NOT NULL PRIMARY KEY AUTO_INCREMENT," .
            "entry TEXT NOT NULL, ".
            "entry_time DATETIME NOT NULL );";

        $result = ts_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }

    
    
    $tableName = $tableNamePrefix . "tickets";
    if( ! ts_doesTableExist( $tableName ) ) {

        // this table contains general info about each ticket
        $query =
            "CREATE TABLE $tableName(" .
            "key_id INT(10) NOT NULL PRIMARY KEY AUTO_INCREMENT," .
            "login_key VARCHAR(255) NOT NULL UNIQUE," .
            "discord_id VARCHAR(255) NOT NULL UNIQUE," .
            "creation_date DATETIME NOT NULL," .
            "email CHAR(255) NOT NULL UNIQUE," .
            "blocked TINYINT NOT NULL," .
            "time_played INT(11) NOT NULL DEFAULT '0', " . 
            "last_activity TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP );";

        $result = ts_queryDatabase( $query );

        echo "<B>$tableName</B> table created<BR>";
        }
    else {
        echo "<B>$tableName</B> table already exists<BR>";
        }
    }



function ts_showLog() {
    ts_checkPassword( "show_log" );

     echo "[<a href=\"server.php?action=show_data" .
         "\">Main</a>]<br><br><br>";
    
    global $tableNamePrefix;

    $query = "SELECT * FROM $tableNamePrefix"."log ".
        "ORDER BY entry_time DESC;";
    $result = ts_queryDatabase( $query );

    $numRows = mysqli_num_rows( $result );



    echo "<a href=\"server.php?action=clear_log\">".
        "Clear log</a>";
        
    echo "<hr>";
        
    echo "$numRows log entries:<br><br><br>\n";
        

    for( $i=0; $i<$numRows; $i++ ) {
        $time = ts_mysqli_result( $result, $i, "entry_time" );
        $entry = htmlspecialchars( ts_mysqli_result( $result, $i, "entry" ) );

        echo "<b>$time</b>:<br>$entry<hr>\n";
        }
    }



function ts_clearLog() {
    ts_checkPassword( "clear_log" );

     echo "[<a href=\"server.php?action=show_data" .
         "\">Main</a>]<br><br><br>";
    
    global $tableNamePrefix;

    $query = "DELETE FROM $tableNamePrefix"."log;";
    $result = ts_queryDatabase( $query );
    
    if( $result ) {
        echo "Log cleared.";
        }
    else {
        echo "DELETE operation failed?";
        }
    }

function ticketGeneration(){
    global $tableNamePrefix, $fastspringPrivateKeys, $remoteIP;
    global $ticketIDLength, $ticketGenerationSecret, $ts_mysqlLink;
	$login_key = "";
		
		// repeat hashing new rand values, mixed with our secret
		// for security, until we have generated enough digits.
		while( strlen( $login_key ) < $ticketIDLength ) {
			
			$randVal = rand();
			
			$hash_bin =
				ts_hmac_sha1_raw( $ticketGenerationSecret, uniqid( "$randVal", true ) );
			
		
			$hash_base32 = ts_readableBase32Encode( $hash_bin );
			
			$digitsLeft = $ticketIDLength - strlen( $login_key );
			
			$login_key = $login_key . substr( $hash_base32,
											0, $digitsLeft );
			}
		
		
		// break into "-" separated chunks of 5 digits
		$login_key_chunks = str_split( $login_key, 5 );
		
		$login_key = implode( "-", $login_key_chunks );
		return $login_key;
}



function ts_sellTicket() {
    global $tableNamePrefix, $fastspringPrivateKeys, $remoteIP;
    global $ticketIDLength, $ticketGenerationSecret, $ts_mysqlLink;
    
    $failedCount = 0;
    $successCount = 0;


	$emailList = array();

    $email = ts_requestFilter( "email", "/[A-Z0-9._%+\-]+/i" );

    $emailList[] = $email;


    //foreach( $emailList as $email ) {
    //    
        $unfilteredEmail = $email;
        $email = ts_filter( $email, "/[A-Z0-9._%+\-]+/i", "" );
	
        if( $email == "" ) {
            echo "Invalid email address: $unfilteredEmail<br>";
            $failedCount++;
            }
        else {
            $nameFromEmail =
                ts_requestFilter( "name_from_email", "/[01]/", "0" );
            
            $name = "";
    
            if( ! $nameFromEmail ) {
                
                $name = ts_requestFilter( "name", "/[A-Z0-9.' -_]+/i" );
                
                // some names have ' in them
                // need to escape this for use in DB query
                $name = mysqli_real_escape_string( $ts_mysqlLink, $name );
                }
            else {
                $emailParts = preg_split( "/@/", $email );
                
                if( count( $emailParts ) == 2 ) {
                    $name = $emailParts[0];
                    }
                }
            
	
    
            $tries = 0;
            $success = 0;
            
            while( $tries < 3 && $success == 0 ) {
        
        
        
                $login_key = "";
                
                // repeat hashing new rand values, mixed with our secret
                // for security, until we have generated enough digits.
                while( strlen( $login_key ) < $ticketIDLength ) {
                    
                    $randVal = rand();
                    
                    $hash_bin =
                        ts_hmac_sha1_raw( $ticketGenerationSecret,
                                          uniqid( "$randVal",
                                                          true ) );
                    
            
                    $hash_base32 = ts_readableBase32Encode( $hash_bin );
                    
                    $digitsLeft = $ticketIDLength - strlen( $login_key );
                    
                    $login_key = $login_key . substr( $hash_base32,
                                                      0, $digitsLeft );
                    }
                
        
                // break into "-" separated chunks of 5 digits
                $login_key_chunks = str_split( $login_key, 5 );
                
                $login_key = implode( "-", $login_key_chunks );
                
                
                
                /*
                  "login_key VARCHAR(255) NOT NULL PRIMARY KEY," .
                  "creation_date DATETIME NOT NULL," .
                  "last_download_date DATETIME NOT NULL," .
                  "name TEXT NOT NULL, ".
                  "email CHAR(255) NOT NULL," .
                  "order_number CHAR(255) NOT NULL," .
                  "tag CHAR(255) NOT NULL," .
                  "coupon_code TEXT NOT NULL," .
                  "email_sent TINYINT NOT NULL," .
                  "blocked TINYINT NOT NULL," .
                  "download_count INT, ".
                  "email_opt_in TINYINT NOT NULL );";
                */
                
                $rand_discordid = mt_rand(000000,999999);

                // opt-in to emails by default
                $query = "INSERT INTO $tableNamePrefix". "tickets VALUES ( " .
                    "NULL,'$login_key', $rand_discordid, CURRENT_TIMESTAMP, '$email', '0', '0', CURRENT_TIMESTAMP );";
	
	
                $result = mysqli_query( $ts_mysqlLink, $query );
                
                if( $result ) {
                    $success = 1;
                    
                    ts_log( "Ticket $login_key created by $remoteIP" );
                    
	
                    echo "Successfully created user<br><br>\n";
					
                    $successCount++;
					ts_showData();
                }else {
                    global $debug;
                    if( $debug == 1 ) {
                        echo "Duplicate ids?  Error:  " .
                            mysqli_error( $ts_mysqlLink ) ."<br>";
                        }
                    // try again
					ts_log( "Unable to create user: $email with key $login_key" );
					$tries += 1;
					
					if($tries == 3){
						echo "Unable to create user, check email isn't in use";
						ts_showData();
					}
				}
			}
	
		}
	//}
    }





function ts_getTicketID() {
    global $tableNamePrefix, $sharedEncryptionSecret;

    $email = ts_requestFilter( "email", "/[A-Z0-9._%+\-]+/i" );

    $query = "SELECT login_key FROM $tableNamePrefix"."tickets ".
        "WHERE email = '$email' AND blocked = '0';";
    $result = ts_queryDatabase( $query );

    $numRows = mysqli_num_rows( $result );

    $login_key = "";
    
    // could be more than one with this email
    // return first only
    if( $numRows > 0 ) {
        $login_key = ts_mysqli_result( $result, 0, "login_key" );
        }
    else {
        echo "DENIED";
        return;
        }


    
    // remove hyphens
    $login_key = implode( preg_split( "/-/", $login_key ) );

    $login_key_bits = ts_readableBase32DecodeToBitString( $login_key );

    $ticketLengthBits = strlen( $login_key_bits );


    // generate enough bits by hashing shared secret repeatedly
    $hexToMixBits = "";

    $runningSecret = ts_hmac_sha1( $sharedEncryptionSecret, $email );
    while( strlen( $hexToMixBits ) < $ticketLengthBits ) {

        $newBits = ts_hexDecodeToBitString( $runningSecret );

        $hexToMixBits = $hexToMixBits . $newBits;

        $runningSecret = ts_hmac_sha1( $sharedEncryptionSecret,
                                       $runningSecret );
        }

    // trim down to bits that we need
    $hexToMixBits = substr( $hexToMixBits, 0, $ticketLengthBits );

    $mixBits = str_split( $hexToMixBits );
    $ticketBits = str_split( $login_key_bits );

    // bitwise xor
    $i = 0;
    foreach( $mixBits as $bit ) {
        if( $bit == "1" ) {
            if( $login_key_bits[$i] == "1" ) {
                
                $ticketBits[$i] = "0";
                }
            else {
                $ticketBits[$i] = "1";
                }
            }
        $i++;
        }

    $login_key_bits = implode( $ticketBits );

    $encrypted_login_key =
        ts_readableBase32EncodeFromBitString( $login_key_bits );

    echo "$encrypted_login_key";
    }



function ts_checkTicketHash() {
    global $tableNamePrefix;

    $email = ts_requestFilter( "email", "/[A-Z0-9._%+\-]+/i" );

    
    
    $query = "SELECT login_key FROM $tableNamePrefix"."tickets ".
        "WHERE email = '$email' AND blocked = '0';";
    $result = ts_queryDatabase( $query );

    $numRows = mysqli_num_rows( $result );

    $login_key = "";
    
    // could be more than one with this email
    // return first only
    if( $numRows > 0 ) {
        $login_key = ts_mysqli_result( $result, 0, "login_key" );
        }
    else {
        ts_log( "email $email not found on check_ticket_hash" );
        echo "INVALID";
        return;
        }

    // remove hyphens
    $login_key = implode( preg_split( "/-/", $login_key ) );
    

    $hash_value = ts_requestFilter( "hash_value", "/[A-F0-9]+/i", "" );

    $hash_value = strtoupper( $hash_value );
    
    $string_to_hash =
        ts_requestFilter( "string_to_hash", "/[A-Z0-9]+/i", "0" );


    $computedHashValue =
        strtoupper( ts_hmac_sha1( $login_key, $string_to_hash ) );
    

    if( $computedHashValue == $hash_value ) {
        echo "VALID";
        }
    else {
        ts_log( "hash for $email invalid on check_ticket_hash" );
        echo "INVALID";
        }
    }




function ts_editTicket() {

    ts_checkPassword( "edit_ticket" );
    global $tableNamePrefix, $remoteIP, $ts_mysqlLink;


    $login_key = ts_requestFilter( "login_key", "/[A-HJ-NP-Z2-9\-]+/i" );

    $login_key = strtoupper( $login_key );
    
    $email = ts_requestFilter( "email", "/[A-Z0-9._%+\-]+/i" );

    $new_key = ts_requestFilter( "key", "/[A-HJ-NP-Z2-9\-]+/i" );


    $query = "UPDATE $tableNamePrefix". "tickets SET " .
        "email = '$email', ".
        "login_key = '$new_key' " .
        "WHERE login_key = '$login_key';";
    
    global $ts_mysqlLink;
    
    $result = mysqli_query( $ts_mysqlLink, $query );

    if( $result ) {
        ts_log( "$login_key data changed by $remoteIP" );
        echo "Update of $login_key succeeded<br><br>";

        // don't check password again here
        ts_showDetail( false );
        }
    else {
        ts_log( "$login_key data change failed for $remoteIP" );

        echo "Update of $login_key failed";
        }
    }



function ts_blockTicketID() {
    ts_checkPassword( "block_login_key" );


    global $tableNamePrefix;

    $login_key = ts_requestFilter( "login_key", "/[A-HJ-NP-Z2-9\-]+/i" );

    $login_key = strtoupper( $login_key );
    

    $blocked = ts_requestFilter( "blocked", "/[01]/", "0" );
    
    
    global $remoteIP;

    

    
    $query = "SELECT * FROM $tableNamePrefix"."tickets ".
        "WHERE login_key = '$login_key';";
    $result = ts_queryDatabase( $query );

    $numRows = mysqli_num_rows( $result );

    if( $numRows == 1 ) {

        
        $query = "UPDATE $tableNamePrefix"."tickets SET " .
            "blocked = '$blocked' " .
            "WHERE login_key = '$login_key';";
        
        $result = ts_queryDatabase( $query );

        
        ts_log( "$login_key block changed to $blocked by $remoteIP" );

        ts_showData();
        }
    else {
        ts_log( "$login_key not found for $remoteIP" );

        echo "$login_key not found";
        }    
    }



function ts_deleteTicketID() {
    ts_checkPassword( "delete_login_key" );

    global $tableNamePrefix, $remoteIP;

    $login_key = ts_requestFilter( "login_key", "/[A-HJ-NP-Z2-9\-]+/i" );

    $login_key = strtoupper( $login_key );
    

    $query = "DELETE FROM $tableNamePrefix"."tickets ".
        "WHERE login_key = '$login_key';";
    $result = ts_queryDatabase( $query );
    
    if( $result ) {
        ts_log( "$login_key deleted by $remoteIP" );

        echo "$login_key deleted.<hr>";

        // don't check password again here
        ts_showData( false );
        }
    else {
        ts_log( "$login_key delete failed for $remoteIP" );

        echo "DELETE operation failed?";
        }
    }

function ts_printLink( $inFileName, $inTicketID ) {
    global $useRemoteMirrors, $remoteMirrorURLFile;

    if( $useRemoteMirrors ) {    

        if( is_file( $remoteMirrorURLFile ) ) {

            $urls = file( $remoteMirrorURLFile,
                          FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

            $num = count( $urls );


            if( $num > 0 ) {

                // pick one at random
                $url = $urls[ mt_rand( 0, $num - 1 ) ];

                $url = $url . $inFileName;
                
                
                echo "<a href=\"$url\">$inFileName</a>";
                return;
                }
            }
        }

    
    // default:  server directly through our script

    echo "<a href=\"server.php?action=download&login_key=$inTicketID&" .
        "file_name=$inFileName\">$inFileName</a>";
    }



function ts_checkTicket() {
    
    $login_key= ts_requestFilter( "login_key", "/[A-HJ-NP-Z2-9\-]+/i" );
    
    $login_key= strtoupper( $login_key);    
    
    global $tableNamePrefix;

    
    $query = "SELECT COUNT(*) FROM $tableNamePrefix"."tickets ".
        "WHERE login_key = '$login_key' AND blocked = 0;";
    $result = ts_queryDatabase( $query );

    $countMatching = ts_mysqli_result( $result, 0, 0 );

    if( $countMatching == 1 ) {
        echo "VALID";
        }
    else {
        echo "INVALID";
        }
    }



function ts_getTicketEmail() {
    
    $login_key= ts_requestFilter( "login_key", "/[A-HJ-NP-Z2-9\-]+/i" );
    
    $login_key= strtoupper( $login_key);    
    
    global $tableNamePrefix;


    
    $query = "SELECT email FROM $tableNamePrefix"."tickets ".
        "WHERE login_key= '$login_key' AND blocked = 0;";
    $result = ts_queryDatabase( $query );

    if( mysqli_num_rows( $result ) == 1 ) {
        
        $email = ts_mysqli_result( $result, 0, 0 );

        echo $email;
        }
    else {
        echo "INVALID";
        }
    }

function ts_logout() {
    ts_checkReferrer();
    
    ts_clearPasswordCookie();

    echo "Logged out";
    }


function ts_showData( $checkPassword = true ) {
    // these are global so they work in embeded function call below
    global $skip, $search, $order_by;

    if( $checkPassword ) {
        ts_checkPassword( "show_data" );
        }
    
    global $tableNamePrefix, $remoteIP;
    

    echo "<table width='100%' border=0><tr>".
        "<td>[<a href=\"server.php?action=show_data" .
            "\">Main</a>]</td>".
        "<td align=right>[<a href=\"server.php?action=logout" .
            "\">Logout</a>]</td>".
        "</tr></table><br><br><br>";




    $skip = ts_requestFilter( "skip", "/[0-9]+/", 0 );
    
    global $ticketsPerPage;
    
    $search = ts_requestFilter( "search", "/[A-Z0-9_@. -]+/i" );

    $order_by = ts_requestFilter( "order_by", "/[A-Z_]+/i",
                                  "key_id" );
    
    $keywordClause = "";
    $searchDisplay = "";
    
    if( $search != "" ) {
        

        $keywordClause = "WHERE ( email LIKE '%$search%' " .
            "OR login_key LIKE '%$search%' ) ";

        $searchDisplay = " matching <b>$search</b>";
        }
    

    

    // first, count results
    $query = "SELECT COUNT(*) FROM $tableNamePrefix"."tickets $keywordClause;";

    $result = ts_queryDatabase( $query );
    $totalTickets = ts_mysqli_result( $result, 0, 0 );


    $orderDir = "DESC";

    if( $order_by == "email" ) {
        $orderDir = "ASC";
        }
    
             
    $query = "SELECT * FROM $tableNamePrefix"."tickets $keywordClause".
        "ORDER BY $order_by $orderDir ".
        "LIMIT $skip, $ticketsPerPage;";
    $result = ts_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );

    $startSkip = $skip + 1;
    
    $endSkip = $startSkip + $ticketsPerPage - 1;

    if( $endSkip > $totalTickets ) {
        $endSkip = $totalTickets;
        }



        // form for searching tickets
?>
        <hr>
            <FORM ACTION="server.php" METHOD="post">
    <INPUT TYPE="hidden" NAME="action" VALUE="show_data">
    <INPUT TYPE="hidden" NAME="order_by" VALUE="<?php echo $order_by;?>">
    <INPUT TYPE="text" MAXLENGTH=40 SIZE=20 NAME="search"
             VALUE="<?php echo $search;?>">
    <INPUT TYPE="Submit" VALUE="Search">
    </FORM>
        <hr>
<?php

    

    
    echo "$totalTickets active tickets". $searchDisplay .
        " (showing $startSkip - $endSkip):<br>\n";

    
    $nextSkip = $skip + $ticketsPerPage;

    $prevSkip = $skip - $ticketsPerPage;
    
    if( $prevSkip >= 0 ) {
        echo "[<a href=\"server.php?action=show_data" .
            "&skip=$prevSkip&search=$search&order_by=$order_by\">".
            "Previous Page</a>] ";
        }
    if( $nextSkip < $totalTickets ) {
        echo "[<a href=\"server.php?action=show_data" .
            "&skip=$nextSkip&search=$search&order_by=$order_by\">".
            "Next Page</a>]";
        }

    echo "<br><br>";
    
    echo "<table border=1 cellpadding=5>\n";

    function orderLink( $inOrderBy, $inLinkText ) {
        global $skip, $search, $order_by;
        if( $inOrderBy == $order_by ) {
            // already displaying this order, don't show link
            return "<b>$inLinkText</b>";
            }

        // else show a link to switch to this order
        return "<a href=\"server.php?action=show_data" .
            "&search=$search&skip=$skip&order_by=$inOrderBy\">$inLinkText</a>";
        }

    
    echo "<tr>\n";    
    echo "<tr><td>".orderLink( "email", "Email" )."</td>\n";
	echo "<td>Login Key</td>\n";
    echo "<td>".orderLink( "creation_date", "Created" )."</td>\n";
    echo "<td>Blocked</td>\n";
    echo "</tr>\n";


    for( $i=0; $i<$numRows; $i++ ) {
        $login_key= ts_mysqli_result( $result, $i, "login_key" );
        $creation_date = ts_mysqli_result( $result, $i, "creation_date" );
        $email = ts_mysqli_result( $result, $i, "email" );
        $blocked = ts_mysqli_result( $result, $i, "blocked" );

        $block_toggle = "";
        
        if( $blocked ) {
            $blocked = "BLOCKED";
            $block_toggle = "<a href=\"server.php?action=block_login_key&".
                "blocked=0&login_key=$login_key\">unblock</a>";
            
            }
        else {
            $blocked = "";
            $block_toggle = "<a href=\"server.php?action=block_login_key&".
                "blocked=1&login_key=$login_key\">block</a>";
            
            }
        

        
        echo "<tr>\n";
        echo "<td>$email</td>\n";       
        echo "<td><b>$login_key</b>";
        echo "[<a href=\"server.php?action=show_detail" .
            "&login_key=$login_key\">detail</a>]</td>\n";
        echo "<td>$creation_date</td> ";
        echo "<td align=right>$blocked [$block_toggle]</td>\n";      
        echo "</tr>\n";
        }
    echo "</table>";


    echo "<hr>";

        // put forms in a table
?>
<center>
	<table border=1 cellpadding=10>
		<tr>
			<?php
				// fake a security hashes to include in form
				global $fastspringPrivateKeys;
				$data = "abc";
				// form for force-creating a new id
			?>
			<td>
				Create new Ticket:<br>
				<FORM ACTION="server.php" METHOD="post">
					<INPUT TYPE="hidden" NAME="action" VALUE="sell_ticket">
					<INPUT TYPE="hidden" NAME="manual" VALUE="1">
					Email:
					<INPUT TYPE="text" MAXLENGTH=80 SIZE=20 NAME="email"><br>
						  
					<INPUT TYPE="Submit" VALUE="Generate">
				</FORM>
			</td>
		</tr>
	</table>
</center>  
<?php



    
    echo "<a href=\"server.php?action=show_log\">".
        "Show log</a>";
    echo "<hr>";
    echo "Generated for $remoteIP\n";

    }



function ts_showDetail( $checkPassword = true ) {
    if( $checkPassword ) {
        ts_checkPassword( "show_detail" );
        }
    
    echo "[<a href=\"server.php?action=show_data" .
         "\">Main</a>]<br><br><br>";
    
    global $tableNamePrefix;
    

    $login_key= ts_requestFilter( "login_key", "/[A-HJ-NP-Z2-9\-]+/i" );

    $login_key= strtoupper( $login_key);


    // form for sending out download emails

            
    $query = "SELECT * FROM $tableNamePrefix"."tickets ".
            "WHERE login_key= '$login_key';";
    $result = ts_queryDatabase( $query );
    
    $numRows = mysqli_num_rows( $result );

    $row = mysqli_fetch_array( $result, MYSQLI_ASSOC );

    $email = $row[ "email" ];
    
    // form for editing ticket data
?>
        <hr>
        Edit ticket:<br>
            <FORM ACTION="server.php" METHOD="post">
    <INPUT TYPE="hidden" NAME="action" VALUE="edit_ticket">
    <INPUT TYPE="hidden" NAME="login_key" VALUE="<?php echo $login_key;?>">
    Email:
    <INPUT TYPE="text" MAXLENGTH=80 SIZE=20 NAME="email"
            VALUE="<?php echo $email;?>"><br>
    Key:
    <INPUT TYPE="text" MAXLENGTH=80 SIZE=20 NAME="key"
            VALUE="<?php echo ticketGeneration(); ?>"><br>

    <INPUT TYPE="Submit" VALUE="Update">
    </FORM>
        <hr>
<?php
            
    }




$ts_mysqlLink;


// general-purpose functions down here, many copied from seedBlogs

/**
 * Connects to the database according to the database variables.
 */  
function ts_connectToDatabase() {
    global $databaseServer,
        $databaseUsername, $databasePassword, $databaseName,
        $ts_mysqlLink;
    
    
    $ts_mysqlLink =
        mysqli_connect( $databaseServer, $databaseUsername, $databasePassword )
        or ts_operationError( "Could not connect to database server: " .
                              mysqli_error( $ts_mysqlLink ) );
    
    mysqli_select_db( $ts_mysqlLink, $databaseName )
        or ts_operationError( "Could not select $databaseName database: " .
                              mysqli_error( $ts_mysqlLink ) );
    }


 
/**
 * Closes the database connection.
 */
function ts_closeDatabase() {
    global $ts_mysqlLink;
    
    mysqli_close( $ts_mysqlLink );
    }



/**
 * Queries the database, and dies with an error message on failure.
 *
 * @param $inQueryString the SQL query string.
 *
 * @return a result handle that can be passed to other mysql functions.
 */
function ts_queryDatabase( $inQueryString ) {
    global $ts_mysqlLink;
    
    if( gettype( $ts_mysqlLink ) != "resource" ) {
        // not a valid mysql link?
        ts_connectToDatabase();
        }
    
    $result = mysqli_query( $ts_mysqlLink, $inQueryString );
    
    if( $result == FALSE ) {

        $errorNumber = mysqli_errno( $ts_mysqlLink );
        
        // server lost or gone?
        if( $errorNumber == 2006 ||
            $errorNumber == 2013 ||
            // access denied?
            $errorNumber == 1044 ||
            $errorNumber == 1045 ||
            // no db selected?
            $errorNumber == 1046 ) {

            // connect again?
            ts_closeDatabase();
            ts_connectToDatabase();

            $result = mysqli_query( $ts_mysqlLink, $inQueryString )
                or ts_operationError(
                    "Database query failed:<BR>$inQueryString<BR><BR>" .
                    mysqli_error( $ts_mysqlLink ) );
            }
        else {
            // some other error (we're still connected, so we can
            // add log messages to database
            ts_fatalError( "Database query failed:<BR>$inQueryString<BR><BR>" .
                           mysqli_error( $ts_mysqlLink ) );
            }
        }

    return $result;
    }


/**
 * Replacement for the old mysql_result function.
 */
function ts_mysqli_result( $result, $number, $field=0 ) {
    mysqli_data_seek( $result, $number );
    $row = mysqli_fetch_array( $result );
    return $row[ $field ];
    }



/**
 * Checks whether a table exists in the currently-connected database.
 *
 * @param $inTableName the name of the table to look for.
 *
 * @return 1 if the table exists, or 0 if not.
 */
function ts_doesTableExist( $inTableName ) {
    // check if our table exists
    $tableExists = 0;
    
    $query = "SHOW TABLES";
    $result = ts_queryDatabase( $query );

    $numRows = mysqli_num_rows( $result );


    for( $i=0; $i<$numRows && ! $tableExists; $i++ ) {

        $tableName = ts_mysqli_result( $result, $i, 0 );
        
        if( $tableName == $inTableName ) {
            $tableExists = 1;
            }
        }
    return $tableExists;
    }



function ts_log( $message ) {
    global $enableLog, $tableNamePrefix, $ts_mysqlLink;

    if( $enableLog ) {
        $slashedMessage = mysqli_real_escape_string( $ts_mysqlLink, $message );
    
        $query = "INSERT INTO $tableNamePrefix"."log VALUES ( " .
            "NULL, '$slashedMessage', CURRENT_TIMESTAMP );";
        $result = ts_queryDatabase( $query );
        }
    }



/**
 * Displays the error page and dies.
 *
 * @param $message the error message to display on the error page.
 */
function ts_fatalError( $message ) {
    //global $errorMessage;

    // set the variable that is displayed inside error.php
    //$errorMessage = $message;
    
    //include_once( "error.php" );

    // for now, just print error message
    $logMessage = "Fatal error:  $message";
    
    echo( $logMessage );

    ts_log( $logMessage );
    
    die();
    }



/**
 * Displays the operation error message and dies.
 *
 * @param $message the error message to display.
 */
function ts_operationError( $message ) {
    
    // for now, just print error message
    echo( "ERROR:  $message" );
    die();
    }


/**
 * Recursively applies the addslashes function to arrays of arrays.
 * This effectively forces magic_quote escaping behavior, eliminating
 * a slew of possible database security issues. 
 *
 * @inValue the value or array to addslashes to.
 *
 * @return the value or array with slashes added.
 */
function ts_addslashes_deep( $inValue ) {
    return
        ( is_array( $inValue )
          ? array_map( 'ts_addslashes_deep', $inValue )
          : addslashes( $inValue ) );
    }



/**
 * Recursively applies the stripslashes function to arrays of arrays.
 * This effectively disables magic_quote escaping behavior. 
 *
 * @inValue the value or array to stripslashes from.
 *
 * @return the value or array with slashes removed.
 */
function ts_stripslashes_deep( $inValue ) {
    return
        ( is_array( $inValue )
          ? array_map( 'ts_stripslashes_deep', $inValue )
          : stripslashes( $inValue ) );
    }



/**
 * Filters a $_REQUEST variable using a regex match.
 *
 * Returns "" (or specified default value) if there is no match.
 */
function ts_requestFilter( $inRequestVariable, $inRegex, $inDefault = "" ) {
    if( ! isset( $_REQUEST[ $inRequestVariable ] ) ) {
        return $inDefault;
        }

    return ts_filter( $_REQUEST[ $inRequestVariable ], $inRegex, $inDefault );
    }


/**
 * Filters a value  using a regex match.
 *
 * Returns "" (or specified default value) if there is no match.
 */
function ts_filter( $inValue, $inRegex, $inDefault = "" ) {
    
    $numMatches = preg_match( $inRegex,
                              $inValue, $matches );

    if( $numMatches != 1 ) {
        return $inDefault;
        }
        
    return $matches[0];
    }



// this function checks the password directly from a request variable
// or via hash from a cookie.
//
// It then sets a new cookie for the next request.
//
// This avoids storing the password itself in the cookie, so a stale cookie
// (cached by a browser) can't be used to figure out the password and log in
// later. 
function ts_checkPassword( $inFunctionName ) {
    $password = "";
    $password_hash = "";

    $badCookie = false;
    
    
    global $accessPasswords, $tableNamePrefix, $remoteIP, $enableYubikey,
        $passwordHashingPepper;

    $cookieName = $tableNamePrefix . "cookie_password_hash";

    $passwordSent = false;
    
    if( isset( $_REQUEST[ "passwordHMAC" ] ) ) {
        $passwordSent = true;

        // already hashed client-side on login form
        // hash again, because hash client sends us is not stored in
        // our settings file
        $password = ts_hmac_sha1( $passwordHashingPepper,
                                  $_REQUEST[ "passwordHMAC" ] );
        
        
        // generate a new hash cookie from this password
        $newSalt = time();
        $newHash = md5( $newSalt . $password );
        
        $password_hash = $newSalt . "_" . $newHash;
        }
    else if( isset( $_COOKIE[ $cookieName ] ) ) {
        ts_checkReferrer();
        $password_hash = $_COOKIE[ $cookieName ];
        
        // check that it's a good hash
        
        $hashParts = preg_split( "/_/", $password_hash );

        // default, to show in log message on failure
        // gets replaced if cookie contains a good hash
        $password = "(bad cookie:  $password_hash)";

        $badCookie = true;
        
        if( count( $hashParts ) == 2 ) {
            
            $salt = $hashParts[0];
            $hash = $hashParts[1];

            foreach( $accessPasswords as $truePassword ) {    
                $trueHash = md5( $salt . $truePassword );
            
                if( $trueHash == $hash ) {
                    $password = $truePassword;
                    $badCookie = false;
                    }
                }
            
            }
        }
    else {
        // no request variable, no cookie
        // cookie probably expired
        $badCookie = true;
        $password_hash = "(no cookie.  expired?)";
        }
    
        
    
    if( ! in_array( $password, $accessPasswords ) ) {

        if( ! $badCookie ) {
            
            echo "Incorrect password.";

            ts_log( "Failed $inFunctionName access with password:  ".
                    "$password" );
            }
        else {
            echo "Session expired.";
                
            ts_log( "Failed $inFunctionName access with bad cookie:  ".
                    "$password_hash" );
            }
        
        die();
        }
    else {
        
        if( $passwordSent && $enableYubikey ) {
            global $yubikeyIDs, $yubicoClientID, $yubicoSecretKey,
                $ticketGenerationSecret;
            
            $yubikey = $_REQUEST[ "yubikey" ];

            $index = array_search( $password, $accessPasswords );
            $yubikeyIDList = preg_split( "/:/", $yubikeyIDs[ $index ] );

            $providedID = substr( $yubikey, 0, 12 );

            if( ! in_array( $providedID, $yubikeyIDList ) ) {
                echo "Provided Yubikey does not match ID for this password.";
                die();
                }
            
            
            $nonce = ts_hmac_sha1( $ticketGenerationSecret, uniqid() );
            
            $callURL =
                "https://api2.yubico.com/wsapi/2.0/verify?id=$yubicoClientID".
                "&otp=$yubikey&nonce=$nonce";
            
            $result = trim( file_get_contents( $callURL ) );

            $resultLines = preg_split( "/\s+/", $result );

            sort( $resultLines );

            $resultPairs = array();

            $messageToSignParts = array();
            
            foreach( $resultLines as $line ) {
                // careful here, because = is used in base-64 encoding
                // replace first = in a line (the key/value separator)
                // with #
                
                $lineToParse = preg_replace( '/=/', '#', $line, 1 );

                // now split on # instead of =
                $parts = preg_split( "/#/", $lineToParse );

                $resultPairs[$parts[0]] = $parts[1];

                if( $parts[0] != "h" ) {
                    // include all but signature in message to sign
                    $messageToSignParts[] = $line;
                    }
                }
            $messageToSign = implode( "&", $messageToSignParts );

            $trueSig =
                base64_encode(
                    hash_hmac( 'sha1',
                               $messageToSign,
                               // need to pass in raw key
                               base64_decode( $yubicoSecretKey ),
                               true) );
            
            if( $trueSig != $resultPairs["h"] ) {
                echo "Yubikey authentication failed.<br>";
                echo "Bad signature from authentication server<br>";
                die();
                }

            $status = $resultPairs["status"];
            if( $status != "OK" ) {
                echo "Yubikey authentication failed: $status";
                die();
                }

            }
        
        // set cookie again, renewing it, expires in 24 hours
        $expireTime = time() + 60 * 60 * 24;
    
        setcookie( $cookieName, $password_hash, $expireTime, "/" );
        }
    }
 



function ts_clearPasswordCookie() {
    global $tableNamePrefix;

    $cookieName = $tableNamePrefix . "cookie_password_hash";

    // expire 24 hours ago (to avoid timezone issues)
    $expireTime = time() - 60 * 60 * 24;

    setcookie( $cookieName, "", $expireTime, "/" );
    }
 
 



// found here:
// http://php.net/manual/en/function.fpassthru.php

function ts_send_file( $path ) {
    session_write_close();
    //ob_end_clean();
    
    if( !is_file( $path ) || connection_status() != 0 ) {
        return( FALSE );
        }
    

    //to prevent long file from getting cut off from     //max_execution_time

    set_time_limit( 0 );

    $name = basename( $path );

    //filenames in IE containing dots will screw up the
    //filename unless we add this

    // sometimes user agent is not set!
    if( ! empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
        
        if( strstr( $_SERVER['HTTP_USER_AGENT'], "MSIE" ) ) {
            $name =
                preg_replace('/\./', '%2e',
                             $name, substr_count($name, '.') - 1);
            }
        }
    
    
    //required, or it might try to send the serving
    //document instead of the file

    header("Cache-Control: ");
    header("Pragma: ");
    header("Content-Type: application/octet-stream");
    header("Content-Length: " .(string)(filesize($path)) );
    header('Content-Disposition: attachment; filename="'.$name.'"');
    header("Content-Transfer-Encoding: binary\n");

    if( $file = fopen( $path, 'rb' ) ) {
        while( ( !feof( $file ) )
               && ( connection_status() == 0 ) ) {
            print( fread( $file, 1024*8 ) );
            flush();
            }
        fclose($file);
        }
    return( (connection_status() == 0 ) and !connection_aborted() );
    }




function ts_hmac_sha1( $inKey, $inData ) {
    return hash_hmac( "sha1", 
                      $inData, $inKey );
    } 

 
function ts_hmac_sha1_raw( $inKey, $inData ) {
    return hash_hmac( "sha1", 
                      $inData, $inKey, true );
    } 


 
// convert a binary string into a "readable" base-32 encoding
function ts_readableBase32Encode( $inBinaryString ) {
    global $readableBase32DigitArray;
    
    $binaryDigits = str_split( $inBinaryString );

    // string of 0s and 1s
    $binString = "";
    
    foreach( $binaryDigits as $digit ) {
        $binDigitString = decbin( ord( $digit ) );

        // pad with 0s
        $binDigitString =
            substr( "00000000", 0, 8 - strlen( $binDigitString ) ) .
            $binDigitString;

        $binString = $binString . $binDigitString;
        }

    // now have full string of 0s and 1s for $inBinaryString

    return ts_readableBase32EncodeFromBitString( $binString );
    } 




// encodes a string of 0s and 1s into an ASCII readable-base32 string 
function ts_readableBase32EncodeFromBitString( $inBitString ) {
    global $readableBase32DigitArray;


    // chunks of 5 bits
    $chunksOfFive = str_split( $inBitString, 5 );

    $encodedString = "";
    foreach( $chunksOfFive as $chunk ) {
        $index = bindec( $chunk );

        $encodedString = $encodedString . $readableBase32DigitArray[ $index ];
        }
    
    return $encodedString;
    }
 


// decodes an ASCII readable-base32 string into a string of 0s and 1s 
function ts_readableBase32DecodeToBitString( $inBase32String ) {
    global $readableBase32DigitArray;
    
    $digits = str_split( $inBase32String );

    $bitString = "";

    foreach( $digits as $digit ) {
        $index = array_search( $digit, $readableBase32DigitArray );

        $binDigitString = decbin( $index );

        // pad with 0s
        $binDigitString =
            substr( "00000", 0, 5 - strlen( $binDigitString ) ) .
            $binDigitString;

        $bitString = $bitString . $binDigitString;
        }

    return $bitString;
    }
 
 
 
// decodes a ASCII hex string into an array of 0s and 1s 
function ts_hexDecodeToBitString( $inHexString ) {
        global $readableBase32DigitArray;
    
    $digits = str_split( $inHexString );

    $bitString = "";

    foreach( $digits as $digit ) {
        $index = hexdec( $digit );

        $binDigitString = decbin( $index );

        // pad with 0s
        $binDigitString =
            substr( "0000", 0, 4 - strlen( $binDigitString ) ) .
            $binDigitString;

        $bitString = $bitString . $binDigitString;
        }

    return $bitString;
    }
 


 
?>
