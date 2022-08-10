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

namespace Tygh\UpgradeCenter\Connectors\AltteamClonePromotion;

use Tygh\Addons\SchemesManager;
use Tygh\Http;
use Tygh\Registry;
use Tygh\Settings;
use Tygh\Tools\Url;
use Tygh\UpgradeCenter\Connectors\BaseAddonConnector;
use Tygh\UpgradeCenter\Connectors\IConnector;

class Connector extends BaseAddonConnector implements IConnector
{
    const ACTION_PARAM = 'dispatch';
    const ACTION_CHECK_UPDATES = 'updates.check';
    const ACTION_DOWNLOAD_PACKAGE = 'updates.download_package';

    protected $addon_id = 'altteam_clone_promotion';
    protected $addon_version;
    protected $product_url;

    public function __construct()
    {
        parent::__construct();

        $this->updates_server = 'https://updates.example.com';

        $addon = SchemesManager::getScheme($this->addon_id);

        $this->addon_version = $addon->getVersion() ? $addon->getVersion() : '1.0';
        $this->license_number = (string) Settings::instance()->getValue('license_number', $this->addon_id);

        $this->product_name = PRODUCT_NAME;
        $this->product_version = PRODUCT_VERSION;
        $this->product_build = PRODUCT_BUILD;
        $this->product_edition = PRODUCT_EDITION;
        $this->product_url = Registry::get('config.current_location');
    }

    public function getConnectionData()
    {
        $data = [
            self::ACTION_PARAM => self::ACTION_CHECK_UPDATES,
            'addon_id'         => $this->addon_id,
            'addon_version'    => $this->addon_version,
            'license_number'   => $this->license_number,
            'product_name'     => $this->product_name,
            'product_version'  => $this->product_version,
            'product_build'    => $this->product_build,
            'product_edition'  => $this->product_edition,
            'product_url'      => $this->product_url,
        ];

        $headers = [];

        return [
            'method'  => 'get',
            'url'     => $this->updates_server,
            'data'    => $data,
            'headers' => $headers,
        ];
    }

    public function downloadPackage($schema, $package_path)
    {
        $download_url = new Url($this->updates_server);

        $download_url->setQueryParams(array_merge($download_url->getQueryParams(), [
            self::ACTION_PARAM => self::ACTION_DOWNLOAD_PACKAGE,
            'package_id'       => $schema['package_id'],
            'addon_id'         => $this->addon_id,
            'license_number'   => $this->license_number,
        ]));

        $download_url = $download_url->build();

        $request_result = Http::get($download_url, [], [
            'write_to_file' => $package_path,
        ]);

        if (!$request_result || strlen($error = Http::getError())) {
            $download_result = [false, __('text_uc_cant_download_package')];

            fn_rm($package_path);
        } else {
            $download_result = [true, ''];
        }

        return $download_result;
    }
}