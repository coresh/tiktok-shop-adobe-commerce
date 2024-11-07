<?php

namespace M2E\TikTokShop\Model\Magento;

class Customer extends \Magento\Framework\DataObject
{
    public const FAKE_EMAIL_POSTFIX = '@dummy.email';

    private \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory;
    private \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory;
    private \Magento\Framework\Math\Random $mathRandom;
    private \Magento\Customer\Model\CustomerFactory $customerFactory;
    private \Magento\Customer\Model\AddressFactory $addressFactory;
    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private \Magento\Customer\Model\Customer $customer;
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;
    private \M2E\TikTokShop\Helper\Module\Database\Structure $dbStructureHelper;
    /** @var \M2E\TikTokShop\Model\Magento\Attribute\BuilderFactory */
    private Attribute\BuilderFactory $magentoAttributeBuilderFactory;

    public function __construct(
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper,
        \M2E\TikTokShop\Helper\Module\Database\Structure $dbStructureHelper,
        \M2E\TikTokShop\Model\Magento\Attribute\BuilderFactory $magentoAttributeBuilderFactory,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct();
        $this->customerDataFactory = $customerDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->mathRandom = $mathRandom;
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->resourceConnection = $resourceConnection;
        $this->exceptionHelper = $exceptionHelper;
        $this->dbStructureHelper = $dbStructureHelper;
        $this->magentoAttributeBuilderFactory = $magentoAttributeBuilderFactory;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    public function buildCustomer()
    {
        $password = $this->mathRandom->getRandomString(7);

        /**
         * Magento can replace customer group to the default.
         * vendor/magento/module-customer/Observer/AfterAddressSaveObserver.php:121
         * Can be disabled here:
         * Customers -> Customer Configuration -> Create new account options -> Automatic Assignment to Customer Group
         */
        $customerData = $this->customerDataFactory->create()
                                                  ->setPrefix($this->getData('customer_prefix'))
                                                  ->setFirstname($this->getData('customer_firstname'))
                                                  ->setMiddlename($this->getData('customer_middlename'))
                                                  ->setLastname($this->getData('customer_lastname'))
                                                  ->setSuffix($this->getData('customer_suffix'))
                                                  ->setWebsiteId($this->getData('website_id'))
                                                  ->setGroupId($this->getData('group_id'))
                                                  ->setEmail($this->getData('email'))
                                                  ->setConfirmation($password);

        $this->customer = $this->customerFactory->create();
        $this->customer->updateData($customerData);
        $this->customer->setPassword($password);
        $this->customer->save();

        // Add customer address
        // ---------------------------------------
        $addressModel = $this->addressFactory->create();
        $this->_updateAddress($addressModel);

        $addressData = $this->addressDataFactory->create()
                                                ->setIsDefaultBilling(true)
                                                ->setIsDefaultShipping(true);

        $addressModel->updateData($addressData);

        $addressModel->setCustomer($this->customer);
        $addressModel->save();

        $this->customer->addAddress($addressModel);
        // ---------------------------------------
    }

    public function updateAddress(\Magento\Customer\Model\Customer $customerObject)
    {
        $this->customer = $customerObject;

        foreach ($customerObject->getPrimaryAddresses() as $addressModel) {
            $this->_updateAddress($addressModel);
            $addressModel->save();
        }
    }

    private function _updateAddress(\Magento\Customer\Model\Address $addressModel)
    {
        $street = $this->getData('street');
        if (!is_array($street)) {
            $street = explode('; ', $street);
        }

        $addressData = $this->addressDataFactory->create()
                                                ->setPrefix($this->getData('prefix'))
                                                ->setFirstname($this->getData('firstname'))
                                                ->setMiddlename($this->getData('middlename'))
                                                ->setLastname($this->getData('lastname'))
                                                ->setSuffix($this->getData('suffix'))
                                                ->setCountryId($this->getData('country_id'))
                                                ->setCity($this->getData('city'))
                                                ->setPostcode($this->getData('postcode'))
                                                ->setTelephone($this->getData('telephone'))
                                                ->setStreet($street)
                                                ->setCompany($this->getData('company'));

        $addressModel->updateData($addressData);
        /**
         * Updating 'region_id' value to null will be skipped in
         * vendor/magento/framework/Reflection/DataObjectProcessor.php::buildOutputDataArray()
         * So, we are forced to use separate setter for 'region_id' to bypass this validation
         */
        $addressModel->setRegionId($this->getData('region_id'));
    }

    public function buildAttribute($code, $label)
    {
        try {
            $attributeBuilder = $this->magentoAttributeBuilderFactory->create();
            $attributeBuilder->setCode($code);
            $attributeBuilder->setLabel($label);
            $attributeBuilder->setInputType('text');
            $attributeBuilder->setEntityTypeId(
                $this->customerFactory->create()->getEntityType()->getId()
            );
            $attributeBuilder->setParams(['default_value' => '']);

            $result = $attributeBuilder->save();
            if (!$result['result']) {
                return;
            }

            /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
            $attribute = $result['obj'];

            $defaultAttributeSetId = $this->getDefaultAttributeSetId();

            $this->addAttributeToGroup(
                $attribute->getId(),
                $defaultAttributeSetId,
                $this->getDefaultAttributeGroupId($defaultAttributeSetId)
            );
        } catch (\Throwable $exception) {
            $this->exceptionHelper->process($exception);
        }
    }

    // ---------------------------------------

    private function addAttributeToGroup($attributeId, $attributeSetId, $attributeGroupId)
    {
        $connWrite = $this->resourceConnection->getConnection();

        $data = [
            'entity_type_id' => $this->customerFactory->create()->getEntityType()->getId(),
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'attribute_id' => $attributeId,
        ];

        $connWrite->insert(
            $this->dbStructureHelper->getTableNameWithPrefix('eav_entity_attribute'),
            $data
        );
    }

    private function getDefaultAttributeSetId()
    {
        $connRead = $this->resourceConnection->getConnection();

        $select = $connRead->select()
                           ->from(
                               $this->dbStructureHelper->getTableNameWithPrefix('eav_entity_type'),
                               'default_attribute_set_id'
                           )
                           ->where('entity_type_id = ?', $this->customerFactory->create()->getEntityType()->getId());

        return $connRead->fetchOne($select);
    }

    private function getDefaultAttributeGroupId($attributeSetId)
    {
        $connRead = $this->resourceConnection->getConnection();

        $select = $connRead->select()
                           ->from(
                               $this->dbStructureHelper->getTableNameWithPrefix(
                                   'eav_attribute_group'
                               ),
                               'attribute_group_id'
                           )
                           ->where('attribute_set_id = ?', $attributeSetId)
                           ->order(['default_id ' . \Magento\Framework\DB\Select::SQL_DESC, 'sort_order'])
                           ->limit(1);

        return $connRead->fetchOne($select);
    }
}
