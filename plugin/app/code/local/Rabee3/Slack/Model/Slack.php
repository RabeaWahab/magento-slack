<?php
class Rabee3_Slack_Model_Slack extends Mage_Core_Model_Abstract
{
    // Slack notification type
    const SLACK_TYPE_ORDERS      = 'orders';
    const SLACK_TYPE_PRODUCTS    = 'products';
    const SLACK_TYPE_CUSTOMERS   = 'customers';
    const SLACK_TYPE_LOG         = 'logs';

    // default
    const SLACK_ENABLED         = 'slack/general/enabled';
    const SLACK_WEBHOOK         = 'slack/general/webhookurl';
    const SLACK_USERNAME        = 'slack/general/username';

    // Orders
    const SLACK_ORDERS          = 'slack/orders';
    const SLACK_ORDERS_ENABLED  = 'slack/orders/enabled';
    const SLACK_ORDERS_USERNAME = 'slack/orders/username';
    const SLACK_ORDERS_CHANNEL  = 'slack/orders/channel';

    // Orders
    const SLACK_CUSTOMERS          = 'slack/customers';
    const SLACK_CUSTOMERS_ENABLED  = 'slack/customers/enabled';
    const SLACK_CUSTOMERS_USERNAME = 'slack/customers/username';
    const SLACK_CUSTOMERS_CHANNEL  = 'slack/customers/channel';

    public $userName;
    public $channel;
    public $webHook;
    public $slackConfig;
    public $ttl;

    public function isEnabled($type)
    {
        $this->slackConfig = Mage::getStoreConfig('slack');
        if(!$this->slackConfig['general']['enabled']) {
            return false;
        }

        if($type == self::SLACK_TYPE_ORDERS) {
            $isEnabled = Mage::getStoreConfig(self::SLACK_ORDERS_ENABLED);
            if(!$isEnabled){
                return false;
            }
        }

        if($type == self::SLACK_TYPE_CUSTOMERS) {
            $isEnabled = Mage::getStoreConfig(self::SLACK_CUSTOMERS_ENABLED);
            if(!$isEnabled){
                return false;
            }
        }

        return true;
    }

    public function getConfigs($type)
    {
        $this->slackConfig = Mage::getStoreConfig('slack');
        $this->getWebHook();
        $this->getChannel($type);
        $this->getUsername($type);
        $this->getTTL();
    }

    public function getTTL()
    {
        $slackConfig = $this->slackConfig;
        $ttl = $slackConfig['general']['timeout'];

        if(empty($ttl) || !is_numeric($ttl) || $ttl < 1000) {
            $this->ttl = 1000;
        } else {
            $this->ttl = $ttl;
        }
    }

    public function getWebHook()
    {
        $slackConfig = $this->slackConfig;
        $this->webHook = $slackConfig['general']['webhookurl'];
    }

    public function getChannel($slackType)
    {
        $slackConfig = $this->slackConfig;
        if($slackType == self::SLACK_TYPE_ORDERS) {
            $orderChannel = $slackConfig['orders']['channel'];
            if(!empty($orderChannel) && isset($orderChannel)) {
                $this->channel = $orderChannel;
                return;
            }
        } elseif ($slackType == self::SLACK_TYPE_CUSTOMERS) {
            $customerChannel = $slackConfig['customers']['channel'];
            if(!empty($customerChannel) && isset($customerChannel)) {
                $this->channel = $customerChannel;
                return;
            }
        }

        $generalChannel = $slackConfig['general']['channel'];
        if(empty($this->channel) && !empty($generalChannel) && isset($generalChannel)) {
            $this->channel = $generalChannel;
        } else {
            $this->channel = 'MagentoStore';
        }

        return;
    }

    public function getUsername($slackType)
    {
        $slackConfig = $this->slackConfig;

        if($slackType == self::SLACK_TYPE_ORDERS) {
            $orderUserName = $slackConfig['orders']['username'];
            if(!empty($orderUserName) && isset($orderUserName)) {
                $this->userName = $orderUserName;
                return;
            }
        } elseif ($slackType == self::SLACK_TYPE_CUSTOMERS) {
            $customerUserName = $slackConfig['customers']['username'];
            if(!empty($customerUserName) && isset($customerUserName)) {
                $this->userName = $customerUserName;
                return;
            }
        }

        $generalUserName = $slackConfig['general']['username'];
        if(empty($this->username) && !empty($generalUserName) && isset($generalUserName)) {
            $this->userName = $generalUserName;
        } else {
            $this->userName = 'MagentoStore';
        }

        return;
    }

    public function prepareMessage($message, $type, $username)
    {
        $this->getConfigs($type);

        $params = array("text" => $message, "channel" => "#" . $this->channel, "username" => "@" . $this->userName);
        return json_encode($params);
    }

    public function send($message, $type = 'orders')
    {
        $messagePrepared = $this->prepareMessage($message, $type);

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $this->webHook);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->ttl);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array('payload' => $messagePrepared));

            $result = curl_exec($ch);
            if(!$result) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            Mage::log($e->getMessage());
            return false;
        }
    }
}
