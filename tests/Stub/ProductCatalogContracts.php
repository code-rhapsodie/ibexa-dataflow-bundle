<?php

declare(strict_types=1);

/*
 * ibexa/product-catalog is only available from Ibexa's private repository, so it cannot be a dev dependency
 * of this open source bundle. Minimal stand-ins of the contracts used by the bundle are declared here,
 * only when the real package is not installed.
 */

namespace Ibexa\Contracts\ProductCatalog\Values {
    if (!interface_exists(AttributeInterface::class)) {
        interface AttributeInterface
        {
            public function getIdentifier(): string;

            public function getValue(): mixed;

            public function getAttributeDefinition(): AttributeDefinitionInterface;
        }
    }

    if (!interface_exists(AttributeDefinitionInterface::class)) {
        interface AttributeDefinitionInterface
        {
            public function getType(): AttributeTypeInterface;
        }
    }

    if (!interface_exists(AttributeTypeInterface::class)) {
        interface AttributeTypeInterface
        {
            public function getIdentifier(): string;
        }
    }

    if (!interface_exists(ProductInterface::class)) {
        interface ProductInterface
        {
            /** @return iterable<AttributeInterface> */
            public function getAttributes(): iterable;
        }
    }
}

namespace Ibexa\Contracts\ProductCatalog {
    use Ibexa\Contracts\ProductCatalog\Values\ProductInterface;

    if (!interface_exists(ProductServiceInterface::class)) {
        interface ProductServiceInterface
        {
            public function getProduct(string $code): ProductInterface;
        }
    }
}
