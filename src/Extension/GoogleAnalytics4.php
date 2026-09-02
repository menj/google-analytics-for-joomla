<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.google_analytics_4
 *
 * @copyright   Copyright (C) 2023-2026 MENJ
 * @license     GNU General Public License version 2 or later
 */

namespace MENJ\Plugin\System\GoogleAnalytics4\Extension;

use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Version;
use Joomla\Event\SubscriberInterface;

\defined('_JEXEC') or die;

/**
 * System plugin to install Google Analytics 4 (gtag.js) with Consent Mode v2 defaults.
 *
 * Compatible with Joomla 4.4 LTS, 5.x, and 6.x — this class targets the current
 * CMSPlugin + SubscriberInterface API, which is identical across all three lines.
 * It does not depend on the "Behaviour - Backward Compatibility" plugin(s) that
 * shim legacy JPlugin/JFactory-in-constructor code for J6 migrations, so it is
 * unaffected by that plugin's presence, absence, or load-order changes.
 *
 * @since  1.0.0
 */
final class GoogleAnalytics4 extends CMSPlugin implements SubscriberInterface
{
    /**
     * Minimum supported Joomla major.minor version.
     *
     * @since  2.0.0
     */
    private const MIN_JOOMLA_VERSION = '4.4';

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  1.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   2.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeCompileHead' => 'onBeforeCompileHead',
        ];
    }

    /**
     * Injects the GA4 gtag.js snippet, preceded by Consent Mode v2 defaults.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function onBeforeCompileHead(): void
    {
        $app = $this->getApplication();

        // Front-end HTML documents only.
        if (!$app->isClient('site')) {
            return;
        }

        if ((new Version())->isCompatible(self::MIN_JOOMLA_VERSION) === false) {
            $this->logAndWarn(
                sprintf('Requires Joomla %s or later.', self::MIN_JOOMLA_VERSION)
            );

            return;
        }

        $doc = $app->getDocument();

        if ($doc->getType() !== 'html') {
            return;
        }

        $measurementId = trim((string) $this->params->get('measurement_id', ''));

        if (!preg_match('/^G-[A-Z0-9]+$/i', $measurementId)) {
            $this->logAndWarn(sprintf('Invalid GA4 Measurement ID format: "%s"', $measurementId));

            return;
        }

        // Consent Mode v2: sensible EU-safe defaults, configurable via plugin params.
        // These are DEFAULTS only. If the site has a cookie-consent tool (CookieBot,
        // Complianz, Klaro, etc.), that tool should push a 'consent' 'update' command
        // to the dataLayer once the visitor makes a choice — this plugin does not
        // attempt to read or manage consent state itself.
        $analyticsStorage  = $this->params->get('default_analytics_storage', 'denied');
        $adStorage         = $this->params->get('default_ad_storage', 'denied');
        $adUserData        = $this->params->get('default_ad_user_data', 'denied');
        $adPersonalization = $this->params->get('default_ad_personalization', 'denied');
        $waitForUpdate     = (int) $this->params->get('wait_for_update', 500);
        $anonymizeIp       = $this->params->get('anonymize_ip', '1') === '1' ? 'true' : 'false';

        $measurementIdJs = json_encode($measurementId);

        $script = <<<JS
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('consent', 'default', {
            'analytics_storage': '{$analyticsStorage}',
            'ad_storage': '{$adStorage}',
            'ad_user_data': '{$adUserData}',
            'ad_personalization': '{$adPersonalization}',
            'wait_for_update': {$waitForUpdate}
        });
        gtag('js', new Date());
        gtag('config', {$measurementIdJs}, {
            'anonymize_ip': {$anonymizeIp}
        });
        JS;

        $wa = $doc->getWebAssetManager();
        $wa->registerScript(
            'plg_system_google_analytics_4.gtag',
            'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode($measurementId),
            [],
            ['async' => true]
        );
        $wa->useScript('plg_system_google_analytics_4.gtag');

        $doc->addScriptDeclaration($script);
    }

    /**
     * Logs an error and enqueues a visible admin warning.
     *
     * @param   string  $message  The message to log/display.
     *
     * @return  void
     *
     * @since   2.0.0
     */
    private function logAndWarn(string $message): void
    {
        Log::add($message, Log::ERROR, 'plg_system_google_analytics_4');

        $app = $this->getApplication();

        if (method_exists($app, 'enqueueMessage')) {
            $app->enqueueMessage('Google Analytics 4 Plugin: ' . $message, 'warning');
        }
    }
}
