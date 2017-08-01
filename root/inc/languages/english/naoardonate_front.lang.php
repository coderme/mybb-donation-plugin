<?php

#  Naoar Donation plugin v3.0 for mybb 1.4x, 1.6x
#  This file will be used in frontend/ donate.php
#  Copyright(c) 2015  """ https://coderme.com """
#
#  This is a free software, you can redistribute it freely provided that you keep my credits, files of this module and this notice unchanged.
#
#  This module released UNDER THE TERMS OF CREATIVE COMMONS - Attribution No Derivatives("cc by-nd"). THIS MODULE IS PROTECTED BY COPYRIGHT AND/OR OTHER APPLICABLE LAW. ANY USE OF THIS MODULE OTHER THAN AS AUTHORIZED UNDER THIS LICENSE OR COPYRIGHT LAW IS PROHIBITED
#  For Details visit: http://creativecommons.org/licenses/by-nd/3.0/legalcode  or http://creativecommons.org/licenses/by-nd/3.0/


$l['naoardonate_front_top_title'] = "Top Donors";
$l['naoardonate_front_email'] = "Email";
$l['naoardonate_front_donate_title'] = "Donate";
$l['naoardonate_front_custom'] = "Custom amount";
$l['naoardonate_front_note'] = "Note";
$l['naoardonate_front_optional'] = "Optional";
$l['naoardonate_front_required'] = "Required";
$l['naoardonate_front_writenote'] = "Write a note";
$l['naoardonate_front_charsleft'] = " Chars left";


$l['naoardonate_front_goto'] = "Go to";


$l['naoardonate_front_online'] = "Donating at <a href=\"{1}\">Donation page</a>";

$l['naoardonate_front_thanku'] = "Really thank you for helping me to continue maintain this website<br /> your donation highly appreciated<br /> Admin";
$l['naoardonate_front_thanku_title'] = "Thank you!";
$l['naoardonate_front_thanku_error'] = "ooh! something went wrong, have you come from donation page? Oh! thank you :)";

$l['naoardonate_front_error_notinstalled'] = "Oh! how comes?!! Naoar Donation is not installed yet";
$l['naoardonate_front_error_disabled'] = "Donations is disabled by the Admin";

$l['naoardonate_front_error_notready'] = "Donations settings are not set the right way, please contact the Admin";
$l['naoardonate_front_error_blockedgroups'] = "Members of your group cannot make donations";
$l['naoardonate_front_error_noguests'] = 'Guests cannot make donations, please <a href="member.php?action=register">Register</a> or <a href="member.php?action=login">Login</a>';


$l['naoardonate_front_error_invalidcaptcha'] = "Invalid image verification code!,Please enter the code exactly as is.";
$l['naoardonate_front_error_cannotviewtop'] = "You don&#39;t have permission to view top donors";

$l['naoardonate_front_error_invalidamount'] = "Invalid amount!, please enter a valid amount number";
$l['naoardonate_front_error_toosmallamount'] = "Too small amount!, amount cannot be less than {1}";
$l['naoardonate_front_error_invalidemail'] = "Invalid email address!";
$l['naoardonate_front_error_minimumzero'] = "Invaild donation amount";
$l['naoardonate_front_error_minimum'] = "Donation amount cannot be less than {1}";
$l['naoardonate_front_error_notsupportedpayment_method'] = "{1} is currently not supported";
$l['naoardonate_front_error_nopayment_method'] = "Payment Method field cannot be empty";
$l['naoardonate_front_error_unsupportedcurency'] = "Unsupported currency, please select from the list an one corresponds with {1}";
$l['naoardonate_front_error_onlyusdoreuro'] = 'Unsupported currency, please select either Euro or USD';

$l['naoardonate_front_error_namerequired'] = "Name is required, please enter your name";
$l['naoardonate_front_error_nametooshort'] = "Name is too short, minmum {1} characters";
$l['naoardonate_front_error_emailrequired'] = "Email is required, please enter your email";
$l['naoardonate_front_error_captchatooshort'] = "Code you entered for Image verification is too short";
$l['naoardonate_front_error_emptycaptcha'] = "Image verification is required, please enter a code exactly as it appears in the image";
$l['naoardonate_front_error_bademail'] = "Invalid email, please enter a valid email address";



$l['naoardonate_front_aboutu'] = "About you";
$l['naoardonate_front_donationdetails'] = "Donation Details";
$l['naoardonate_front_donationnote'] = "Donation Note";
$l['naoardonate_front_donationform'] = "Donation form";
$l['naoardonate_front_minimum'] = "Minimum {1}";


$l['naoardonate_front_donation'] = "Donation: ";
$l['naoardonate_front_redirect'] = "now redirecting...";
$l['naoardonate_front_continuebutton'] = "Click here to continue";
$l['naoardonate_front_finiishbutton'] = "Finish";

$l['naoardonate_front_waitingyouraction'] = "Unconfirmed Donation(s) waiting your action";
$l['naoardonate_front_formoreinfo'] = "For more information please";
$l['naoardonate_front_clickhere'] = "Click Here!";


# currencies
$l['naoardonate_front_currencies_supported_by']  = 'Currencies supported by {1}';
$l['naoardonate_front_offline_payment_methods']  = '{1} details';





$l['naoardonate_front_unconfirmed_emailsubject'] = '{1} Unconfirmed Donation(s)';
$l['naoardonate_front_unconfirmed_emailhtmlmessage'] = 'Hello Admin\n <br /> There are {1} Donations waiting your action, please <a href="{2}" target="_blank" title="in a new page">Login to your Admin Control Panel</a> in order to manage these donations\n<br />Sweet Times\n<br />CoderMe.com \n<br /> ---------------------------------------\n<br /> In Order to Stop recieving Alert Emails Regarding Unconfirmed Donations please edit plugins&#39; settings';
$l['naoardonate_front_unconfirmed_emailtextmessage'] = 'Hello Admin\n There are {1} Donations waiting your action, please Go to {2} in order to manage donations\n Sweet Times\n CoderMe.com\n \n ---------------------------------------\n In Order to Stop recieving Alert Emails Regarding Unconfirmed Donations please edit plugin settings';


?>
