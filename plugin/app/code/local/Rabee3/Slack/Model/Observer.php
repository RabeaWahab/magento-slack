<?php
class Rabee3_Slack_Model_Observer
{
    public function slackNewCustomer($observer)
    {
        $enabled = Mage::getModel('slack/slack')->isEnabled('customers');
        if(!$enabled) {
            return $this;
        }

        $customer = $observer->getEvent()->getCustomer();
        $customerName = $customer->getName();
        $customerEmail = $customer->getEmail();

        $message = "New Customer : `".$customerName."` - `".$customerEmail."`";
        $slacker = Mage::getModel('slack/slack')->send($message, 'customers');

        return $this;
    }

    public function slackNewOrder($observer)
    {
        $enabled = Mage::getModel('slack/slack')->isEnabled('orders');
        if(!$enabled) {
            return $this;
        }

        $orderIds   = $observer->getEvent()->getOrderIds();
        $order      = Mage::getModel('sales/order')->load($orderIds[0]);

        $orderNumber        = $order->getIncrementId();
        $orderTotal         = $order->getBaseGrandTotal();
        $orderCreationDate  = $order->getCreatedAt();

        $message = "New Order Number: `".$orderNumber."` \nCreated At: `".$orderCreationDate."` \nTotal: `".$orderTotal."`";
        $slacker = Mage::getModel('slack/slack')->send($message);

        return $this;
    }

    public function ordersHourly()
    {
        $enabled = Mage::getModel('slack/slack')->isEnabled('orders');
        if(!$enabled) {
            return $this;
        }

        $timeFilter = array(
        		'from' 	=> date('Y-m-d H:i:s', Mage::getSingleton('core/date')->gmtDate(time()-60*60)),
        		'to'	=> date('Y-m-d H:i:s', Mage::getSingleton('core/date')->gmtDate(time())),
        	);

        $ordersCount = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('created_at', array('from' => $timeFilter['from'], 'to' => $timeFilter['to']))
            ->addAttributeToFilter('status', array('neq' => 'canceled'))
            ->count();

        $message = "Orders Created Last Hour : " . $ordersCount;
        $slacker = Mage::getModel('slack/slack')->send($message);
    }
}
