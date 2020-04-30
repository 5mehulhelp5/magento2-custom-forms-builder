<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace Alekseon\CustomFormsBuilder\Controller\Adminhtml\FormField;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Edit
 * @package Alekseon\CustomFormsBuilder\Controller\Adminhtml\FormField
 */
class Edit extends \Alekseon\CustomFormsBuilder\Controller\Adminhtml\FormField
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|mixed
     */
    public function execute()
    {
        try {
            $attribute = $this->initAttribute();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->returnResult('*/form/index');
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        if ($attribute) {
            $resultPage->getConfig()->getTitle()->prepend(__('Edit Form Field') . ' ' . $attribute->getFrontendLabel());
        } else {
            $this->messageManager->addError(__('Form Field not found.'));
            return $this->returnResult('*/form/index');
        }
        return $resultPage;
    }
}
