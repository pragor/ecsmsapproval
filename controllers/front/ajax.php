<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class EcSmsApprovalAjaxModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        header('Content-Type: application/json');

        if (Tools::getValue('token') !== Tools::getToken('ecsmsapproval')) {
            die(json_encode(['success' => false, 'error' => 'Invalid token']));
        }

        $idCustomer = (int) $this->context->customer->id;
        if (!$idCustomer) {
            die(json_encode(['success' => false, 'error' => 'Not authenticated']));
        }

        $approval = (int) Tools::getValue('approval', 0);
        $idShop = (int) $this->context->shop->id;
        $now = date('Y-m-d H:i:s');

        $existingId = (int) Db::getInstance()->getValue(
            'SELECT `id_ecsmsapproval` FROM `' . _DB_PREFIX_ . 'ecsmsapproval`
             WHERE `id_customer` = ' . $idCustomer . '
             AND `id_shop` = ' . $idShop
        );

        if ($existingId) {
            $result = Db::getInstance()->update('ecsmsapproval', [
                'approval' => $approval,
                'date_upd' => $now,
            ], '`id_ecsmsapproval` = ' . $existingId);
        } else {
            $result = Db::getInstance()->insert('ecsmsapproval', [
                'id_customer' => $idCustomer,
                'id_shop' => $idShop,
                'approval' => $approval,
                'date_add' => $now,
                'date_upd' => $now,
            ]);
        }

        die(json_encode(['success' => (bool) $result]));
    }
}
