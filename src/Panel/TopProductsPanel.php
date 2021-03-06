<?php

namespace SilverCommerce\Dashboard\Panel;

use DateTime;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Forms\TextField;
use SilverStripe\View\Requirements;
use SilverStripe\Core\Injector\Injector;
use UncleCheese\Dashboard\DashboardPanel;
use SilverCommerce\OrdersAdmin\Model\Invoice;
use UncleCheese\Dashboard\DashboardPanelAction;
use SilverCommerce\Reports\ItemsOrderedReport;

class TopProductsPanel extends DashboardPanel
{
    private static $table_name = 'DashboardTopProductsPanel';

    private static $db = array (
        'Count' => 'Int'
    );

	private static $defaults = array (
		'Count' => "5",
		'PanelSize' => "small"
	);

    private static $icon = "silvercommerce/dashboard: client/dist/images/top.png";

    public function getLabel()
    {
        return _t(__CLASS__ . '.TopProducts', 'Top Products');
    }

    public function getDescription()
    {
        return _t(__CLASS__ . '.TopProductsDescription' ,'Shows top selling products this month.');
    }

    /**
     * Return a link to the "items ordered" report 
     *
     * @return string
     */
    public function ReportLink()
    {
        if (class_exists(ItemsOrderedReport::class)) {
            return Injector::inst()->create(ItemsOrderedReport::class)->getLink();
        }
        return null;
    }

    public function PanelHolder()
    {
        Requirements::css("silvercommerce/dashboard: client/dist/css/dashboard.css");
        return parent::PanelHolder();
    }

    public function getConfiguration()
    {
        $fields = parent::getConfiguration();

        $fields->push(
            TextField::create(
                "Count",
                "Number of products to show"
            )
        );

        return $fields;
    }

    /**
     * Add view all button to actions
     *
     * @return ArrayList
     */
    public function getSecondaryActions()
    {
		$actions = parent::getSecondaryActions();
		$actions->push(DashboardPanelAction::create(
            $this->ReportLink(),
            _t("SilverCommerce.ViewAll", "View All")
        ));
			
		return $actions;
	}

    /**
     * Return a list of top products for the template
     *
     * @return ArrayList
     */
    public function Products()
    {
        $return = ArrayList::create();

        $start_date = new DateTime();
        $start_date->modify("-1 month");

        $end_date = new DateTime();

        // Get all orders in the date range
        $orders = Invoice::get()
            ->filter(array(
                "Created:GreaterThan" => $start_date->format('Y-m-d H:i:s'),
                "Created:LessThan" => $end_date->format('Y-m-d H:i:s')
            ));

        // Loop through orders, find all items and add to a tally
        foreach ($orders as $order) {
            foreach ($order->Items() as $order_item) {
                if ($order_item->StockID) {
                    if ($list_item = $return->find("StockID", $order_item->StockID)) {
                        $list_item->Quantity = $list_item->Quantity + $order_item->Quantity;
                    } else {
                        $return->add(ArrayData::create(array(
                            "StockID" => $order_item->StockID,
                            "Title" => $order_item->Title,
                            "Quantity" => $order_item->Quantity
                        )));
                    }
                }
            }
        }

        return $return;
    }
}