<?php
	if (!isset($_POST['id'])) return;
	$options 	= get_option('aec_options');
	$event 		= $this->get_event($_POST['id']);
	
	// split date/time into form fields	
	$start_date = $this->date_convert($event->start, AEC_WP_DATE_TIME_FORMAT, AEC_WP_DATE_FORMAT);
	$start_time = $this->date_convert($event->start, AEC_WP_DATE_TIME_FORMAT, AEC_WP_TIME_FORMAT);
	$end_date 	= $this->date_convert($event->end, AEC_WP_DATE_TIME_FORMAT, AEC_WP_DATE_FORMAT);
	$end_time 	= $this->date_convert($event->end, AEC_WP_DATE_TIME_FORMAT, AEC_WP_TIME_FORMAT);

	$out = '<ul>';
	$out .= '<li><h3>';

	if ($start_date != $end_date) {
		// multiple day event, spanning all day
		if ($event->allDay) {
			$out .= $start_date;
			$out .= ' - ' . $end_date;
			$event->start = $start_date;
			$event->end = $end_date;
		
		// multiple day event, not spanning all day
		} else {
			$out .= $start_date . ' ' . $start_time;
			$out .= '<br>' . $end_date . ' ' . $end_time;
		}
		
	} else {
			
		// one day event, spanning all day
		if ($event->allDay) {
			$out .= $start_date;
			$event->start = $start_date;
			$event->end = $end_date;
		
		// one day event, spanning hours
		} else {
			$out .= $start_date;
			$out .= '<br>' . $start_time . ' - ' . $end_time;
		}
	}
	$out .= '</h3>';
	$duration = $this->process_duration($event);
	$out .= '<span class="duration round5">' . __('Duration', AEC_PLUGIN_NAME) . '<br>' . $duration . '</span>';
	$out .= '</li>';
	$out .= '<li>' . stripslashes($event->description) . '</li>';

	if (!empty($event->venue) || !empty($event->address) ||
		!empty($event->city) || !empty($event->state) ||
		!empty($event->zip) ) {

		$out .= '<li><h3>' . __('Location', AEC_PLUGIN_NAME) . '</h3>';
		if (!empty($event->venue)) $out .= stripslashes($event->venue) . '<br>';
		$city 	= (!empty($event->city)) ? stripslashes($event->city) . ', ' : '';
		$state 	= (!empty($event->state)) ? strtoupper($event->state) . ', ' : '';
		$zip	= (!empty($event->zip)) ? stripslashes($event->zip) : '';
		$csz 	= $city . $state . $zip;
		if (!empty($event->address)) {
			$address = stripslashes($event->address);
			$out .= $address . '<br>' . $csz;
		} else {
			$out .= $csz;
		}
		$out .= '</li>';

		// google map link
		if ($options['show_map_link']) {
			$out .= '<li>';
			$out .= '<a href="http://maps.google.com/?q=' . urlencode($address . ' ' . $csz) . '" class="round5 cat' . $event->category_id . '" target="_blank">' . __('View Map', AEC_PLUGIN_NAME) . '</a>';
			$out .= '</li>';
		}
	}

	if (!empty($event->contact) || !empty($event->contact_info)) {
		$out .= '<li><h3>' . __('Contact Information', AEC_PLUGIN_NAME) . '</h3>';
		if (!empty($event->contact)) $out .= stripslashes($event->contact) . '<br>';
		if (!empty($event->contact_info)) $out .= stripslashes($event->contact_info);
		$out .= '</li>';
	}

	if ($event->access || $event->rsvp) {
		$out .= '<hr>';
		if ($event->access) $out .= '<li>' . __('This event is accessible to people with disabilities.', AEC_PLUGIN_NAME) . '</li>';
		if ($event->rsvp) $out .= '<li>' . __('Please register with the contact person for this event.' , AEC_PLUGIN_NAME) . '</li>';
	}

	$org = get_userdata($event->user_id);
	if (!empty($org->organization)) {
			$out .= '<li class="presented">' . __('Presented by', AEC_PLUGIN_NAME) . ' ';
		if (!empty($org->user_url)) {
			$out .= '<a href="' . $org->user_url . '" target="_blank">' . stripslashes($org->organization) . '</a>';
		} else {
			$out .= stripslashes($org->organization);
		}
		$out .= '</li>';
	}

	if (!empty($event->link)) $out .= '<li><a href="' . $event->link . '" class="link cat' . $event->category_id . '" target="_blank">' . __('Event Link', AEC_PLUGIN_NAME) . '</a></li>';

	$out .= '</ul>';

	$categories = $this->get_categories();
	foreach ($categories as $category) {
		if ($category->id == $event->category_id) $cat = $category->category;
	}

	$output = array(
		'title'		=> $event->title . ' (' . $cat . ')',
		'content'	=> $out
	);
	header( "Content-Type: application/json" );
	echo json_encode($output);
	exit;
?>