<?php

namespace SilverCommerce\Dashboard\Panel;

use DateTime;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverCommerce\OrdersAdmin\Model\Invoice;
use ilateral\SilverStripe\Dashboard\Panels\DashboardPanel;
use ilateral\SilverStripe\Dashboard\Components\DashboardChart;

class RecentOrdersChartPanel extends DashboardPanel
{
    private static $table_name = 'DashboardRecentOrdersChartPanel';

    private static $font_icon = "chart-line";

    private static $defaults = array (
        'PanelSize' => "large"
    );

    public function getLabel(): string
    {
        return _t(__CLASS__ . '.RecentOrdersChart', 'Recent Orders Chart');
    }

    public function getDescription(): string
    {
        return _t(__CLASS__ . '.RecentOrdersChartDescription', 'Shows a chart of the last months orders.');
    }

    public function getChart(): DashboardChart
    {
        $chart = DashboardChart::create(
            "Last 30 days orders",
            "Date",
            "Number of orders",
            null
        );

        $results = ArrayList::create();
        $status = Invoice::config()->incomplete_status;

        // Get results for the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = new DateTime();

            if ($i > 0) {
                $date->modify("-{$i} day");
            }

            $orders = Invoice::get()
                ->filter(
                    array(
                        "Created:PartialMatch" => $date->format('Y-m-d'),
                        "Status:not" => $status
                    )
                )->count();
            
            $results->add(
                ArrayData::create(
                    array(
                        "Date"  => $date->format('jS F Y'),
                        "Count" => $orders
                    )
                )
            );
        }

        // Reverse the data
        $results = $results->reverse();

        foreach ($results as $result) {
            $chart->addData($result->Date, $result->Count);
        }
        
        return $chart;
    }
}
