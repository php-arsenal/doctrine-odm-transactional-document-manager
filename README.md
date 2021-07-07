# Doctrine ODM Transactional Document Manager

Built upon Maciej [blog post](https://zgadzaj.com/development/mongodb/mongodb-multi-document-transactions-in-symfony-4-with-doctrine-and-mongodb-odm-bundle)

[![Release](https://img.shields.io/github/v/release/php-arsenal/doctrine-odm-transactional-document-manager)](https://github.com/php-arsenal/doctrine-odm-transactional-document-manager/releases)
[![CI](https://img.shields.io/github/workflow/status/php-arsenal/doctrine-odm-transactional-document-manager/CI)](https://github.com/php-arsenal/doctrine-odm-transactional-document-manager/actions/workflows/ci.yml)
[![Packagist](https://img.shields.io/packagist/dt/php-arsenal/doctrine-odm-transactional-document-manager)](https://packagist.org/packages/php-arsenal/doctrine-odm-transactional-document-manager)

## Install

Require with Composer

```
composer require php-arsenal/doctrine-odm-transactional-document-manager 
```

Add to `services.yaml`

```yaml
    PhpArsenal\DoctrineOdmTransactionalDocumentManager\TransactionalDocumentManager:
        autowire: true
        autoconfigure: true
```

## Use

We might also wrap that `publishProducts()` code in a try-catch block and call `$this->documentManager->abortTransaction();` in its catch section, but if a transaction is not committed, it will be automatically aborted (rolled back), so there is no real need for that here.

```php
<?php

namespace YourNamespace;

use PhpArsenal\DoctrineOdmTransactionalDocumentManager\TransactionalDocumentManager;

class ProductManager
{
    public function __construct(
        private TransactionalDocumentManager $documentManager, 
        private ProductRepository $productRepository
    ) {
    }
 
    public function publishProducts(): void
    {
        $products = $this->productRepository->findBy(['published' => false]);
 
        $this->documentManager->startTransaction();
 
        foreach ($products as $product) {
            $product->setPublished(true);
        }
 
        $this->documentManager->flush([
            'session' => $this->documentManager->getSession(),
        ]);
 
        $this->documentManager->commitTransaction();
    }
}
```
