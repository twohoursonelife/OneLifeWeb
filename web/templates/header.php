<html>

<head>
<title>Two Hours One Life</title>

<?php
global $pathToRoot;


$referrer = "";
    
if( isset( $_SERVER['HTTP_REFERER'] ) ) {
    // pass it through without a regex filter
    // because we can't control its safety in the end anyway
    // (user can just edit URL sent to FastSpring).
    $referrer = urlencode( $_SERVER['HTTP_REFERER'] );
    }
    

if( isset( $blockRobots ) && $blockRobots == 1 ) {
?>
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
<?php
    }
?>

</head>

<body bgcolor=#222222 text=white link=#b2a536 vlink=#b2a536 alink=#b2a536>


    
    <table border=0 cellspacing=5 cellpadding=0 width=100%><tr>

    <td align=center width=11%>[<a href='https://twohoursonelife.com'>Website</a>]</td>
    <td align=center width=11%>[<a href='https://web.twohoursonelife.com/lineageServer/server.php?action=front_page'>Family Trees</a>]</td>
    <td align=center width=11%>[<a href='https://web.twohoursonelife.com/photoServer/server.php?action=front_page'>Photos</a>]</td>
    <td align=center width=11%>[<a href='https://twotech.twohoursonelife.com/'>Tech Tree</a>]</td>


</tr></table>
<center>

<table border=0 width=100% bgcolor=black>
<tr>
<td align=center>
    <table border=0 width=900><tr><td>
