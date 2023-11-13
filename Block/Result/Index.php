<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\SearchSeller\Block\Result;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $sellerHelper;
    protected $sellerModel;
    protected $sellerPage;
    protected $response;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Response\Http             $response,
        \Lof\MarketPlace\Helper\Data                     $sellerHelper,
        \Lof\MarketPlace\Block\Sellerpage                $sellerPage,
        \Lof\MarketPlace\Model\Seller                    $sellerModel,
        array                                            $data = []
    ) {
        $this->sellerPage = $sellerPage;
        $this->response = $response;
        $this->sellerHelper = $sellerHelper;
        $this->sellerModel = $sellerModel;
        parent::__construct($context, $data);
        if (!$this->getRequest()->getParam('q')) {
//            $this->response->setRedirect('/');
        }
    }

    public function getSellers()
    {
        $seller = $this->sellerModel->getCollection();

        $page = $this->getRequest()->getParam('p') ?? 1;
        $limit = $this->getRequest()->getParam('limit') ?? 8;
        $orderBy = $this->getRequest()->getParam('order_by') ?? 'name';
        $orderType = $this->getRequest()->getParam('order_type') ?? 'ASC';
        $searchKey = $this->getRequest()->getParam('q') ?? null;
        $countryId = $this->getRequest()->getParam('country') ?? null;
        $city = $this->getRequest()->getParam('ct') ?? null;
        $postcode = $this->getRequest()->getParam('pc') ?? null;

        if ($countryId) {
            $seller->addFieldToFilter('country_id', $countryId);
        }

        if ($searchKey) {
            $seller->addFieldToFilter(
                ['name','address', 'url_key', 'region','city','postcode'],
                [
                    ['like' => "%$searchKey%"],
                    ['like' => "%$searchKey%"],
                    ['like' => "%$searchKey%"],
                    ['like' => "%$searchKey%"],

                    ['like' => "%$city%"],
                    ['like' => "%$postcode%"],
                ]
            );
        }
        $seller->addFieldToFilter('status', 1);
        $seller->setOrder($orderBy, $orderType);
        $seller->setPageSize($limit);
        $seller->setCurPage($page);
        return $seller;
    }

    /**
     * @param mixed|string $social
     * @return bool
     */
    public function isAllowedSocial($social)
    {
        return $this->sellerHelper->isAllowedSocial($social);
    }

    public function getTotalSales($seller_id)
    {
        return $this->sellerPage->getTotalSales($seller_id);
    }

    public function getCountRating($rateId)
    {
        return $this->sellerPage->getCountRating($rateId);
    }

    protected function _addBreadcrumbs()
    {
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'lof_seachseller',
                [
                    'label' => "Search results for: '{$this->getRequest()->getParam('q')}'",
                    'title' => "Search results for: '{$this->getRequest()->getParam('q')}'"
                ]
            );
        }
    }

    protected function _prepareLayout()
    {
        $page_title = "Search results for: '{$this->getRequest()->getParam('q')}'";
        $this->pageConfig->addBodyClass('lof-sellerlist');
        $this->pageConfig->getTitle()->set($page_title);
        $this->_addBreadcrumbs();
        if ($this->getSellers()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'lof.search.seller.pager'
            )->setAvailableLimit([8 => 8, 16 => 16 , 32 => 32])->setShowPerPage(true)->setCollection(
                $this->getSellers()
            );
            $this->setChild('pager', $pager);
            $this->getSellers()->load();
        }
        return parent::_prepareLayout();
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
