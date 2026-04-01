<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class EcSmsApproval extends Module
{
    public function __construct()
    {
        $this->name = 'ecsmsapproval';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Ether Creation';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.1.0', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = 'EC SMS Approval';
        $this->description = 'Gestion du consentement SMS des clients.';
    }

    public function install()
    {
        return parent::install()
            && $this->createTable()
            && $this->registerHook('displayAfterCarrier')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('actionEcSmsApprovalCheck')
            && $this->registerHook('displayAdminCustomers')
            && $this->registerHook('actionAdminControllerSetMedia')
            && $this->installTab();
    }

    public function uninstall()
    {
        return $this->uninstallTab()
            && parent::uninstall();
        // La table est conservée pour ne pas perdre les données
    }

    private function createTable()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ecsmsapproval` (
                `id_ecsmsapproval` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_customer` int(10) unsigned NOT NULL,
                `id_shop` int(10) unsigned NOT NULL,
                `approval` tinyint(1) NOT NULL DEFAULT 0,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_ecsmsapproval`),
                UNIQUE KEY `customer_shop` (`id_customer`, `id_shop`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;'
        );
    }

    private function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminEcSmsApproval';
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'SMS Approval';
        }
        $tab->id_parent = -1;
        $tab->module = $this->name;

        return $tab->add();
    }

    private function uninstallTab()
    {
        $idTab = (int) Db::getInstance()->getValue(
            'SELECT `id_tab` FROM `' . _DB_PREFIX_ . 'tab`
             WHERE `class_name` = \'AdminEcSmsApproval\''
        );
        if ($idTab) {
            $tab = new Tab($idTab);
            return $tab->delete();
        }

        return true;
    }

    public function getContent()
    {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminEcSmsApproval', true)
        );
    }

    public function hookActionFrontControllerSetMedia()
    {
        if ($this->context->controller->php_self === 'order') {
            $this->context->controller->registerJavascript(
                'module-ecsmsapproval-front',
                'modules/ecsmsapproval/views/js/front.js',
                ['position' => 'bottom', 'priority' => 150]
            );
            $this->context->controller->registerStylesheet(
                'module-ecsmsapproval-front',
                'modules/ecsmsapproval/views/css/front.css',
            );

            Media::addJsDef([
                'ecsmsAjaxUrl' => $this->context->link->getModuleLink($this->name, 'ajax', [], true),
                'ecsmsToken' => Tools::getToken('ecsmsapproval'),
            ]);
        }
    }

    public function hookDisplayAfterCarrier()
    {
        return $this->display(__FILE__, 'views/templates/hook/displayCheckoutDeliveryStep.tpl');
    }

    public function hookActionAdminControllerSetMedia()
    {
        if (Tools::getValue('_legacy_controller') === 'AdminCustomers'
            || Tools::getValue('controller') === 'AdminCustomers'
        ) {
            $this->context->controller->addJS($this->_path . 'views/js/admin_customer.js');
        }
    }

    public function hookDisplayAdminCustomers(array $params)
    {
        $idCustomer = (int) ($params['id_customer'] ?? 0);
        $approval = self::getCustomerApproval($idCustomer);

        $this->context->smarty->assign([
            'ecsms_approval' => $approval,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayAdminCustomers.tpl');
    }

    /**
     * Hook non natif : retourne le consentement SMS d'un client.
     *
     * Usage : Hook::exec('actionEcSmsApprovalCheck', ['id_customer' => $idCustomer])
     *
     * @param array $params ['id_customer' => int]
     * @return array|null ['has_record' => bool, 'approved' => bool|null]
     */
    public function hookActionEcSmsApprovalCheck(array $params)
    {
        $idCustomer = (int) ($params['id_customer'] ?? 0);
        if (!$idCustomer) {
            return null;
        }

        $row = Db::getInstance()->getRow(
            'SELECT `approval` FROM `' . _DB_PREFIX_ . 'ecsmsapproval`
             WHERE `id_customer` = ' . $idCustomer . '
             AND `id_shop` = ' . (int) $this->context->shop->id
        );

        if (!$row) {
            return ['has_record' => false, 'approved' => null];
        }

        return ['has_record' => true, 'approved' => (bool) $row['approval']];
    }

    /**
     * Méthode utilitaire pour interroger directement sans passer par le hook.
     *
     * @param int $idCustomer
     * @param int|null $idShop
     * @return bool|null null si pas d'enregistrement
     */
    public static function getCustomerApproval($idCustomer, $idShop = null)
    {
        if (!$idShop) {
            $idShop = (int) Context::getContext()->shop->id;
        }

        $row = Db::getInstance()->getRow(
            'SELECT `approval` FROM `' . _DB_PREFIX_ . 'ecsmsapproval`
             WHERE `id_customer` = ' . (int) $idCustomer . '
             AND `id_shop` = ' . (int) $idShop
        );

        if (!$row) {
            return null;
        }

        return (bool) $row['approval'];
    }
}
