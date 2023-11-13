<?php
declare(strict_types=1);
namespace Lofmp\SearchSeller\Helper;

use Lof\MarketPlace\Block\Seller\Editprofile;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    protected Editprofile $editProfile;
    public function __construct(Context $context, Editprofile $editProfile)
    {
        $this->editProfile = $editProfile;
        parent::__construct($context);
    }

    public function getOrderUrl(string $orderBy): string
    {
        return $this->_getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => ['order_by' => $orderBy]]);
    }

    public function getOrderTypeUrl(string $orderType): string
    {
        return $this->_getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => ['order_type' => $orderType]]);
    }

    public function getCountries($countryId = "US")
    {
        return $this->editProfile->getCountries($countryId);
    }
}
