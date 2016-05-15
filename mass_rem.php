<?php
//
// Mass Remove Downtime Component
//

require_once(dirname(__FILE__) . '/../../common.inc.php');

// Initialization stuff
pre_init();
init_session();

// Grab GET or POST variables and do prereq/auth checks
grab_request_vars();
check_prereqs();
check_authentication(false);

$title = gettext("Nagios XI - Mass Remove Downtime");

do_page_start(array("page_title" => $title), true);
?>

<style type='text/css'>
select {
    font-family: 'Courier New';
}
    td {
        vertical-align: center;
    }

    .alignleft {
        text-align: left;
    }

    .aligncenter {
        text-align: center;
    }

    .plugin_output {
        width: 300px;
        overflow: hidden;
        word-wrap: break-word;
    }

    #massack_wrapper {
        margin: 10px auto;
        width: 1000px;
    }

    #checkAllButton, #submit {
        margin-top: 3px;
    }

    #submit {
        margin-left: 500px;
    }

    div.errorMessage, div.actionMessage {
        width: 400px;
        padding: 10px;
    }

    .stickyhead {
        width: 70px;
    }

    .notifyhead {
        width: 70px;
    }

    .persisthead {
        width: 90px;
    }

    .centertd {
        text-align: center;
    }
</style>
<script src="js/filterlist.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('tr:even').addClass('even');
    });

    var allChecked = false;
    var allCheckedSticky = false;
    var allCheckedNotify = false;
    var allCheckedPersist = false;

    function checkAll(host) {
        if (host == 'all' && allChecked == false) {
            $('.hostcheck:checkbox').each(function () {
                this.checked = 'checked';
            });
            $('.servicecheck:checkbox').each(function () {
                this.checked = 'checked';
            });

            $('#checkAllButton').val('Uncheck All Items');
            allChecked = true;

        }
        else if (host == 'all' && allChecked == true) {
            $('.hostcheck:checkbox').each(function () {
                this.checked = '';
            });
            $('.servicecheck:checkbox').each(function () {
                this.checked = '';
            });
            $('#checkAllButton').val('Check All Items');
            allChecked = false;
        }
        else {
            $('input.' + host).each(function () {
                if (this.checked == '')
                    this.checked = 'checked';
                else
                    this.checked = '';
            });
        }
    }//end checkAll

    function checkAllSticky() {
        if (allCheckedSticky == false) {
            $('input.sticky').each(function () {
                this.checked = 'checked';
            });
            allCheckedSticky = true;
        }
        else {
            $('input.sticky').each(function () {
                this.checked = '';
            });
            allCheckedSticky = false;
        }
    }


    function checkAllNotify() {
        if (allCheckedNotify == false) {
            $('input.notify').each(function () {
                this.checked = 'checked';
            });
            allCheckedNotify = true;
        }
        else {
            $('input.notify').each(function () {
                this.checked = '';
            });
            allCheckedNotify = false;
        }

    }

    function checkAllPersist() {
        if (allCheckedPersist == false) {
            $('input.persist').each(function () {
                this.checked = 'checked';
            });
            allCheckedPersist = true;
        }
        else {
            $('input.persist').each(function () {
                this.checked = '';
            });
            allCheckedPersist = false;
        }

    }

    function checkAlldt() {
        if (allCheckedPersist == false) {
            $('input.dt').each(function () {
                this.checked = 'checked';
            });
            allCheckedPersist = true;
        }
        else {
            $('input.dt').each(function () {
                this.checked = '';
            });
            allCheckedPersist = false;
        }

    }

    function checkTime() {
        if ($('#massack_type').val() == 'acknowledgment' || $('#massack_type').val() == 'both') {
            $('#time').attr('disabled') == true;
            $('.sticky').show();
            $('.notify').show();
            $('.persist').show();

        }
        else {
            $('#time').removeAttr('disabled');
            $('.sticky').hide();
            $('.notify').hide();
            $('.persist').hide();
        }
    }

</script>
<?php //////////////////////////////////////MAIN//////////////////////////////
$submitted = grab_request_var('submitted', false);
$feedback = '';
//display output from command submissions 
if ($submitted) {
    $exec_errors = 0;
    $error_string = '';
    $feedback = massremdown_core_commands();
}

$downtimes = massremdown_get_downtimes();
$html = massremdown_build_downtime_html($downtimes);
print $html;

/////////////////FUNCTIONS/////////////////////////////////

function massremdown_build_downtime_html($downtimes)
{
    if (is_readonly_user(0)) {
        $html = gettext("You are not authorized for this component");
        return $html;
    }

    $html = "
		<div id='downtime_wrapper'>
		<h3>" . gettext('Mass Remove Downtime') . "</h3>
		<div id='downtime_info'>
			<a href='" . htmlentities($_SERVER['PHP_SELF']) . "' title='Update List' />" . gettext("Update List") . "</a><br />
			<p>" . gettext("Use this tool to remove scheduled downtimes.  Commands may take a few moments to take effect on status details.") . "</p>
		</div>		
		<div id='massdt'>
			<form name='myform' id='myform' action='" . htmlentities($_SERVER['PHP_SELF']) . "' method='post'>
			<input type='hidden' name='submitted' value='true' />
            <input type='hidden' name='type' value='removedt' />
			<input type='submit' value='" . gettext("Remove Downtimes") . "' />
			<br /><br />
			<table class='standardtable servicestatustable' id='massdt_table'>
<input type=\"text\" name=\"filterdt\" id=\"filterdt\" style=\"display:inline-block;width:auto;min-width:225px;\" placeholder=\"Filter...\" onKeyUp='myfilter.set(this.value)'><br>";

    $hostcount = 0;
    $html .= '<tr><select size=30 multiple id="downtime" name="downtime[]"';
    $html .= '<option><value="0"></option>';

    foreach ($downtimes as $host) {
	foreach ($host as $downtime) {
		$downtime_value = ($downtime['service_description'] == "") ? "h" : "s";
		if (strlen($downtime['host_name']) >= 21){
			$hostname = substr($downtime['host_name'],0,20) . " ";
		} else {
			$hostname = $downtime['host_name'] . str_repeat('&nbsp;', 20 - strlen($downtime['host_name'])); 
		}
                if (strlen($downtime['service_description']) >= 51){
                        $service = substr($downtime['service_description'],0,50) . " ";
                } else {
                        $service = $downtime['service_description'] . str_repeat('&nbsp;', 50 - strlen($downtime['service_description']));
                }
		$html .= "<option style=\"font-family: 'Courier New'\" value='$downtime_value-{$downtime['downtime_id']}'>$hostname - $service - {$downtime['scheduled_start_time']} - {$downtime['scheduled_end_time']}</option>";
		//$html .= "<option value='$downtime_value-{$downtime['downtime_id']}'>{$downtime['host_name']};{$downtime['service_description']};{$downtime['scheduled_start_time']};{$downtime['scheduled_end_time']}</option>";
	}
	$hostcount++;
    }
    $html .= '</select>
<SCRIPT TYPE="text/javascript">
<!--
var myfilter = new filterlist(document.myform.downtime);
//-->
</SCRIPT>
</tr>';

    $html .= "</table><br /><input type='submit' value='" . gettext("Remove Downtimes") . "' /></form>";
    $html .= "</div></div></body></html>";

    if ($hostcount == 0)
        $html = "<h1>No downtimes exist</h1>";
    return $html;
}

function feedback_message($msg, $error = false)
{
    $class = ($error) ? 'errorMessage' : 'actionMessage';
    $html = "<div class='{$class}'>
				{$msg}
			</div>";
    return $html;
}

function massremdown_del_downtime_exec_script($id)
{
    global $cfg;
    global $exec_errors;
    global $error_string;
    //split to determine host or service
    $splitid = explode("-", $id);
    //security measures 
    $dt_id = escapeshellcmd($splitid[1]);
    //$dt_id = $id;
    if ($splitid[0] == "h")
        $dtCommand = "DEL_HOST_DOWNTIME";
    else
        $dtCommand = "DEL_SVC_DOWNTIME";
    $pipe = $cfg['component_info']['nagioscore']['cmd_file'];
    $now = time();
    $dtString = "/bin/echo '[$now] $dtCommand;$dt_id\n' > $pipe";
    $bool = exec($dtString);
    //handle errors
    if ($bool > 0) {
        $exec_errors++;
        //$error_string .=$output.'<br />';
    }
}

function massremdown_get_downtimes()
{
    $backendargs = array();
    $backendargs["cmd"] = "getscheduleddowntime";

    $xml = get_backend_xml_data($backendargs);
    $downtimes = array();
    if ($xml) {
        foreach ($xml->scheduleddowntime as $x) {
            $downtime_id = "$x->internal_id";
            $host_name = "$x->host_name";
            $service_description = "$x->service_description";
            $scheduled_start_time = "$x->scheduled_start_time";
            $scheduled_end_time = "$x->scheduled_end_time";
            $duration = "$x->duration";

            $downtimes[$host_name][] = array('downtime_id' => $downtime_id, 'host_name' => $host_name, 'service_description' => $service_description, 'scheduled_start_time' => $scheduled_start_time, 'scheduled_end_time' => $scheduled_end_time, 'duration' => $duration);
        }
    } else echo "can't find host xml!";
    return $downtimes;
}

function massremdown_core_commands()
{
    global $exec_errors;
    global $error_string;

    //print_r($_POST);

    $hosts = grab_request_var('hosts', array());
    $services = grab_request_var('services', array());
    $sticky = grab_request_var('sticky', array());
    $notify = grab_request_var('notify', array());
    $persist = grab_request_var('persist', array());
    $message = grab_request_var('comment', '');
    $mode = grab_request_var('type', 'both');
    $time = grab_request_var('time', 0);
    $message = grab_request_var('comment', '');
    $username = get_user_attr($_SESSION['user_id'], 'name');
    $username = $username == '' ? $_SESSION['username'] : $username; //default to session username

    //bail if missing required values
    if (count($hosts) == 0 && count($services) == 0 && $mode != "removedt")
        return feedback_message('You must specify at least one service', true);
    if ($message == '' && $mode != "removedt")
        return feedback_message('You must specify a comment', true);

    //make sure script is executable
    //if(!is_executable(dirname(__FILE__).'/ack_Host.sh')) exec('chmod +x ack_Host.sh');
    if ($mode == "removedt") {
        $downtimes = grab_request_var('downtime', array());
        foreach ($downtimes as $id) {
	    //echo $id;
            massremdown_del_downtime_exec_script($id);
        }
    }
    //return feedback for front-end
    if ($exec_errors == 0)
        return feedback_message(gettext('Commands processed successfully! Your command submissions may take a few moments to update in the display.'));
    else
        return feedback_message("$exec_errors " . gettext("errors were encountered while processing these commands") . " <br />$error_string", true);
}

