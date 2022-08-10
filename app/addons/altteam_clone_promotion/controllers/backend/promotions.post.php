<?php

/*****************************************************************************
 * This is a commercial software, only users who have purchased a  valid
 * license and accepts the terms of the License Agreement can install and use  
 * this program.
 *----------------------------------------------------------------------------
 * @copyright  LCC Alt-team: https://www.alt-team.com
 * @module     "Alt-team: Clone promotion"
 * @license    https://www.alt-team.com/addons-license-agreement.html
****************************************************************************/

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

// promotion data
if ($mode == 'update') {
	Registry::set('navigation.dynamic.actions.clone', array (
		'href' => "promotions.clone?promotion_id=$_REQUEST[promotion_id]",
		'meta' => '',
		'target' => '',
	));
}

if ($mode == 'clone') {
	if (AREA == 'A') {
		if (!empty($_REQUEST['promotion_id'])) {
			$promotion_id = $_REQUEST['promotion_id'];
			
			// Clone main data
			$data = db_get_row("SELECT * FROM ?:promotions WHERE promotion_id = ?i", $promotion_id);
			unset($data['promotion_id']);
			$data['status'] = 'D';
			$pid = db_query("INSERT INTO ?:promotions ?e", $data);

			// Clone descriptions
			$data = db_get_array("SELECT * FROM ?:promotion_descriptions WHERE promotion_id = ?i", $promotion_id);
			foreach ($data as $v) {
				$v['promotion_id'] = $pid;
				$v['name'] .= ' [CLONE]';
				db_query("INSERT INTO ?:promotion_descriptions ?e", $v);
			}
			
			if (!empty($pid)) {
				fn_set_notification('N', __('notice'), __('altteam_clone_promotion.promotion_cloned'));
			}

			return array(CONTROLLER_STATUS_REDIRECT, "promotions.update?promotion_id=$pid");
		}
	}
}