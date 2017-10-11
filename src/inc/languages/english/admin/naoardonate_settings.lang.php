<?php

/**
 *
 * CoderMe Donation plugin
 * Copyright 2017 CoderMe.com, All Rights Reserved
 *
 * Website: https://coderme.com
 * Home:    https://red.coderme.com/mybb-donation-plugin
 * License: https://red.coderme.com/mybb-donation-plugin#license
 * Version: 4.0.1
 *
 **/


$l['naoardonate_settings_intro'] = 'Personalize Naoar Donation plugin to suit your needs :)';
$l['naoardonate_settings_onoff'] = 'Accept Donations?';
$l['naoardonate_settings_onoff_desc'] = '<span style="color:#9900FF;font-size:small">Choose whether you like to accept donations from people or not</span>';
$l['naoardonate_settings_enablebar'] = 'Enable Donation goal&#39;s bar?';
$l['naoardonate_settings_enablebar_desc'] = '<span style="color:#9900FF;font-size:small">Donation goal&#39;s bar if enabled will appear in different pages just below the top menu</span>';
$l['naoardonate_settings_reason'] = 'Reason for collecting money?';
$l['naoardonate_settings_reason_desc'] = '<span style="color:#9900FF;font-size:small">This will appear just below the donation bar, leave it blank to disable it</span>';
$l['naoardonate_settings_target'] = 'Enter target amount:';
$l['naoardonate_settings_target_desc'] = '<span style="color:#9900FF;font-size:small">the target amount you want to reach in certain period of time</span>';
$l['naoardonate_settings_duration'] = 'Number of days needed to finish the goal:';
$l['naoardonate_settings_duration_desc'] = '<span style="color:#9900FF;font-size:small">how many days goal needs to achieved</span>';
$l['naoardonate_settings_ifreached'] = 'Hide Donation bar if target reached or time finished?';
$l['naoardonate_settings_ifreached_desc'] = '<span style="color:#9900FF;font-size:small">if donation goal achieved or time of goal expired this option can hide donation bar for you.</span>';
$l['naoardonate_settings_amount'] = 'Amount of money donor can choose:';
$l['naoardonate_settings_amount_desc'] = '<span style="color:#9900FF;font-size:small">amount of money accepted, separate multiple amount by comma for example <br />
<strong style="color: black; ">0,7,99,301</strong><br />0 value means the users can enter their own amount, the lowest value(not zero) will be the minimum accepted value, for instance the preceding example means the minimum accepted value is 7<br /> if you want to accept custom amount and not less than certain amount enter two values first zero and then the minimum accepted amount like 0,7<br /><br />
<strong>NEW:</strong> you can enter real numbers with decimal point like <b>9.95</b> also text is supported but must be enclosed with square brackets <b>[ ]</b> character, this text will be displayed as is INSTEAD of the donation amount in the select box, consider the following advanced example:<br /><strong style="color: black; ">0,  [ Basic donation - 70 EUR ] 70.00, [  Gold donation - 700 EUR ] 700.00, [ Platinum donation - 7000 EUR ] 7000.00</strong></span>';
$l['naoardonate_settings_currency'] = 'Currency:';
$l['naoardonate_settings_currency_desc'] = '<span style="color:#9900FF;font-size:small">choosing unsupported currency(By certain payment processor) will result in failure of donation process, please select a currency that matches your payment processor..,also note that PayPal allow Malaysian Ringgit (MYR) ONLY for Malaysian users and Brazilian Real (BRL) ONLY for Brazilian users , more information about PayPal Currencies <a href="https://www.paypal.com/us/cgi-bin/?cmd=p/sell/mc/mc_intro-outside" target="_blank" style="color:blue;text-decoration:underline" title="new page" > Here! </a> or see <a href="https://www.payza.com/features/available-currencies" target="_blank" style="color:blue;text-decoration:underline" title="new page"> Payza Currencies </a> or <a href="http://help.skrill.com/en/Article/business/my-skrill-money/what-currencies-can-customers-pay-in" target="_blank" style="color:blue;text-decoration:underline" title="new page"> Skrill Currencies </a> however, to ensure compatibility  with all payment processors you may choose
either Euro or US dollar, as these currencies are available all the time</span>';
$l['naoardonate_settings_payment_method'] = 'Accept donation through:';
$l['naoardonate_settings_payment_method_desc'] = '<span style="color:#9900FF;font-size:small">your prefered payment processor to accept donations</span>';
$l['naoardonate_settings_payment_method_LR'] = 'LibertyReserve Account:';
$l['naoardonate_settings_payment_method_LR_desc'] = '<span style="color:#9900FF;font-size:small">Donations will sent to this account if LibertyReseve chosen, , Don&#39;t have an account? then <a href="https://www.libertyreserve.com/en/registration" style="color:blue;text-decoration:underline"  target="_blank" title="new page">Get your FREE account from Here</a></span>';
$l['naoardonate_settings_payment_method_AP'] = 'Payza Account:';
$l['naoardonate_settings_payment_method_AP_desc'] = '<span style="color:#9900FF;font-size:small">Donations will sent to this account if Payza chosen, Don&#39;t have an account? then <a href="https://secure.payza.com/?1cSIX3YMzAHE6df7fltAQA%3d%3d" style="color:blue;text-decoration:underline" target="_blank" title="new page">Get your FREE account from Here</a></span>';
$l['naoardonate_settings_cannotviewtop'] = 'Groups cannot view top donors:';
$l['naoardonate_settings_cannotviewtop_desc'] = '<span style="color:#9900FF;font-size:small">Select groups cannot see topdonors when visiting donate.php?action=top_donors</span>';
$l['naoardonate_settings_captcha'] = 'Enable Image verification?';
$l['naoardonate_settings_captcha_desc'] = '<span style="color:#9900FF;font-size:small">Choose if Image verification disabled or not and for who its enabled, choosing &quot;Always&quot; mean its active for members and guests</span>';
$l['naoardonate_settings_donormsg'] = 'Can a donor leave a message while donation?';
$l['naoardonate_settings_donormsg_desc'] = '<span style="color:#9900FF;font-size:small">if &quot;Yes&quot; donors can enter a message while donating</span>';
$l['naoardonate_settings_currency_any'] = 'Any Supported Currency';
$l['naoardonate_settings_disabled'] = 'Disabled';
$l['naoardonate_settings_guestonly'] = 'Guests Only';
$l['naoardonate_settings_memberonly'] = 'Members Only';
$l['naoardonate_settings_always'] = 'Always';
$l['naoardonate_settings_get_payment_method_account'] = 'Sign up with {1} || New Page';
$l['naoardonate_settings_payment_method_SK'] = 'Skrill Account:';
$l['naoardonate_settings_payment_method_SK_desc'] = '<span style="color:#9900FF;font-size:small">Donations will sent to this account if Skrill chosen, Don&#39;t have an account? then <a href="https://www.skrill.com/en/?rid=19686949" style="color:blue;text-decoration:underline" target="_blank" title="new page">Get your FREE account from Here</a></span>';

$l['naoardonate_settings_payment_method_bank'] = 'Bank/Wire tranfser';
$l['naoardonate_settings_payment_method_bank_desc'] = '<span style="color:#9900FF;font-size:small">Your bank account details including  your name, your account number, bank name, bank address,<a href="http://en.wikipedia.org/wiki/SWIFT">bank SWIFT code</a>. and bank contact info. You may need additional info depending on your bank, please ask your bank</span>';

$l['naoardonate_settings_payment_method_WU'] = 'Western Union';
$l['naoardonate_settings_payment_method_WU_desc'] = '<span style="color:#9900FF;font-size:small">Your name ( as it appears in your id ) and your address</span>';

$l['naoardonate_settings_payment_method_PP'] = 'PayPal Account:';
$l['naoardonate_settings_payment_method_PP_desc'] = '<span style="color:#9900FF;font-size:small">Donations will sent to this account if PayPal chosen, Don&#39;t have an account? then <a href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_registration-run"  style="color:blue;text-decoration:underline" target="_blank" title="new page">Get your FREE account from Here</a></span>';
$l['naoardonate_settings_from'] = 'Accept Donations From:';
$l['naoardonate_settings_from_desc'] = '<span style="color:#9900FF;font-size:small">Select the groups that are allowed to donate ( and see Donation links\bar if its enabled )</span>';
$l['naoardonate_settings_unmovable'] = 'Unmovable Groups:';
$l['naoardonate_settings_unmovable_desc'] = '<span style="color:#9900FF;font-size:small">Select  the groups you don&#39;t want to move them to donation group even after their donations have been confirmed. ( consider this is an exception to the setting above )</span>';
$l['naoardonate_settings_donorsgroup'] = 'Donors&#39; Group';
$l['naoardonate_settings_donorsgroup_desc'] = '<span style="color:#9900FF;font-size:small">choose a group to move donors to it, this will be done upon donation&#39;s confirmation and only if donor is already a member, default is &quot;No Change&quot; which means not to move donors</span>';
$l['naoardonate_settings_donors_nochange'] = 'No Change';
$l['naoardonate_settings_disabled'] = 'Disabled';
$l['naoardonate_settings_email'] = 'Email';
$l['naoardonate_settings_notice'] = 'Notice';

$l['naoardonate_settings_newgoal'] = 'Is this a new Goal?';
$l['naoardonate_settings_newgoal_desc'] = '<span style="color:#9900FF;font-size:small">choosing &quot;Yes&quot; will reset &quot;&quot;&quot; the counter of recieved donations &quot;&quot;&quot; / if you have already reached a goal and want to add another goal just choose &quot;Yes&quot; | &quot;No&quot; will keep this goal settings unchanged &quot;&quot;&quot; counter will not be reset &quot;&quot;&quot; </span>';

$l['naoardonate_settings_info'] = 'Enable Name/Email fields at donating form?';
$l['naoardonate_settings_info_desc'] = '<span style="color:#9900FF;font-size:small">Choose if you want to show name and email fields for: Guests, Members or always</span>';

$l['naoardonate_settings_bar_width'] = 'Donation container/bar width';
$l['naoardonate_settings_bar_width_desc'] = '<span style="color:#9900FF;font-size:small">Default value is 851/605, these values in pixels..: 1st value is the <span title="html element that contains the bar">container</span> width and the second is the bar width,  please enter only numbers in the text box</span>';


$l['naoardonate_settings_info_required'] = 'Is Name/Email required for donating?';
$l['naoardonate_settings_info_required_desc'] = '<span style="color:#9900FF;font-size:small"> Choose &quot;Yes&quot; if name/email is a MUST, or &quot;No&quot; if they are optional</span>';


$l['naoardonate_settings_recievedmsg'] = 'Amount recieved Msg';
$l['naoardonate_settings_recievedmsg_desc'] = '<span style="color:#9900FF;font-size:small">Enter the msg(HTML code allowed) to show for users the amount reached, {1} will show the percentage of recieved amount, you can delete it completely, no harm :)</span>';


$l['naoardonate_settings_recievedmsg_100'] = 'Goal reached Msg';
$l['naoardonate_settings_recievedmsg_100_desc'] = '<span style="color:#9900FF;font-size:small">Msg(HTML code allowed) to show when the recieved amount reached 100% of the goal, this msg will show instead of the above when Goal is reached consider it as a thank you msg.. can be empty too</span>';



$l['naoardonate_settings_unconfirmednotice'] = 'Unconfirmed Donations Alert';
$l['naoardonate_settings_unconfirmednotice_desc'] = '<span style="color:#9900FF;font-size:small">&quot;Notice&quot; =&gt; a notice will appear in the front end just like the default pm notice (this is only for the admin who have the permission to browse donors, &quot;Email&quot; =&gt; recieve email when there is unconfirmed donation (emails will be sent to the admin&#39;s email), &quot;Disabled&quot; no action</span>';
$l['naoardonate_settings_googleanalytics'] = 'Google Analytics';
$l['naoardonate_settings_googleanalytics_dec'] = '<span style="color:#9900FF;font-size:small">Paste your Google Analytics&#39;s Code here, in order to track donors. please note only Asynchronous tracking supported, this code will be inserted before &quot;&lt;/head&gt;&quot;, leave it blank to  disable it. For more information about setting up tracking code <a href="http://www.google.com/support/analytics/bin/answer.py?hl=en_US&answer=174090&utm_id=ad" target="_blank" style="color:blue;text-decoration:underline" >Click Here</a> or to get Google Analytics&#39;s Account <a href="https://www.google.com/analytics/" target="_blank" style="color:blue;text-decoration:underline" > Click Here </a></span></span>';




?>
