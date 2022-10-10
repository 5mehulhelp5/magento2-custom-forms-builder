<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace Alekseon\CustomFormsBuilder\Model\ResourceModel;

use Alekseon\AlekseonEav\Api\Data\AttributeInterface;
use Alekseon\AlekseonEav\Model\ResourceModel\Entity;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class FormRecord
 * @package Alekseon\CustomFormsBuilder\Model\ResourceModel
 */
class FormRecord extends \Alekseon\AlekseonEav\Model\ResourceModel\Entity
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'alekseon_custom_form_builder_form_record';
    /**
     * @var string
     */
    protected $entityTypeCode = 'alekseon_custom_form_record';

    /**
     * @var string
     */
    protected $imagesDirName = 'alekseon_custom_forms';
    /**
     * @var
     */
    protected $currentForm;

    /**
     * FormRecord constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param FormRecord\Attribute\CollectionFactory $attributeCollectionFactory
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Alekseon\CustomFormsBuilder\Model\ResourceModel\FormRecord\Attribute\CollectionFactory $attributeCollectionFactory,
        $connectionName = null
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        parent::__construct($context, $connectionName);
    }

    /**
     * @return void
     */
    protected function _construct() // @codingStandardsIgnoreLine
    {
        $this->_init('alekseon_custom_form_record', 'entity_id');
    }

    /**
     * @return $this
     */
    public function loadAllAttributes()
    {
        if ($this->allAttributesLoaded) {
            return $this;
        }
        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->setOrder('sort_order', AbstractDb::SORT_ORDER_ASC);

        if ($this->getCurrentForm()) {
            $attributeCollection->addFieldToFilter('form_id', $this->getCurrentForm()->getId());
        }

        foreach ($attributeCollection as $attribute) {
            $this->attributes[$attribute->getAttributeCode()] = $attribute;
        }
        $this->allAttributesLoaded = true;
        return $this;
    }

    /**
     *
     */
    protected function getCurrentForm()
    {
        return $this->currentForm;
    }

    /**
     * @param $form
     */
    public function setCurrentForm($form)
    {
        $this->currentForm = $form;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param AttributeInterface $attribute
     * @param string $fileName
     * @return string
     */
    public function getNameForUploadedFile(
        \Magento\Framework\Model\AbstractModel $object,
        AttributeInterface $attribute,
        string $fileName
    ) {
        $fileNameParts = explode('.', $fileName);
        $ext = end($fileNameParts);
        return md5($attribute->getAttributeCode() . $object->getId() . time()) . '.' . $ext;
    }

    /**
     * @return string
     */
    public function getImagesDirName()
    {
        $imagesDirName = parent::getImagesDirName();
        $form = $this->getCurrentForm();
        if ($form) {
            return $imagesDirName . '/' . substr(md5($form->getCreatedAt() . $form->getId()), 0, 10);
        }

        return $imagesDirName;
    }
}
