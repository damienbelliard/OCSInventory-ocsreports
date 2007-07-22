<?php 
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Pierre LEMMET 2006
// Web: http://ocsinventory.sourceforge.net
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================
//Modified on $Date: 2007-07-22 18:05:41 $$Author: plemmet $($Revision: 1.9 $)

if( $_SESSION["lvluser"] != SADMIN )
	die("FORBIDDEN");
	
if( isset( $_GET["isgroup"] ) )
	$_SESSION["isgroup"] = $_GET["isgroup"];
	
if( isset($_GET["frompref"]) && $_GET["frompref"] == 1 ) {
	unset( $_SESSION["saveId"] );
}
else if( isset($_GET["systemid"]) ) {
	$_SESSION["saveId"] = $_GET["systemid"];
}

if( ! isset($_SESSION["saveRequest"])) {
	$_SESSION["saveRequest"] = $_SESSION["storedRequest"];
}

if( isset($_GET["affpack"])) {
	$ok = resetPack( $_GET["affpack"] );
	$ok = $ok && setPack( $_GET["affpack"] );
}

if( $_GET["retour"] == 1 || (isset($_GET["affpack"]) && $ok) ) {
	$_SESSION["storedRequest"] = $_SESSION["saveRequest"];
	unset( $_SESSION["saveRequest"] );
	if( ! isset( $_SESSION["saveId"] ) )
		echo "<script language='javascript'>window.location='index.php?redo=1".$_SESSION["queryString"]."';</script>";
		//TODO MARCHE P�S
	else if( isset( $_SESSION["isgroup"] ) && $_SESSION["isgroup"]== "1" )
		echo "<script language='javascript'>window.location='index.php?multi=29&popup=1&systemid=".$_SESSION["saveId"]."&option=".$l->g(500)."';</script>";
	else
		echo "<script language='javascript'>window.location='machine.php?systemid=".$_SESSION["saveId"]."&option=".$l->g(500)."';</script>";
	die();
}

$nbMach = 0;
if( isset($_GET["systemid"]))
	$nbMach = 1;
else if( isset( $_POST["maxcheck"] ) ) {
	foreach( $_POST as $key=>$val ) {
		if( strpos ( $key, "checkmass" ) !== false ) {
			$tbd[] = $val;
			$nbMach++;
		}		
	}	
}

if( empty( $tbd ) )
	$nbMach = getCount($_SESSION["saveRequest"]);

if( $nbMach > 0 ) {
	$canAc = 1;
	$strHead = $l->g(477);
	if( ! isset($_SESSION["isgroup"]) || $_SESSION["isgroup"] == 0 ) 
		$strHead .= " <font class='warn'>( $nbMach ".$l->g(478).")</font>";
	PrintEnTete( $strHead );
}
else {
	die($l->g(478));	
}

echo "<br><center><a href='#' OnClick=\"window.location='index.php?multi=24&retour=1'\"><= ".$l->g(188)."</a></center>";

if( isset($_GET["systemid"]))
	$canAc = 3; //preferences.php must set systemid in query string
	
$lbl = "pack";	
$sql = "";
$whereId = "e.ID";
$linkId = "e.ID";
$select = array("ID"=>$l->g(460), "e.FILEID"=>$l->g(475), "NAME"=>$l->g(49), 
"PRIORITY"=>$l->g(440),"INFO_LOC"=>$l->g(470), "PACK_LOC"=>$l->g(471), 
"FRAGMENTS"=>$l->g(480), "SIZE"=>$l->g(462), "OSNAME"=>$l->g(25));	
$selectPrelim = array("e.ID"=>"e.ID");	
$from = "download_enable e LEFT JOIN download_available d ON d.fileid = e.fileid";
$fromPrelim = "";
$group = "";
$order = "e.FILEID DESC";
$countId = "e.ID";

$requete = new Req($lbl,$whereId,$linkId,$sql,$select,$selectPrelim,$from,$fromPrelim,$group,$order,$countId,true);
ShowResults($requete,true,false,false,false,false,false,$canAc);

	function setPack( $packid ) {		
		global $_GET;
		if( isset($_GET["systemid"])) {
			$val["h.id"] = $_GET["systemid"];
			if( ! @mysql_query( "INSERT INTO devices(HARDWARE_ID, NAME, IVALUE) VALUES('".$val["h.id"]."', 'DOWNLOAD', $packid )", $_SESSION["writeServer"] )) {
				echo "<br><center><font color=red><b>ERROR: MySql connection problem<br>".mysql_error($_SESSION["writeServer"])."</b></font></center>";
				return false;
			}
			$_SESSION["justAdded"] = true;
		}
		else if( isset( $_GET["compAffect1"] ) ) {		
			foreach( $_GET as $key=>$val ) {
				if( strpos ( $key, "compAffect" ) !== false ) {
					if( ! @mysql_query( "INSERT INTO devices(HARDWARE_ID, NAME, IVALUE) VALUES('".$val."', 'DOWNLOAD', $packid)", $_SESSION["writeServer"] )) {
						echo "<br><center><font color=red><b>ERROR: MySql connection problem<br>".mysql_error($_SESSION["writeServer"])."</b></font></center>";
						return false;
					}
				}
			}
		}
		else {
			$lareq = getPrelim( $_SESSION["saveRequest"] );
			if( ! $res = @mysql_query( $lareq, $_SESSION["readServer"] ))
				return false;
			while( $val = @mysql_fetch_array($res)) {
				if( ! @mysql_query( "INSERT INTO devices(HARDWARE_ID, NAME, IVALUE) VALUES('".$val["h.id"]."', 'DOWNLOAD', $packid)", $_SESSION["writeServer"] )) {
					echo "<br><center><font color=red><b>ERROR: MySql connection problem<br>".mysql_error($_SESSION["writeServer"])."</b></font></center>";
					return false;
				}
			}
		}
		return true;	
	}
	
	function resetPack( $packid ) {
		global $_GET;
		if( isset($_GET["systemid"])) {
			$val["h.id"] = $_GET["systemid"];
			if( ! @mysql_query( "DELETE FROM devices WHERE name='DOWNLOAD' AND IVALUE=$packid AND hardware_id='".$val["h.id"]."'", $_SESSION["writeServer"] )) {
				echo "<br><center><font color=red><b>ERROR: MySql connection problem<br>".mysql_error($_SESSION["writeServer"])."</b></font></center>";
				return false;
			}
		}
		else if( isset( $_GET["compAffect1"] ) ) {		
			foreach( $_GET as $key=>$val ) {
				if( strpos ( $key, "compAffect" ) !== false ) {
					if( ! @mysql_query( "DELETE FROM devices WHERE name='DOWNLOAD' AND IVALUE=$packid AND hardware_id='".$val."'", $_SESSION["writeServer"] )) {
						echo "<br><center><font color=red><b>ERROR: MySql connection problem<br>".mysql_error($_SESSION["writeServer"])."</b></font></center>";
						return false;
					}
				}
			}
		}
		else {
			$lareq = getPrelim( $_SESSION["saveRequest"] );
			if( ! $res = @mysql_query( $lareq, $_SESSION["readServer"] ))
				return false;
			while( $val = @mysql_fetch_array($res)) {
			
				if( ! @mysql_query( "DELETE FROM devices WHERE name='DOWNLOAD' AND IVALUE=$packid AND hardware_id='".$val["h.id"]."'", $_SESSION["writeServer"] )) {
					echo "<br><center><font color=red><b>ERROR: MySql connection problem<br>".mysql_error($_SESSION["writeServer"])."</b></font></center>";
					return false;
				}
			}
		}

		return true;		
		// comprends pas: echo "DELETE FROM devices WHERE name='FREQUENCY' AND hardware_id IN ($lareq)";flush();		
	}
?>