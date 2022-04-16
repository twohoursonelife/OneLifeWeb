<?php

// Basic settings
// You must set these for the server to work
$databaseServer = "server";
$databaseUsername = "username";
$databasePassword = "password";
$databaseName = "database";


// Base URL/Domain for public web servers. Must have a forward slash at the end.
$mainSiteDomain = "web.twohoursonelife.com/";

$mainSiteURL = "https://" + $mainSiteDomain;


// URL to specific servers, generally used commonly. http only for compatability.
$ticketServerURL = "http://" + $mainSiteDomain + "ticketServer/server.php";
$photoServerURL = "http://" + $mainSiteDomain + "photoServer/server.php";

// secret shared with trusted game servers that allows them to post
// game stats

// MUST be changed from this default to prevent false game stats reporting.

// should not contain spaces
$sharedGameServerSecret = "secret_phrase";


// should web-based admin require yubikey two-factor authentication?
$enableYubikey = 0;


// For hashing admin passwords so that they don't appear in the clear
// in this file.
// You can change this to your own string so that password hashes in
// this file differ from hashes of the same passwords used elsewhere.
$passwordHashingPepper = "262f43f043031282c645d0eb352df723a3ddc88f";


// passwords are given as hashes below, computed by:
// hmac_sha1( $passwordHashingPepper,
//            hmac_sha1( $passwordHashingPepper, $password ) )
// Where $passwordHashingPepper is used as the hmac key.
// Client-side hashing sends the password to the server as:
//   hmac_sha1( $passwordHashingPepper, $password )
// The extra hash performed by the server prevents the hashes in
// this file from being used to login directly without knowing the actual
// password.

// For convenience, after setting a $passwordHashingPepper and chosing a
// password, hashes can be generated by invoking passwordHashUtility.php
// in your browser.

// default passwords that have been included as hashes below are:
// "secret" and "secret2"

// hashes of passwords for for web-based admin access
$accessPasswords = array( "8e409075ab35b161f6d2d57775e5efbee8d7b674",
                          "20e1883a3d63607b60677dca87b41e04316ffc63" );


// secret used for encrypting a download code when it is requested for a
// given email address
// (for remote procedure calls that need to obtain a download code for a given
//  user)
// MUST replace this to keep ticket ids secret from outsiders
$sharedEncryptionSecret = "19fbc6168268d7a80945e35d999f0d0ddae4cdff";