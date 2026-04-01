<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminEcSmsApprovalController extends ModuleAdminController
{
    const ITEMS_PER_PAGE = 50;

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function postProcess()
    {
        if (Tools::getValue('action') === 'export') {
            $this->exportCsv();
        }
        parent::postProcess();
    }

    public function initContent()
    {
        $this->content = $this->buildList();
        parent::initContent();
    }

    private function buildShopWhere()
    {
        if (Shop::getContext() === Shop::CONTEXT_ALL) {
            return '1';
        }
        return 'a.`id_shop` = ' . (int) $this->context->shop->id;
    }

    private function buildList()
    {
        $page = max(1, (int) Tools::getValue('page', 1));
        $offset = ($page - 1) * self::ITEMS_PER_PAGE;

        $where = $this->buildShopWhere();

        $total = (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'ecsmsapproval` a WHERE ' . $where
        );

        $rows = Db::getInstance()->executeS(
            'SELECT a.`id_customer`, a.`id_shop`, a.`approval`, a.`date_add`, a.`date_upd`,
                    CONCAT(c.`firstname`, \' \', c.`lastname`) AS customer_name,
                    c.`email`, c.`is_guest`
             FROM `' . _DB_PREFIX_ . 'ecsmsapproval` a
             LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON c.`id_customer` = a.`id_customer`
             WHERE ' . $where . '
             ORDER BY a.`date_upd` DESC
             LIMIT ' . self::ITEMS_PER_PAGE . ' OFFSET ' . $offset
        );

        $totalPages = (int) ceil($total / self::ITEMS_PER_PAGE);
        $baseUrl = $this->context->link->getAdminLink('AdminEcSmsApproval', true);

        $this->context->smarty->assign([
            'rows' => $rows ?: [],
            'total' => $total,
            'page' => $page,
            'total_pages' => $totalPages,
            'base_url' => $baseUrl,
            'export_url' => $baseUrl . '&action=export',
        ]);

        return $this->context->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/admin/list.tpl'
        );
    }

    private function exportCsv()
    {
        $where = $this->buildShopWhere();

        $rows = Db::getInstance()->executeS(
            'SELECT a.`id_customer`, a.`id_shop`, a.`approval`, a.`date_add`, a.`date_upd`,
                    CONCAT(c.`firstname`, \' \', c.`lastname`) AS customer_name,
                    c.`email`, c.`is_guest`
             FROM `' . _DB_PREFIX_ . 'ecsmsapproval` a
             LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON c.`id_customer` = a.`id_customer`
             WHERE ' . $where . '
             ORDER BY a.`date_upd` DESC'
        );

        $filename = 'sms_approval_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $output = fopen('php://output', 'w');
        // BOM UTF-8 pour compatibilité Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'ID Client', 'Nom', 'Email', 'Invité', 'Shop', 'Consentement SMS', 'Date création', 'Date mise à jour',
        ], ';');

        if ($rows) {
            foreach ($rows as $row) {
                fputcsv($output, [
                    $row['id_customer'],
                    $row['customer_name'],
                    $row['email'],
                    $row['is_guest'] ? 'Oui' : 'Non',
                    $row['id_shop'],
                    $row['approval'] ? 'Approuvé' : 'Refusé',
                    $row['date_add'],
                    $row['date_upd'],
                ], ';');
            }
        }

        fclose($output);
        exit;
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
    }
}
