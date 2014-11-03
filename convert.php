<?php
//$start_time = MICROTIME(TRUE);
error_reporting(E_ALL);
function netmask2cidr($netmask)
{
	$bits    = 0;
	$netmask = explode(".", $netmask);
	foreach ($netmask as $octect)
		$bits += strlen(str_replace("0", "", decbin($octect)));
	return $bits;
}
function xmlToArray($xml, $ns = null)
{
	$a = array();
	for ($xml->rewind(); $xml->valid(); $xml->next()) {
		$key = $xml->key();
		if (!isset($a[$key])) {
			$a[$key] = array();
			$i       = 0;
		} else
			$i = count($a[$key]);
		$simple = true;
		foreach ($xml->current()->attributes() as $k => $v) {
			$a[$key][$i][$k] = (string) $v;
			$simple          = false;
		}
		if ($ns)
			foreach ($ns as $nid => $name) {
				foreach ($xml->current()->attributes($name) as $k => $v) {
					$a[$key][$i][$nid . ':' . $k] = (string) $v;
					$simple                       = false;
				}
			}
		if ($xml->hasChildren()) {
			if ($simple)
				$a[$key][$i] = xmlToArray($xml->current(), $ns);
			else
				$a[$key][$i]['content'] = xmlToArray($xml->current(), $ns);
		} else {
			if ($simple)
				$a[$key][$i] = strval($xml->current());
			else
				$a[$key][$i]['content'] = strval($xml->current());
		}
		$i++;
	}
	return $a;
}
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
?>

<html>
<head>
<title>Loadbalancer.org xml conversion tool</title>
</head>
<body>
<style>
p { font-family: Arial, Helvetica, sans-serif;}
h1,h2,h3 { font-family: Arial, Helvetica, sans-serif; }
label { font-family: Arial, Helvetica, sans-serif;}
</style>
<div style="background-color:lightgrey; width:820px; padding:0px; margin:0 auto;">
<div style="background-color:red; width:800px; height:100px; padding:10px;">
<img src="http://images.loadbalancer.org/2014/loadbalancer_white.png">
</div><br/><center>
<?php
if ($_GET['ver']=='incorrect') { echo "<div id='helpdiv' style='display:block; background-color:e99; width:80%'>This is not a loadbalancer.org appliance v6.21 XML file. Please try another lb_config.xml</div>"; 
 }
?>
<script type="text/javascript">
// close the div in 5 secs
window.setTimeout("closeHelpDiv();", 2000);

function closeHelpDiv(){
document.getElementById("helpdiv").style.display="none";
}
</script>
<h1>loadbalancer.org appliance XML Conversion</h1>
<p>Convert your lb_config.xml v6.21 to v7.6.2</p>
<p>If you send the incorrect revision it will not be converted to prevent misconfiguration errors<br/>
<center>
<form name='xmlconvert' method='post' action='convert.php' enctype='multipart/form-data'>
<label for download>Please tick this box if you wish to download the xml file otherwise it will display in your browser</label>
<input type="checkbox" name="download"><br/><br/>
<br/><label for file>Please select your v6.21 XML </label><input type="file" name="lbconfigxml" onchange="javascript:this.form.submit();"><br/>
</form>
<p align=center>&copy; 2014 loadbalancer.org.<p>
</center>
</div>
</body>
</html>

<?php
} else {
	$VIP_array         = array(
		"server",
		"label",
		"fallback",
		"scheduler",
		"persistent",
		"granularity",
		"protocol",
		"feedbackmethod",
		"forwardingmethod",
		"service"
	);
	$VIP_array_count   = count($VIP_array);
	$VIP_service_array = array(
		"type",
		"port",
		"command",
		"file",
		"response"
	);
	$RIP_array         = array(
		"server",
		"label",
		"weight",
		"previous_weight",
		"forwardingmethod",
		"minconns",
		"maxconns"
	);
	$xmldata           = $_FILES['lbconfigxml']['tmp_name'];
	$RIP_array_count   = count($RIP_array);
	$xml               = new SimpleXmlIterator($xmldata, null, true);
	$namespaces        = $xml->getNamespaces(true);
	$arr               = xmlToArray($xml, $namespaces);
	if ($arr[xmlinfo][0][version][0] != '6.21' ) 
	{
		header( 'Location: convert.php?ver=incorrect' );
	} else {
		if ($_POST['download']) {
			header("Content-disposition: attachment; filename=7.6.2-lb_config.xml");
		}
		header("Content-Type: text/xml");
		echo "<config>\n\t<xmlinfo>\n\t\t<version>7.6.2</version>\n\t\t<revision>\$Revision: 4579 \$</revision>\n\t\t<xml_version>2</xml_version>\n\t</xmlinfo>\n\t<physical>\n
		\t\t<network>\n	\t\t\t<role>master</role>\n\t\t\t<hostname>" . $arr[physical][0][network][0][hostname][0] . "</hostname>\n\t\t\t<slave>" . $arr[physical][0][network][0][slave][0] . "</slave>\n\t\t\t<fullsync>" . $arr[physical][0][network][0][fullsync][0] . "</fullsync>\n\t\t\t<dns>" . $arr[physical][0][network][0][dns][0] . "</dns>\n
		\t\t</network>\n\t\t<interface>\n";
		if ($arr[physical][0][rip][0][eth0][0][ip][0]) {
			echo "\t\t\t<eth0>up</eth0>\n";
		} else {
			echo "\t\t\t<eth0>down</eth0>\n";
		}
		if ($arr[physical][0][rip][0][eth1][0][ip][0]) {
			echo "\t\t\t<eth1>up</eth1>\n";
		} else {
			echo "\t\t\t<eth1>down</eth1>\n";
		}
		if ($arr[physical][0][rip][0][eth2][0][ip][0]) {
			echo "\t\t\t<eth2>up</eth2>\n";
		} else {
			echo "\t\t\t<eth2>down</eth2>\n";
		}
		if ($arr[physical][0][rip][0][eth3][0][ip][0]) {
			echo "\t\t\t<eth3>up</eth3>\n";
		} else {
			echo "\t\t\t<eth3>down</eth3>\n";
		}
		if (isset($arr[physical][0][rip][0][bond0][0][ip][0])) {
			echo "\t\t\t<bond0>up</bond0>\n";
		}
		if (isset($arr[physical][0][rip][0][bond1][0][ip][0])) {
			echo "\t\t\t<bond1>up</bond1>\n";
		}
		// need to check if eth0-3 are really up and know if bond0/1 need adding above
		echo "\t\t</interface>\n
		\t\t<rip>\n
		   \t\t\t<eth0>" . $arr[physical][0][rip][0][eth0][0][ip][0];
		if (!empty($arr[physical][0][rip][0][eth0][0][netmask][0])) {
			echo "/" . netmask2cidr($arr[physical][0][rip][0][eth0][0][netmask][0]);
		}
		echo "</eth0>\n";
		echo "\t\t\t<eth1>" . $arr[physical][0][rip][0][eth1][0][ip][0];
		if (!empty($arr[physical][0][rip][0][eth1][0][netmask][0])) {
			echo "/" . netmask2cidr($arr[physical][0][rip][0][eth1][0][netmask][0]);
		}
		echo "</eth1>\n";
		echo "\t\t\t<bond0>" . $arr[physical][0][rip][0][bond0][0][ip][0];
		if (!empty($arr[physical][0][rip][0][bond0][0][netmask][0])) {
			echo "/" . netmask2cidr($arr[physical][0][rip][0][bond0][0][netmask][0]);
		}
		echo "</bond0>\n
                        \t\t\t<bond1>" . $arr[physical][0][rip][0][bond1][0][ip][0];
		if (!empty($arr[physical][0][rip][0][bond1][0][netmask][0])) {
			echo "/" . netmask2cidr($arr[physical][0][rip][0][bond1][0][netmask][0]);
		}
		echo "</bond1>\n
                        \t\t\t<eth2>" . $arr[physical][0][rip][0][eth2][0][ip][0];
		if (!empty($arr[physical][0][rip][0][eth2][0][netmask][0])) {
			echo "/" . netmask2cidr($arr[physical][0][rip][0][eth2][0][netmask][0]);
		}
		echo "</eth2>\n
                        \t\t\t<eth3>" . $arr[physical][0][rip][0][eth3][0][ip][0];
		if (!empty($arr[physical][0][rip][0][eth3][0][netmask][0])) {
			echo "/" . netmask2cidr($arr[physical][0][rip][0][eth3][0][netmask][0]);
		}
		echo "</eth3>\n

		\t\t</rip>\n
		\t\t<routing>\n
			\t\t\t<gateway>\n
				\t\t\t\t<ipv4>" . $arr[physical][0][rip][0][gateway][0] . "</ipv4>\n
				\t\t\t\t<ipv6></ipv6>\n
			\t\t\t</gateway>\n
		\t\t</routing>\n
	\t</physical>\n


";
		// heartbeat section
		// VIP FLOATNG IP SECTION
		$FloatingIPCount = count($arr[physical][0][vip]);
		echo "\t<heartbeat>\n
                \t\t<node>" . $arr[heartbeat][0][node][0] . "</node>\n
                \t\t<node>" . $arr[heartbeat][0][node][1] . "</node>\n
                \t\t<keepalive>" . $arr[heartbeat][0][keepalive][0] . "</keepalive>\n
                \t\t<warntime>" . $arr[heartbeat][0][warntime][0] . "</warntime>\n
                \t\t<deadtime>" . $arr[heartbeat][0][deadtime][0] . "</deadtime>\n
                \t\t<initdead>" . $arr[heartbeat][0][initdead][0] . "</initdead>\n
                \t\t<auto_failback>" . $arr[heartbeat][0][auto_failback][0] . "</auto_failback>\n
                \t\t<serial>" . $arr[heartbeat][0][serial][0] . "</serial>\n
                \t\t<ucast>";
		if($arr[heartbeat][0][bcast][0]=='on' || $arr[heartbeat][0][bcast][0]=='yes') $arr[heartbeat][0][ucast][0]=='yes'; 
		echo $arr[heartbeat][0][ucast][0] . "</ucast>\n
                \t\t<bcast>off</bcast>\n
                \t\t<serial_ports>\n
                        \t\t\t<master>/dev/ttyS0</master>\n
                        \t\t\t<master>/dev/ttyS1</master>\n
                        \t\t\t<master>/dev/ttyS2</master>\n
                \t\t</serial_ports>\n
                \t\t<ucast_interface>\n
                        \t\t\t<master>lo</master>\n
                        \t\t\t<slave>lo</slave>\n
                \t\t</ucast_interface>\n
                \t\t<ucast_ip>\n
                        \t\t\t<master>127.0.0.1</master>\n
                        \t\t\t<slave>127.0.0.1</slave>\n
                \t\t</ucast_ip>\n
                \t\t<udpport>" . $arr[heartbeat][0][udpport][0] . "</udpport>\n
                \t\t<ping>" . $arr[heartbeat][0][ping][0] . "</ping>\n";
		// create vips
		for ($fip = 0; $fip <= $FloatingIPCount; $fip++) {
			$CurrentFloatingIP = $arr[physical][0][vip][$fip];
			echo "\t<vip>" . $CurrentFloatingIP . "</vip>\n";
		}
		echo "</heartbeat>\n";
		// ldirectord start
		echo "\t<ldirectord>\n
		\t\t<global>\n
			\t\t\t<checkinterval>" . $arr[ldirectord][0]['global'][0][checkinterval][0] . "</checkinterval>\n
			\t\t\t<checktimeout>" . $arr[ldirectord][0]['global'][0][checktimeout][0] . "</checktimeout>\n
			\t\t\t<negotiatetimeout>" . $arr[ldirectord][0]['global'][0][negotiatetimeout][0] . "</negotiatetimeout>\n
			\t\t\t<failurecount>" . $arr[ldirectord][0]['global'][0][failurecount][0] . "</failurecount>\n
			\t\t\t<fallbackswitch>" . $arr[ldirectord][0]['global'][0][fallbackswitch][0] . "</fallbackswitch>\n
			\t\t\t<quiescent>" . $arr[ldirectord][0]['global'][0][quiescent][0] . "</quiescent>\n
			\t\t\t<default_forwarding>" . $arr[ldirectord][0]['global'][0][default_forwarding][0] . "</default_forwarding>\n
			\t\t\t<autonat>" . $arr[ldirectord][0]['global'][0][autonat][0] . "</autonat>\n
			\t\t\t<fork>" . $arr[ldirectord][0]['global'][0][forkcheck][0] . "</fork>
			\t\t\t<emailalert>\t
			\t\t\t<to>" . $arr[ldirectord][0]['global'][0][emailalert][0] . "</to>\n
			\t\t\t<from>" . $arr[ldirectord][0]['global'][0][emailsender][0] . "</from>\n
			\t\t</emailalert>\n
			\t\t\t<read_only>" . $arr[ldirectord][0]['global'][0][read_only][0] . "</read_only>\n
		\t\t</global>\n";
		$RealVipCount = count($arr[ldirectord][0][virtual]) - 1;
		// Start converting the VIP 
		for ($vip = 0; $vip <= $RealVipCount; $vip++) { // loop through L4 VIPS
			for ($vipkey = 0; $vipkey < $VIP_array_count; $vipkey++) { //echo $key;
				$CurrentVIP = $arr[ldirectord][0][virtual][$vip][$VIP_array[$vipkey]][0];
				        if ($VIP_array[$vipkey] == 'server') {
					$ServerPort = explode(':', $CurrentVIP);
					echo "<virtual>\t<type>";
  if($arr[ldirectord][0][virtual][$vip][protocol][0]=='fwm'){ echo 'fwmark';} else {echo 'ip';}

echo "</type>\n\t<ip_version>";
if($arr[ldirectord][0][virtual][$vip][protocol][0]!='fwm') echo "4";
echo "</ip_version>\n";
                                        if($arr[ldirectord][0][virtual][$vip][protocol][0]!='fwm'){
						echo "\t<server>" . $ServerPort[0] . "</server>\n";
						echo "\t<ports>" . $ServerPort[1] . "</ports>\n";
					}
					if($arr[ldirectord][0][virtual][$vip][protocol][0]=='fwm') {
						echo "
			<server></server>\n
			<ports></ports>\n<firewallmark>".$arr[ldirectord][0][virtual][$vip][server][0]."</firewallmark>"; 
					}

				} elseif ($VIP_array[$vipkey] == 'service') {
					echo "\t<service>\n
				\t\t<type>" . $arr[ldirectord][0][virtual][0][service][0][type][0] . "</type>\n
				\t\t<login>" . $arr[ldirectord][0][virtual][0][service][0][login][0] . "</login>\n
					\t\t<password>" . $arr[ldirectord][0][virtual][0][service][0][password][0] . "</password>\n	
						\t\t<vhost>" . $arr[ldirectord][0][virtual][0][service][0][vhost][0] . "</vhost>\n
						\t\t<check>\n
							\t\t<type>" . $arr[ldirectord][0][virtual][0][service][0][check][0][type][0] . "</type>\n\t\t";
					echo "<port>" . $arr[ldirectord][0][virtual][0][service][0][check][0][port][0] . "</port>\n
				\t\t<file>" . $arr[ldirectord][0][virtual][0][service][0][check][0][file][0] . "</file>\n
				\t\t<response>" . $arr[ldirectord][0][virtual][0][service][0][check][0][response][0] . "</response>\n
				\t\t<command/>\n
				\t\t</check>\n
				\t\t</service>\n
				\t\t<fallback>\n";
					$FallbackIPPort = explode(':', $arr[ldirectord][0]['virtual'][0][fallback][0]);
					echo "\t\t<ip>" . $FallbackIPPort[0] . "</ip>\n
				\t\t<port>" . $FallbackIPPort[1] . "</port>\n
				\t\t</fallback>\n";
				} elseif ($VIP_array[$vipkey] == 'granularity') {
					echo "\t<granularity/>\n";
				} else {
					if (empty($CurrentVIP)) {
						echo "\t<" . $VIP_array[$vipkey] . "/>\n";
					} else {
						echo "\t<" . $VIP_array[$vipkey] . ">" . $CurrentVIP . "</" . $VIP_array[$vipkey] . ">\n";
					}
				}
			}
			// Start converting each RIP in the VIP
			for ($rip = 1; $rip < count($arr[ldirectord][0][virtual][$vip][real]); $rip++) {
				echo "\t\t<real>\n";
				echo "\t\t\t<ip_version>4</ip_version>\n";
				for ($ripkey = 0; $ripkey != $RIP_array_count; $ripkey++) { //echo $key;
					$CurrentRIP = $arr[ldirectord][0][virtual][$vip][real][$rip][$RIP_array[$ripkey]][0];
					if ($RIP_array[$ripkey] == 'server') {
						$ServerPort = explode(':', $CurrentRIP);
						echo "\n\t\t\t<server>" . $ServerPort[0] . "</server>\n";
						if (isset($ServerPort[1])) {
							echo "\t\t\t<port>" . $ServerPort[1] . "</port>\n";
						}
					} else {
						if (empty($CurrentRIP)) {
							echo "\t\t\t<" . $RIP_array[$ripkey] . "/>\n";
						} else {
							echo "\t\t\t<" . $RIP_array[$ripkey] . ">" . $CurrentRIP . "</" . $RIP_array[$ripkey] . ">\n";
						}
					}
				}
				echo "\t\t</real>\n";
			} // End Each L4 RIP Inside a VIP
			echo "\t</virtual>\n";
		}
		echo "\t</ldirectord>\n";
		// HAPROXY
		echo "<haproxy>\n
		<global>\n
			<contimeout>" . $arr[haproxy][0]['global'][0][contimeout][0] . "</contimeout>\n
			<clitimeout>" . $arr[haproxy][0]['global'][0][clitimeout][0] . "</clitimeout>\n
			<srvtimeout>" . $arr[haproxy][0]['global'][0][srvtimeout][0] . "</srvtimeout>\n
			<interval>" . $arr[haproxy][0]['global'][0][interval][0] . "</interval>\n
			<rise>" . $arr[haproxy][0]['global'][0][rise][0] . "</rise>\n
			<fall>" . $arr[haproxy][0]['global'][0][fall][0] . "</fall>\n
			<maxconn>" . $arr[haproxy][0]['global'][0][maxconn][0] . "</maxconn>\n
			<redispatch>" . $arr[haproxy][0]['global'][0][redispatch][0] . "</redispatch>\n
			<ulimit>" . $arr[haproxy][0]['global'][0][ulimit][0] . "</ulimit>\n
			<abortonclose>" . $arr[haproxy][0]['global'][0][abortonclose][0] . "</abortonclose>\n
			<transparentproxy>" . $arr[haproxy][0]['global'][0][transparentproxy][0] . "</transparentproxy>\n
			<statspass>" . $arr[haproxy][0]['global'][0][statspass][0] . "</statspass>\n
			<logging>" . $arr[haproxy][0]['global'][0][logging][0] . "</logging>\n
			<read_only>" . $arr[haproxy][0]['global'][0][read_only][0] . "</read_only>\n
		</global>\n";
		// find how many virtuals we have 
		$HAProxyVIPCount = count($arr[haproxy][0]['virtual']) - 1;
		for ($hapvip = 0; $hapvip <= $HAProxyVIPCount; $hapvip++) {
			// populate the virtuals and reals inside virtuals
			echo "<virtual>\n
			<label>" . $arr[haproxy][0]['virtual'][$hapvip][label][0] . "</label>\n
			<type>ip</type>\n
			<ip_version>4</ip_version>\n";
			$ServerPort = explode(':', $arr[haproxy][0]['virtual'][$hapvip][server][0]);
			echo "<server>" . $ServerPort[0] . "</server>\n
			<ports>" . $ServerPort[1] . "</ports>\n
			<mode>" . $arr[haproxy][0]['virtual'][$hapvip][mode][0] . "</mode>\n
			<forward_for>" . $arr[haproxy][0]['virtual'][$hapvip][forward_for][0] . "</forward_for>\n
			<cookie>" . $arr[haproxy][0]['virtual'][$hapvip][cookie][0] . "</cookie>\n
			<l7_protocol>other_tcp</l7_protocol>\n
			<http_pipeline>" . $arr[haproxy][0]['virtual'][$hapvip][http_pipeline][0] . "</http_pipeline>\n
			<scheduler>" . $arr[haproxy][0]['virtual'][$hapvip][scheduler][0] . "</scheduler>\n
			<persist_time>" . $arr[haproxy][0]['virtual'][$hapvip][persist_time][0] . "</persist_time>\n
			<persist_table_size>" . $arr[haproxy][0]['virtual'][$hapvip][persist_table_size][0] . "</persist_table_size>\n
			<redispatch>" . $arr[haproxy][0]['virtual'][$hapvip][redispatch][0] . "</redispatch>\n
			<abortonclose>" . $arr[haproxy][0]['virtual'][$hapvip][abortonclose][0] . "</abortonclose>\n
			<maxconn>" . $arr[haproxy][0]['virtual'][$hapvip][maxconn][0] . "</maxconn>\n
			<appsession>\n
				<cookie>" . $arr[haproxy][0]['virtual'][$hapvip][sppsession][0][cookie][0] . "</cookie>\n
			</appsession>\n
			<check>\n
				<type>" . $arr[haproxy][0]['virtual'][$hapvip][check][0][type][0] . "</type>\n
				<port>" . $arr[haproxy][0]['virtual'][$hapvip][check][0][port][0] . "</port>\n
				<file>" . $arr[haproxy][0]['virtual'][$hapvip][check][0][file][0] . "</file>\n
				<response>" . $arr[haproxy][0]['virtual'][$hapvip][check][0][response][0] . "</response>\n
			</check>\n
			<fallback>\n";
			$ServerPort = explode(':', $arr[haproxy][0]['virtual'][$hapvip][fallback][0]);
			echo "<ip>" . $ServerPort[0] . "</ip>\n
				<port>" . $ServerPort[1] . "</port>\n
			</fallback>\n
			<stunneltproxy>" . $arr[haproxy][0]['virtual'][$hapvip][server][0][stunneltproxy][0] . "</stunneltproxy>\n";
			// L7RIPS
			// count RIPS in this L7VIP
			$HAProxyRIPCount = count($arr[haproxy][0]['virtual'][$haprip]['real']) - 1;
			for ($haprip = 0; $haprip <= $HAProxyRIPCount; $haprip++) {
				$ServerPort = explode(':', $arr[haproxy][0]['virtual'][$haprip]['real'][$haprip][server][0]);
				//$arr[haproxy][0]['virtual'][1][real][1][server][0];
				echo "	<real>\n";
				echo "<server>" . $ServerPort[0] . "</server>\n";
				echo "<port>" . $ServerPort[1] . "</port>\n";
				echo "<label>" . $arr[haproxy][0]['virtual'][$haprip]['real'][$haprip][label][0] . "</label>\n";
				echo "<type>ipv4</type>\n";
				echo "<weight>" . $arr[haproxy][0]['virtual'][$haprip]['real'][$haprip][weight][0] . "</weight>\n";
				echo "<minconns>" . $arr[haproxy][0]['virtual'][$haprip]['real'][$haprip][minconns][0] . "</minconns>\n";
				echo "<maxconns>" . $arr[haproxy][0]['virtual'][$haprip]['real'][$haprip][maxconns][0] . "</maxconns>\n";
				echo "<previous_weight>" . $arr[haproxy][0]['virtual'][$haprip]['real'][$haprip][previous_weight][0] . "</previous_weight>\n";
				echo "<encrypted></encrypted>";
				echo "			</real>\n";
			}
			echo "</virtual>\n";
		}
		echo "</haproxy>";
		// END HPROXY
		echo "\t<global>\n
		\t\t<proxy>\n
			\t\t\t<ip>" . $arr['global'][InternetAccess][0][ProxyIP][0] . "</ip>\n
			\t\t\t<port>" . $arr['global'][InternetAccess][0][ProxyPort][0] . "</port>\n
		\t\t</proxy>\n
		\t\t<firewall>\n
			\t\t\t<conntrack_size>" . $arr['global'][firewall][conntrack_size][0] . "</conntrack_size>\n
		\t\t</firewall>\n
		\t\t<timezone>UTC</timezone>\n
		\t\t<ntp></ntp>\n
		\t\t<restart>\n
			\t\t\t<heartbeat>no</heartbeat>\n
			\t\t\t<haproxy>no</haproxy>\n
			\t\t\t<pound>no</pound>\n
			\t\t\t<stunnel>no</stunnel>\n
			\t\t\t<collectd>no</collectd>\n
			\t\t\t<ldirectord>no</ldirectord>\n
			\t\t\t<firewall>no</firewall>\n
			\t\t\t<snmp>no</snmp>\n
		\t\t</restart>\n
	\t</global>\n
	\t<graph>\n
		\t\t\t<layer4>on</layer4>\n
		\t\t\t<layer7>on</layer7>\n
		\t\t\t<interfaces>on</interfaces>\n
		\t\t\t<load>on</load>\n
		\t\t\t<memory>on</memory>\n
		\t\t\t<disk>on</disk>\n
		\t\t\t<logging>off</logging>\n
		\t\t\t<interval>10</interval>\n
		\t\t\t<timeout>2</timeout>\n
		\t\t\t<threads>6</threads>\n
	\t</graph>\n
	\t<lbSetupWizard>\n
		\t\t\t<didRun>" . $arr[lbSetupWizard][0][didRun][0] . "</didRun>\n
		\t\t\t<remind>" . $arr[lbSetupWizard][0][remind][0] . "</remind>\n
		\t\t\t<lastRemind>" . $arr[lbSetupWizard][0][lastRemind][0] . "</lastRemind>\n
	\t</lbSetupWizard>\n
	\t<wizard>\n
		\t\t<setup>\n
			\t\t\t\t<hide>no</hide>\n
		\t\t</setup>\n
	\t</wizard>\n";
		echo "<pound>
		<global>
			<clienttimeout>" . $arr[pound][0]['global'][0][clienttimeout][0] . "</clienttimeout>
			<servertimeout>" . $arr[pound][0]['global'][0][servertimeout][0] . "</servertimeout>
			<tproxy>" . $arr[pound][0]['global'][0][tproxy][0] . "</tproxy>
			<logging>on</logging>
			<read_only></read_only>
			<ulimit>81000</ulimit>
			<threads>250</threads>
		</global>";
		$PoundVIP = count($arr[pound][0][virtual]) - 1;
		// Start converting the VIP 
		for ($pip = 0; $pip <= $PoundVIP; $pip++) {
			$ServerPort  = explode(':', $arr[pound][0][virtual][$pip][server][0]);
			$BackendPort = explode(':', $arr[pound][0][virtual][$pip][backend][0]);
			echo "<virtual>
			<label>Server" . $pip . "</label>\n
			<ip_version>4</ip_version>\n
			<server>" . $ServerPort[0] . "</server>\n
			<port>" . $ServerPort[1] . "</port>\n
			<backend>" . $BackendPort[0] . "</backend>\n
			<backend_port>" . $BackendPort[1] . "</backend_port>\n
			<ciphers>" . $arr[pound][0][virtual][$pip][ciphers][0] . "</ciphers>\n
			<xhttp></xhttp>\n
			<honorcipherorder>" . $arr[pound][0][virtual][$pip][cipherorder][0] . "</honorcipherorder>\n
			<allowciphernegotiation>" . $arr[pound][0][virtual][$pip][allowclientrenegotiation][0] . "</allowciphernegotiation>\n
			<disablesslv2>" . $arr[pound][0][virtual][$pip][nosslv2][0] . "</disablesslv2>\n
			<rewritelocation>" . $arr[pound][0][virtual][$pip][rewritelocation][0] . "</rewritelocation>\n
			<fragments>on</fragments>\n
			<compression>on</compression>\n
			<cert>\n
				<name></name>\n
				<state>default</state>\n
				<country></country>\n
				<province></province>\n
				<city></city>\n
				<organisation></organisation>\n
				<unit></unit>\n
				<domain></domain>\n
				<email></email>\n
			</cert>\n
		</virtual>\n";
		}
		echo "	</pound>";
		// stunnel start
		echo "	<stunnel>\n
		<global>\n
			<debug>5</debug>\n
		</global>\n
	</stunnel>\n";
		echo "</config>\n";
	}
}
