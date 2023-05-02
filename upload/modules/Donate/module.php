<?php 
/*
 *  Made by Partydragen
 *  NamelessMC version 2.0.0-pr13
 *
 *  License: MIT
 *
 *  Donate module file
 */

class Donate_Module extends Module {
    private DB $_db;
    private $_language;
    private $_donate_language;
    private $_cache;

    public function __construct($language, $donate_language, $pages, $user, $navigation, $cache) {
        $this->_db = DB::getInstance();
        $this->_language = $language;
        $this->_donate_language = $donate_language;
        $this->_cache = $cache;

        $name = 'Donate';
        $author = '<a href="https://partydragen.com" target="_blank" rel="nofollow noopener">Partydragen</a>';
        $module_version = '1.0.2';
        $nameless_version = '2.1.0';

        parent::__construct($this, $name, $author, $module_version, $nameless_version);

        // Define URLs which belong to this module
        $pages->add('Donate', '/donate', 'pages/donate.php');
        $pages->add('Donate', '/donate/process', '/pages/backend/process.php');
        $pages->add('Donate', '/donate/listener', '/pages/backend/listener.php');

        $pages->add('Donate', '/panel/donate/settings', '/pages/panel/settings.php');
        $pages->add('Donate', '/panel/donate/payments', '/pages/panel/payments.php');

        // Register event
        EventHandler::registerEvent('newDonation', $this->_donate_language->get('admin', 'new_donation'));

        // Check if module version changed
        $cache->setCache('donate_module_cache');
        if (!$cache->isCached('module_version')) {
            $cache->store('module_version', $module_version);
        } else {
            if ($module_version != $cache->retrieve('module_version')) {
                // Version have changed, Perform actions
                $this->initialiseUpdate($cache->retrieve('module_version'));

                $cache->store('module_version', $module_version);

                if ($cache->isCached('update_check')) {
                    $cache->erase('update_check');
                }
            }
        }
    }

    public function onInstall() {
        // Initialise
        $this->initialise();
    }

    public function onUninstall() {
        
    }

    public function onEnable() {
        // Check if we need to initialise again
        $this->initialise();
    }

    public function onDisable() {
        // No actions necessary
    }

    public function onPageLoad($user, $pages, $cache, $smarty, $navs, $widgets, $template) {
        // navigation link location
        $cache->setCache('nav_location');
        if (!$cache->isCached('donate_location')) {
            $link_location = 1;
            $cache->store('donate_location', 1);
        } else {
            $link_location = $cache->retrieve('donate_location');
        }

        // Add link to navbar
        $cache->setCache('navbar_order');
        if (!$cache->isCached('donate_order')) {
            $order = 24;
            $cache->store('donate_order', 24);
        } else {
            $order = $cache->retrieve('donate_order');
        }

        $cache->setCache('navbar_icons');
        if (!$cache->isCached('donate_icon')) {
            $icon = '';
            $cache->store('donate_icon', '');
        } else {
            $icon = $cache->retrieve('donate_icon');
        }

        switch ($link_location) {
            case 1:
                // Navbar
                $navs[0]->add('donate', $this->_donate_language->get('general', 'donate'), URL::build('/donate'), 'top', null, $order, $icon);
            break;
            case 2:
                // "More" dropdown
                $navs[0]->addItemToDropdown('more_dropdown', 'donate', $this->_donate_language->get('general', 'donate'), URL::build('/donate'), 'top', null, $icon, $order);
            break;
            case 3:
                // Footer
                $navs[0]->add('donate', $this->_donate_language->get('general', 'donate'), URL::build('/donate'), 'footer', null, $order, $icon);
            break;
        }

        if (defined('BACK_END')) {
            // Define permissions which belong to this module
            PermissionHandler::registerPermissions($this->_donate_language->get('general', 'donate'), [
                'staffcp.donate.settings' => $this->_donate_language->get('admin', 'staffcp_donate_settings'),
                'staffcp.donate.payments' => $this->_donate_language->get('admin', 'staffcp_donate_payments')
            ]);

            if ($user->hasPermission('staffcp.donate.settings') || $user->hasPermission('staffcp.donate.payments')) {
                $cache->setCache('panel_sidebar');
                if (!$cache->isCached('donate_order')) {
                    $order = 18;
                    $cache->store('donate_order', 18);
                } else {
                    $order = $cache->retrieve('donate_order');
                }

                $navs[2]->add('donate_divider', mb_strtoupper($this->_donate_language->get('general', 'donate')), 'divider', 'top', null, $order, '');
                
                if ($user->hasPermission('staffcp.donate.settings')) {
                    if (!$cache->isCached('donate_settings_icon')) {
                        $icon = '<i class="nav-icon fas fa-cogs"></i>';
                        $cache->store('donate_settings_icon', $icon);
                    } else
                        $icon = $cache->retrieve('donate_settings_icon');

                    $navs[2]->add('donate_settings', $this->_donate_language->get('admin', 'settings'), URL::build('/panel/donate/settings'), 'top', null, ($order + 0.1), $icon);
                }
                
                if ($user->hasPermission('staffcp.donate.payments')) {
                    if (!$cache->isCached('donate_payments_icon')) {
                        $icon = '<i class="nav-icon fas fa-donate"></i>';
                        $cache->store('donate_payments_icon', $icon);
                    } else
                        $icon = $cache->retrieve('donate_payments_icon');

                    $navs[2]->add('donate_payments', $this->_donate_language->get('admin', 'payments'), URL::build('/panel/donate/payments'), 'top', null, ($order + 0.2), $icon);
                }
            }
        }
        
        // Check for module updates
        if (isset($_GET['route']) && $user->isLoggedIn() && $user->hasPermission('admincp.update')) {
            // Page belong to this module?
            $page = $pages->getActivePage();
            if ($page['module'] == 'Donate') {

                $cache->setCache('donate_module_cache');
                if ($cache->isCached('update_check')) {
                    $update_check = $cache->retrieve('update_check');
                } else {
                    require_once(ROOT_PATH . '/modules/Donate/classes/Donate.php');
                    $update_check = Donate::updateCheck();
                    $cache->store('update_check', $update_check, 3600);
                }

                $update_check = json_decode($update_check);
                if (!isset($update_check->error) && !isset($update_check->no_update) && isset($update_check->new_version)) {  
                    $smarty->assign([
                        'NEW_UPDATE' => (isset($update_check->urgent) && $update_check->urgent == 'true') ? $this->_donate_language->get('admin', 'new_urgent_update_available_x', ['module' => $this->getName()]) : $this->_donate_language->get('admin', 'new_update_available_x', ['module' => $this->getName()]),
                        'NEW_UPDATE_URGENT' => (isset($update_check->urgent) && $update_check->urgent == 'true'),
                        'CURRENT_VERSION' => $this->_donate_language->get('admin', 'current_version_x', ['version' => Output::getClean($this->getVersion())]),
                        'NEW_VERSION' => $this->_donate_language->get('admin', 'new_version_x', ['new_version' => Output::getClean($update_check->new_version)]),
                        'NAMELESS_UPDATE' => $this->_donate_language->get('admin', 'view_resource'),
                        'NAMELESS_UPDATE_LINK' => Output::getClean($update_check->link)
                    ]);
                }
            }
        }
    }

    public function getDebugInfo(): array {
        return [];
    }

    private function initialise() {
        // Generate tables
        if (!$this->_db->showTables('donate_payments')) {
            try {
                $this->_db->createTable('donate_payments', '`id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL DEFAULT \'0\', `payment_method` int(11) NOT NULL, `transaction_id` varchar(32) DEFAULT NULL, `payer_email` varchar(64) DEFAULT NULL, `amount` varchar(11) DEFAULT NULL, `currency` varchar(11) DEFAULT NULL, `fee` varchar(11) DEFAULT NULL, `status_id` int(11) NOT NULL DEFAULT \'0\', `created` int(11) NOT NULL, `last_updated` int(11) NOT NULL, PRIMARY KEY (`id`)');
            } catch(Exception $e) {
                // Error
            }
        }

        if (!$this->_db->get('settings', ['module', '=', 'Donate'])->count()) {
            Util::setSetting('paypal_email', '', 'Donate');
            Util::setSetting('currency', 'USD', 'Donate');
            Util::setSetting('min_amount', '5.00', 'Donate');
            Util::setSetting('content', 'Huge thanks to everyone who wish to donate', 'Donate');
            Util::setSetting('success_content', 'We appreciate and thank you for your donation. It might take a while before it shows up in the donation list', 'Donate');
            Util::setSetting('reward_group', '0', 'Donate');
        }
    }

    private function initialiseUpdate($old_version) {
        $old_version = str_replace([".", "-"], "", $old_version);

        if ($old_version < 102) {
            try {
                if ($this->_db->showTables('donate_settings')) {
                    // Convert donate settings to NamelessMC settings system
                    $settings = $this->_db->query('SELECT * FROM nl2_donate_settings')->results();
                    foreach ($settings as $setting) {
                        Util::setSetting($setting->name, $setting->value, 'Donate');
                    }

                    $this->_db->query('DROP TABLE nl2_donate_settings');
                }
            } catch (Exception $e) {
                echo $e->getMessage() . '<br />';
            }
        }
    }
}
