<?php

	//application details
		$apps[$x]['name'] = "Fusion Bells";
		$apps[$x]['uuid'] = "4321b85d-6b0d-4e94-ab49-72ea3cac91b1";
		$apps[$x]['category'] = "Switch";;
		$apps[$x]['subcategory'] = "";
		$apps[$x]['version'] = "";
		$apps[$x]['license'] = "Mozilla Public License 1.1";
		$apps[$x]['url'] = "http://www.fikesmedia.com/";
		$apps[$x]['description']['en-us'] = "Integrated multicast ip bell system.";
		$apps[$x]['description']['es-cl'] = "";
		$apps[$x]['description']['es-mx'] = "";
		$apps[$x]['description']['de-de'] = "";
		$apps[$x]['description']['de-ch'] = "";
		$apps[$x]['description']['de-at'] = "";
		$apps[$x]['description']['fr-fr'] = "";
		$apps[$x]['description']['fr-ca'] = "";
		$apps[$x]['description']['fr-ch'] = "";
		$apps[$x]['description']['pt-pt'] = "";
		$apps[$x]['description']['pt-br'] = "";

	//permission details
		$apps[$x]['permissions'][0]['name'] = "system_status_sofia_status";
		$apps[$x]['permissions'][0]['menu']['uuid'] = "b7aea9f7-d3cf-711f-828e-46e56e2e5328";
		$apps[$x]['permissions'][0]['groups'][] = "superadmin";

		$apps[$x]['permissions'][1]['name'] = "system_status_sofia_status_profile";
		$apps[$x]['permissions'][1]['menu']['uuid'] = "b7aea9f7-d3cf-711f-828e-46e56e2e5328";
		$apps[$x]['permissions'][1]['groups'][] = "superadmin";

		$apps[$x]['permissions'][2]['name'] = "sip_status_switch_status";
		$apps[$x]['permissions'][2]['groups'][] = "superadmin";

?>