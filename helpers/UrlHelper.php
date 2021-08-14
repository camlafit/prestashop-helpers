<?php
/**
 * Helper classes for PrestaShop CMS.
 *
 * @author    Maksim T. <zapalm@yandex.com>
 * @copyright 2018 Maksim T.
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/zapalm/prestashop-helpers GitHub
 * @link      https://prestashop.modulez.ru/en/tools-scripts/53-helper-classes-for-prestashop.html Homepage
 */

namespace zapalm\prestashopHelpers\helpers;

use Configuration;
use Context;
use LogicException;
use Tools;

/**
 * URL helper.
 *
 * @version 0.11.0
 *
 * @author Maksim T. <zapalm@yandex.com>
 */
class UrlHelper
{
    const PARAM_UTM_SOURCE   = 'utm_source';
    const PARAM_UTM_MEDIUM   = 'utm_medium';
    const PARAM_UTM_CAMPAIGN = 'utm_campaign';
    const PARAM_UTM_CONTENT  = 'utm_content';
    const PARAM_UTM_TERM     = 'utm_term';
    const PARAM_UTM_REFERRER = 'utm_referrer';

    /**
     * Returns UTM labels that are generated by specified params.
     *
     * @param string      $source   The source or the referrer (e.g. google, newsletter).
     * @param string      $medium   The marketing medium (e.g. cpc, banner, email).
     * @param string|null $campaign The campaign name, for example, a product, a promo code, or a slogan (e.g. spring_sale).
     * @param string|null $content  The campaign content (use to differentiate ads).
     * @param string|null $term     The campaign term to identify the paid keywords.
     * @param string|null $referrer The extra referrer. This is the additional parameter to allow Yandex.Metrica (may be also others) to correctly detect the source of a click-through when there is a JavaScript redirect, or when users navigate to your site over the HTTP protocol from a site that is only available over HTTPS.
     *
     * @return string[] The array of UTM labels associated with their values.
     *
     * @link https://effinamazing.com/blog/dummies-guide-utm-tracking/ Description in English.
     * @link http://convert.ua/blog/ppc/utm-parameters/ Description in Russian.
     *
     * @see getUtmLabelsQuery() To get URL-encoded query string of UTM labels.
     * @see getUtmLabelsRequest() To get UTM labels from the current request.
     * @see array_filter() To remove UTM labels with empty values.
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public static function getUtmLabels($source, $medium, $campaign = null, $content = null, $term = null, $referrer = null)
    {
        return [
            static::PARAM_UTM_SOURCE   => $source,
            static::PARAM_UTM_MEDIUM   => $medium,
            static::PARAM_UTM_CAMPAIGN => $campaign,
            static::PARAM_UTM_CONTENT  => $content,
            static::PARAM_UTM_TERM     => $term,
            static::PARAM_UTM_REFERRER => $referrer,
        ];
    }

    /**
     * Returns URL-encoded query string of UTM labels.
     *
     * @param array $utmLabels The array of UTM labels.
     *
     * @return string URL-encoded query string.
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public static function getUtmLabelsQuery(array $utmLabels)
    {
        return http_build_query(array_filter($utmLabels));
    }

    /**
     * Returns UTM labels from the current request.
     *
     * @return array Key-Value array with UTM labels.
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public static function getUtmLabelsRequest()
    {
        $params = static::getUtmLabels('', '');
        foreach ($params as $key => $value) {
            $params[$key] = Tools::getValue($key);
        }

        return array_filter($params);
    }

    /**
     * Returns an URL to the back-office (admin page).
     *
     * It's usable only in the context of the back-office.
     *
     * @return string The URL with trailing slash.
     *
     * @throws LogicException If called not in the back-office context.
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public static function getAdminUrl()
    {
        if (false === defined('_PS_ADMIN_DIR_')) {
            throw new LogicException();
        }

        return Context::getContext()->shop->getBaseURL(true) . basename(_PS_ADMIN_DIR_) . '/';
    }

    /**
     * Returns an URL to the upload directory.
     *
     * @return string
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public static function getUploadUrl()
    {
        return Context::getContext()->shop->getBaseURL(true) . 'upload/';
    }

    /**
     * Returns an URL to the download directory.
     *
     * @return string
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public static function getDownloadUrl()
    {
        return Context::getContext()->shop->getBaseURL(true) . 'download/';
    }

    /**
     * Returns an URL to a module configuration page.
     *
     * @param string $moduleName The module name (for example: `gsitemap`).
     *
     * @return string The URL.
     *
     * @throws LogicException If a module name is invalid.
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public static function getModuleConfigureUrl($moduleName)
    {
        if (false === ValidateHelper::isModuleName($moduleName)) {
            throw new LogicException();
        }

        return Context::getContext()->link->getAdminLink('AdminModules') . '&configure=' . $moduleName;
    }

    /**
     * Returns a domain name of a shop.
     *
     * This is the analog of Tools::getShopDomain(), but improved.
     *
     * @param bool $appendHttpProtocol       Whether to return the domain name with HTTP protocol.
     * @param bool $convertSpecialCharacters Whether to convert special characters to HTML entities.
     *
     * @return string
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public static function getShopDomain($appendHttpProtocol = false, $convertSpecialCharacters = false)
    {
        $domain = Configuration::get('PS_SHOP_DOMAIN');
        if (false === $domain) {
            $domain = static::getHost();
        }

        if ($convertSpecialCharacters) {
            $domain = htmlspecialchars($domain, ENT_COMPAT, 'UTF-8');
        }

        if ($appendHttpProtocol) {
            $domain = 'http://' . $domain;
        }

        return $domain;
    }

    /**
     * Returns a current host.
     *
     * This is the analog of Tools::getHttpHost(), but improved.
     *
     * @return string The host.
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public static function getHost()
    {
        $domain = '';

        foreach (['HTTP_X_FORWARDED_HOST', 'HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR'] as $source) {
            if (isset($_SERVER[$source])) {
                $domain = trim($_SERVER[$source]);
                if ('' !== $domain) {
                    if ('HTTP_X_FORWARDED_HOST' === $source) {
                        // The last host in the list is the current host
                        $domain = explode(',', $domain);
                        $domain = end($domain);
                    }

                    break;
                }
            }
        }

        // Removing a port
        $domain = preg_replace('/:\d+$/', '', $domain);

        return strtolower(trim($domain));
    }

    /**
     * Returns whether a host is match with the current host.
     *
     * @param string $host The host to check.
     *
     * @return bool
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public static function isOriginHost($host)
    {
        return (preg_replace('/^www./', '', $host) === preg_replace('/^www./', '', Tools::getHttpHost(false, false, true)));
    }

    /**
     * Returns a client IP.
     *
     * @return string
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public static function getClientIp()
    {
        if (false === empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        if (false === empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return $_SERVER['REMOTE_ADDR'];
    }
}