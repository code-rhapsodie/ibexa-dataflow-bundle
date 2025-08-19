<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Filter;

use CodeRhapsodie\IbexaDataflowBundle\Model\ContentUpdateStructure;
use CodeRhapsodie\IbexaDataflowBundle\Model\ProductUpdateStructure;
use Doctrine\DBAL\Connection;

readonly class NotModifiedProductFilter
{

    public function __construct(private NotModifiedContentFilter $notModifiedContentFilter, private Connection $connection)
    {
    }

    public function __invoke(mixed $data)
    {
        if (!$data instanceof ProductUpdateStructure) {
            return $data;
        }

        $contentId = $this->connection->executeQuery('SELECT content_id FROM ibexa_product_specification where code = :code', ['code' => $data->getCode()])
            ->fetchFirstColumn();

        if (empty($contentId)) {
            return $data;
        }

        $result = $this->notModifiedContentFilter->__invoke(ContentUpdateStructure::createForContentId($contentId[0], $data->getLanguageCode(), $data->getFields()));

        if ($result === false) {
            $data->setUpdateContent(false);
        }

        $stockData = $this->connection->executeQuery('SELECT stock FROM ibexa_product_specification_availability WHERE product_code = :code', ['code' => $data->getCode()])->fetchFirstColumn();

        if (!empty($stockData) && ($data->getStock() === $stockData[0] || ($data->getStock() === 0 && $stockData[0] === null))) {
            $data->setUpdateStock(false);
        }

        return $data;
    }

}
