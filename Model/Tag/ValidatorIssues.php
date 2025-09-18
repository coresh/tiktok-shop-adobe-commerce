<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Tag;

use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator\ValidatorMessage;

class ValidatorIssues
{
    public const NOT_USER_ERROR = 'not-user-error';

    public const ERROR_NO_ASSOCIATED_PRODUCTS_LIST = '0001-m2e';
    public const ERROR_NO_ASSOCIATED_PRODUCTS_RELIST = '0002-m2e';
    public const ERROR_PRICE_OUT_OF_RANGE = '0003-m2e';
    public const ERROR_QUANTITY_POLICY_CONTRADICTION = '0004-m2e';
    public const ERROR_QTY_EXCEEDS_MAXIMUM = '0005-m2e';
    public const ERROR_MISSING_BRAND = '0006-m2e';
    public const ERROR_MISSING_GPSR_INFO = '0007-m2e';
    public const ERROR_CATEGORIES_NOT_SET = '0008-m2e';
    public const ERROR_INVALID_CERTIFICATE_IMAGE_URL = '0009-m2e';
    public const ERROR_MISSING_PRODUCT_IMAGES = '0010-m2e';
    public const ERROR_INVALID_PRODUCT_IMAGES = '0011-m2e';
    public const ERROR_PACKAGE_WEIGHT_NOT_SET = '0012-m2e';
    public const ERROR_PACKAGE_LENGTH_NOT_SET = '0013-m2e';
    public const ERROR_PACKAGE_WIDTH_NOT_SET = '0014-m2e';
    public const ERROR_PACKAGE_HEIGHT_NOT_SET = '0015-m2e';
    public const ERROR_PACKAGE_DIMENSIONS_MISSING = '0016-m2e';
    public const ERROR_PACKAGE_WEIGHT_MISSING = '0017-m2e';
    public const ERROR_PACKAGE_WEIGHT_OUT_OF_RANGE = '0018-m2e';
    public const ERROR_INVALID_SIZE_CHART_IMAGE_URL = '0019-m2e';
    public const ERROR_PRODUCT_NAME_INVALID_LENGTH = '0020-m2e';
    public const ERROR_VARIATIONS_EXCEED_LIMIT = '0021-m2e';
    public const ERROR_CATEGORY_ATTRIBUTE_MISSING = '0022-m2e';

    public function mapByCode(string $code): ?ValidatorMessage
    {
        $map = [
            self::ERROR_NO_ASSOCIATED_PRODUCTS_LIST => (string)__('The product was not listed because it has no associated products.'),
            self::ERROR_NO_ASSOCIATED_PRODUCTS_RELIST => (string)__('The product was not relisted because it has no associated products.'),
            self::ERROR_PRICE_OUT_OF_RANGE => (string)__('The product price is not within the allowed price range.'),
            self::ERROR_QUANTITY_POLICY_CONTRADICTION => (string)__('You\'re submitting an item with QTY contradicting the QTY settings in your Selling Policy. Please check Minimum Quantity to Be Listed and Quantity Percentage options.'),
            self::ERROR_QTY_EXCEEDS_MAXIMUM => (string)__('Product QTY exceeds the allowed limit.'),
            self::ERROR_MISSING_BRAND => (string)__('The required attribute \'Brand\' is missing.'),
            self::ERROR_MISSING_GPSR_INFO => (string)__('The required GPSR Manufacturer and Responsible Person \' \'information is missing.'),
            self::ERROR_CATEGORIES_NOT_SET => (string)__('Categories Settings are not set.'),
            self::ERROR_INVALID_CERTIFICATE_IMAGE_URL => (string)__('The Certificate field contains an invalid URL.'),
            self::ERROR_MISSING_PRODUCT_IMAGES => (string)__('Product Images are missing.'),
            self::ERROR_INVALID_PRODUCT_IMAGES => (string)__('Product Images are invalid.'),
            self::ERROR_PACKAGE_WEIGHT_NOT_SET => (string)__('Package Weight not configured.'),
            self::ERROR_PACKAGE_LENGTH_NOT_SET => (string)__('Package Length not configured.'),
            self::ERROR_PACKAGE_WIDTH_NOT_SET => (string)__('Package Width not configured.'),
            self::ERROR_PACKAGE_HEIGHT_NOT_SET => (string)__('Package Height not configured.'),
            self::ERROR_PACKAGE_DIMENSIONS_MISSING => (string)__('Package Dimensions are missing.'),
            self::ERROR_PACKAGE_WEIGHT_MISSING => (string)__('Package Weight is missing.'),
            self::ERROR_PACKAGE_WEIGHT_OUT_OF_RANGE => (string)__('The product package weight must be within the allowed weight range.'),
            self::ERROR_INVALID_SIZE_CHART_IMAGE_URL => (string)__('The Size Chart field contains an invalid URL.'),
            self::ERROR_PRODUCT_NAME_INVALID_LENGTH => (string)__('The product name must contain between 1 and 255 characters.'),
            self::ERROR_VARIATIONS_EXCEED_LIMIT => (string)__('The number of product variations exceeds the allowed limit.'),
            self::ERROR_CATEGORY_ATTRIBUTE_MISSING => (string)__('Unable to List Product Due to missing Item Attribute(s)'),
        ];

        if (!isset($map[$code])) {
            return null;
        }

        return new ValidatorMessage(
            $map[$code],
            $code
        );
    }
}
